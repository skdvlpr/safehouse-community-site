<?php

namespace App\Services\Donations;

use App\Services\EspoCrm\EspoCrmClient;
use App\Services\Payments\StripePaymentService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Stripe\Event;

/**
 * Maps Stripe cancel / failure / refund / dispute / payout events onto PrimaNota.paymentStatus.
 *
 * Status model:
 * - Planned — Stripe charge settled, awaiting bank payout (not counted in cash totals)
 * - Inviato — counted (manual entries + Stripe after payout.paid)
 * - Cancelled / Refunded / Disputed / Problematic — excluded from totals
 */
class PrimaNotaPaymentStatusService
{
    public const STATUS_PLANNED = 'Planned';

    public const STATUS_INVIATO = 'Inviato';

    public const STATUS_CANCELLED = 'Cancelled';

    public const STATUS_REFUNDED = 'Refunded';

    public const STATUS_DISPUTED = 'Disputed';

    public const STATUS_PROBLEMATIC = 'Problematic';

    public function __construct(
        private readonly EspoCrmClient $client,
        private readonly StripePaymentService $stripePaymentService,
    ) {}

    /**
     * @return array{handled: bool, updated: int, status: ?string, reason: ?string}
     */
    public function applyFromStripeEvent(Event $event): array
    {
        $type = (string) ($event->type ?? '');
        $object = $event->data->object ?? null;

        if (! is_object($object)) {
            return $this->result(false, 0, null, 'no_object');
        }

        return match ($type) {
            'customer.subscription.deleted' => $this->updateBySubscriptionId(
                (string) ($object->id ?? ''),
                self::STATUS_CANCELLED,
            ),
            'payment_intent.canceled' => $this->updateByPaymentIntentId(
                (string) ($object->id ?? ''),
                self::STATUS_CANCELLED,
            ),
            'payment_intent.payment_failed' => $this->updateByPaymentIntentId(
                (string) ($object->id ?? ''),
                self::STATUS_PROBLEMATIC,
            ),
            'invoice.payment_failed' => $this->handleInvoicePaymentFailed($object),
            'charge.refunded' => $this->handleChargeRefunded($object),
            'charge.dispute.created',
            'charge.dispute.updated',
            'charge.dispute.funds_withdrawn' => $this->handleDisputeOpen($object),
            'charge.dispute.closed' => $this->handleDisputeClosed($object),
            'charge.dispute.funds_reinstated' => $this->handleDisputeFundsReinstated($object),
            'payout.paid' => $this->handlePayoutPaid($object),
            default => $this->result(false, 0, null, 'unhandled_type'),
        };
    }

    /**
     * When Stripe pays the NGO bank account, mark each included charge as Inviato.
     *
     * Supported: automatic payouts only (Stripe links BTs via ?payout=po_…).
     * Manual payouts are ignored — Stripe cannot identify included charges.
     *
     * @return array{handled: bool, updated: int, status: ?string, reason: ?string}
     */
    private function handlePayoutPaid(object $payout): array
    {
        $payoutId = trim((string) ($payout->id ?? ''));
        if ($payoutId === '') {
            return $this->result(true, 0, self::STATUS_INVIATO, 'empty_payout');
        }

        if (($payout->automatic ?? null) === false) {
            Log::warning('Ignoring Stripe manual payout for PrimaNota status (automatic payouts only).', [
                'payout_id' => $payoutId,
            ]);

            return $this->result(true, 0, self::STATUS_INVIATO, 'manual_payout_unsupported');
        }

        $paidAt = $this->formatPayoutPaidAt($payout);

        try {
            $balanceTransactions = $this->stripePaymentService->listBalanceTransactionsForPayout($payoutId);
        } catch (\Throwable $exception) {
            Log::warning('Failed to list Stripe balance transactions for payout.', [
                'payout_id' => $payoutId,
                'error' => $exception->getMessage(),
            ]);

            return $this->result(true, 0, self::STATUS_INVIATO, 'bt_lookup_failed');
        }

        $updated = 0;

        foreach ($balanceTransactions as $bt) {
            $type = (string) ($bt->type ?? '');
            if (! in_array($type, ['charge', 'payment'], true)) {
                continue;
            }

            $btId = isset($bt->id) ? (string) $bt->id : '';
            $source = $bt->source ?? null;
            $chargeId = is_string($source) && str_starts_with($source, 'ch_')
                ? $source
                : (is_object($source) && isset($source->id) ? (string) $source->id : '');

            $updated += $this->markInviatoFromPayout(
                balanceTransactionId: $btId,
                chargeId: $chargeId,
                payoutId: $payoutId,
                paidAt: $paidAt,
            );
        }

        return $this->result(
            true,
            $updated,
            self::STATUS_INVIATO,
            $updated > 0 ? null : 'not_found',
        );
    }

    private function formatPayoutPaidAt(object $payout): string
    {
        if (is_numeric($payout->arrival_date ?? null)) {
            return Carbon::createFromTimestamp((int) $payout->arrival_date, 'UTC')->format('Y-m-d H:i:s');
        }

        if (is_numeric($payout->created ?? null)) {
            return Carbon::createFromTimestamp((int) $payout->created, 'UTC')->format('Y-m-d H:i:s');
        }

        return Carbon::now('UTC')->format('Y-m-d H:i:s');
    }

    private function markInviatoFromPayout(
        string $balanceTransactionId,
        string $chargeId,
        string $payoutId,
        string $paidAt,
    ): int {
        $extra = [
            'stripePayoutId' => $payoutId,
            'stripePayoutPaidAt' => $paidAt,
            'paymentStatus' => self::STATUS_INVIATO,
        ];

        // Only advance Planned (or legacy empty) — never overwrite Refunded/Disputed/etc.
        $onlyFrom = [self::STATUS_PLANNED, '', 'Paid', 'PaidOut'];

        if ($balanceTransactionId !== '') {
            $n = $this->updateMatches([
                [
                    'type' => 'equals',
                    'attribute' => 'stripeBalanceTransactionId',
                    'value' => $balanceTransactionId,
                ],
            ], self::STATUS_INVIATO, $extra, onlyFromStatuses: $onlyFrom);
            if ($n > 0) {
                return $n;
            }
        }

        if ($chargeId !== '') {
            return $this->updateMatches([
                [
                    'type' => 'equals',
                    'attribute' => 'stripeChargeId',
                    'value' => $chargeId,
                ],
            ], self::STATUS_INVIATO, $extra, onlyFromStatuses: $onlyFrom);
        }

        return 0;
    }

    private function handleInvoicePaymentFailed(object $invoice): array
    {
        $subscriptionId = (string) ($invoice->subscription ?? '');
        if ($subscriptionId !== '') {
            return $this->updateBySubscriptionId($subscriptionId, self::STATUS_PROBLEMATIC);
        }

        $paymentIntentId = StripePaymentService::paymentIntentIdFromInvoiceObject($invoice) ?? '';
        if ($paymentIntentId !== '') {
            return $this->updateByPaymentIntentId($paymentIntentId, self::STATUS_PROBLEMATIC);
        }

        return $this->result(true, 0, self::STATUS_PROBLEMATIC, 'no_match_key');
    }

    private function handleChargeRefunded(object $charge): array
    {
        $chargeId = (string) ($charge->id ?? '');
        $paymentIntentId = (string) ($charge->payment_intent ?? '');
        $amountRefunded = (int) ($charge->amount_refunded ?? 0);
        $amount = (int) ($charge->amount ?? 0);
        $status = ($amount > 0 && $amountRefunded > 0 && $amountRefunded < $amount)
            ? self::STATUS_PROBLEMATIC
            : self::STATUS_REFUNDED;

        if ($chargeId !== '') {
            $updated = $this->updateByAttributeEquals('stripeChargeId', $chargeId, $status);
            if ($updated > 0) {
                return $this->result(true, $updated, $status, null);
            }
        }

        if ($paymentIntentId !== '') {
            return $this->updateByPaymentIntentId($paymentIntentId, $status);
        }

        return $this->result(true, 0, $status, 'no_match_key');
    }

    private function handleDisputeOpen(object $dispute): array
    {
        return $this->updateByDisputeCharge($dispute, self::STATUS_DISPUTED);
    }

    private function handleDisputeClosed(object $dispute): array
    {
        $status = (string) ($dispute->status ?? '');

        // won / warning_closed → funds kept on Stripe balance → Planned until next payout
        // lost → funds stay with cardholder → Disputed
        $paymentStatus = match ($status) {
            'won', 'warning_closed' => self::STATUS_PLANNED,
            'lost' => self::STATUS_DISPUTED,
            default => self::STATUS_DISPUTED,
        };

        return $this->updateByDisputeCharge($dispute, $paymentStatus);
    }

    private function handleDisputeFundsReinstated(object $dispute): array
    {
        return $this->updateByDisputeCharge($dispute, self::STATUS_PLANNED);
    }

    /**
     * @return array{handled: bool, updated: int, status: ?string, reason: ?string}
     */
    private function updateByDisputeCharge(object $dispute, string $status): array
    {
        $chargeId = (string) ($dispute->charge ?? '');
        if ($chargeId === '') {
            return $this->result(true, 0, $status, 'empty_charge');
        }

        $updated = $this->updateByAttributeEquals('stripeChargeId', $chargeId, $status);

        return $this->result(true, $updated, $status, $updated > 0 ? null : 'not_found');
    }

    /**
     * @return array{handled: bool, updated: int, status: ?string, reason: ?string}
     */
    private function updateByPaymentIntentId(string $paymentIntentId, string $status): array
    {
        $paymentIntentId = trim($paymentIntentId);
        if ($paymentIntentId === '') {
            return $this->result(true, 0, $status, 'empty_payment_intent');
        }

        $updated = $this->updateByAttributeContains('donationPaymentReference', $paymentIntentId, $status);

        return $this->result(true, $updated, $status, $updated > 0 ? null : 'not_found');
    }

    /**
     * @return array{handled: bool, updated: int, status: ?string, reason: ?string}
     */
    private function updateBySubscriptionId(string $subscriptionId, string $status): array
    {
        $subscriptionId = trim($subscriptionId);
        if ($subscriptionId === '') {
            return $this->result(true, 0, $status, 'empty_subscription');
        }

        $updated = $this->updateByAttributeEquals('stripeSubscriptionId', $subscriptionId, $status);

        return $this->result(true, $updated, $status, $updated > 0 ? null : 'not_found');
    }

    private function updateByAttributeEquals(string $attribute, string $value, string $status): int
    {
        return $this->updateMatches([
            [
                'type' => 'equals',
                'attribute' => $attribute,
                'value' => $value,
            ],
        ], $status);
    }

    private function updateByAttributeContains(string $attribute, string $value, string $status): int
    {
        return $this->updateMatches([
            [
                'type' => 'contains',
                'attribute' => $attribute,
                'value' => $value,
            ],
        ], $status);
    }

    /**
     * @param  list<array<string, mixed>>  $where
     * @param  array<string, mixed>  $extraFields
     * @param  list<string>|null  $onlyFromStatuses
     */
    private function updateMatches(
        array $where,
        string $status,
        array $extraFields = [],
        ?array $onlyFromStatuses = null,
    ): int {
        $entity = (string) config('espocrm.prima_nota.entity', 'PrimaNota');
        $result = $this->client->search($entity, [
            'select' => 'id,paymentStatus',
            'maxSize' => 50,
            'where' => $where,
        ]);

        $list = $result['list'] ?? [];
        if (! is_array($list) || $list === []) {
            return 0;
        }

        $updated = 0;

        foreach ($list as $row) {
            if (! is_array($row)) {
                continue;
            }

            $id = (string) ($row['id'] ?? '');
            if ($id === '') {
                continue;
            }

            $current = (string) ($row['paymentStatus'] ?? '');
            if ($current === $status) {
                continue;
            }

            if ($onlyFromStatuses !== null && ! in_array($current, $onlyFromStatuses, true)) {
                continue;
            }

            $payload = array_merge($extraFields, [
                'paymentStatus' => $status,
            ]);

            try {
                $this->client->update($entity, $id, $payload);
                $updated++;
            } catch (\Throwable $exception) {
                Log::warning('Failed to update PrimaNota paymentStatus from Stripe event.', [
                    'prima_nota_id' => $id,
                    'payment_status' => $status,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        return $updated;
    }

    /**
     * @return array{handled: bool, updated: int, status: ?string, reason: ?string}
     */
    private function result(bool $handled, int $updated, ?string $status, ?string $reason): array
    {
        return [
            'handled' => $handled,
            'updated' => $updated,
            'status' => $status,
            'reason' => $reason,
        ];
    }
}
