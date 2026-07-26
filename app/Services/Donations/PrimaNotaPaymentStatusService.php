<?php

namespace App\Services\Donations;

use App\Services\EspoCrm\EspoCrmClient;
use Illuminate\Support\Facades\Log;
use Stripe\Event;

/**
 * Maps Stripe cancel / failure / refund / dispute events onto PrimaNota.paymentStatus.
 */
class PrimaNotaPaymentStatusService
{
    public const STATUS_PLANNED = 'Planned';

    public const STATUS_PAID = 'Paid';

    public const STATUS_CANCELLED = 'Cancelled';

    public const STATUS_REFUNDED = 'Refunded';

    public const STATUS_DISPUTED = 'Disputed';

    public const STATUS_PROBLEMATIC = 'Problematic';

    public function __construct(
        private readonly EspoCrmClient $client,
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
            default => $this->result(false, 0, null, 'unhandled_type'),
        };
    }

    private function handleInvoicePaymentFailed(object $invoice): array
    {
        $subscriptionId = (string) ($invoice->subscription ?? '');
        if ($subscriptionId !== '') {
            return $this->updateBySubscriptionId($subscriptionId, self::STATUS_PROBLEMATIC);
        }

        $paymentIntentId = (string) ($invoice->payment_intent ?? '');
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

        // won / warning_closed → money kept → Paid again
        // lost → funds stay with cardholder → Disputed (terminal loss)
        $paymentStatus = match ($status) {
            'won', 'warning_closed' => self::STATUS_PAID,
            'lost' => self::STATUS_DISPUTED,
            default => self::STATUS_DISPUTED,
        };

        return $this->updateByDisputeCharge($dispute, $paymentStatus);
    }

    private function handleDisputeFundsReinstated(object $dispute): array
    {
        return $this->updateByDisputeCharge($dispute, self::STATUS_PAID);
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
     */
    private function updateMatches(array $where, string $status): int
    {
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

            try {
                $this->client->update($entity, $id, [
                    'paymentStatus' => $status,
                ]);
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
