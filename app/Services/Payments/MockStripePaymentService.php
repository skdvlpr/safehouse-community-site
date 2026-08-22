<?php

namespace App\Services\Payments;

use App\DataTransferObjects\DonationIngestPayload;
use App\Models\DonationCampaign;
use App\Services\Donations\DonationIngestPayloadMapper;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use RuntimeException;
use Stripe\Event;
use Stripe\PaymentIntent;

class MockStripePaymentService extends StripePaymentService
{
    private const CACHE_PREFIX = 'stripe_mock_intent:';

    private const CACHE_TTL_HOURS = 24;

    public static function make(): self
    {
        return new self(null);
    }

    /**
     * @return array{client_secret: string, payment_intent_id: string}
     */
    public function createDonationIntent(
        DonationCampaign $campaign,
        int $amountCents,
        string $donorName,
        string $donorType,
        ?string $comment,
        ?string $donorEmail = null,
        ?string $donorPhone = null,
    ): array {
        if ($campaign->allowsRecurring()) {
            throw new RuntimeException('Recurring campaigns must use Stripe Subscriptions.');
        }

        $this->assertDonationIntentAllowed($campaign, $amountCents);

        $paymentIntentId = 'pi_mock_'.Str::lower(Str::ulid());
        $clientSecret = $paymentIntentId.'_secret_mock_'.Str::lower(Str::random(24));

        $this->storeIntent($paymentIntentId, [
            'payment_intent_id' => $paymentIntentId,
            'amount_cents' => $amountCents,
            'currency' => strtolower($campaign->currency),
            'description' => $this->donationDescription($campaign, $donorName, 'OneTime'),
            'metadata' => $this->metadata($campaign, $donorName, $donorType, $comment, $donorEmail, $donorPhone, 'OneTime'),
            'status' => 'requires_payment_method',
            'created' => now('UTC')->timestamp,
        ]);

        return [
            'client_secret' => $clientSecret,
            'payment_intent_id' => $paymentIntentId,
        ];
    }

    /**
     * @return array{client_secret: string, payment_intent_id: string, subscription_id: string, customer_id: string}
     */
    public function createDonationSubscription(
        DonationCampaign $campaign,
        int $amountCents,
        string $donorName,
        string $donorType,
        ?string $comment,
        ?string $donorEmail = null,
        ?string $donorPhone = null,
    ): array {
        if (! $campaign->allowsRecurring()) {
            throw new RuntimeException('One-time campaigns must use PaymentIntents.');
        }

        $this->assertDonationIntentAllowed($campaign, $amountCents);

        $paymentIntentId = 'pi_mock_'.Str::lower(Str::ulid());
        $subscriptionId = 'sub_mock_'.Str::lower(Str::ulid());
        $customerId = 'cus_mock_'.Str::lower(Str::ulid());
        $clientSecret = $paymentIntentId.'_secret_mock_'.Str::lower(Str::random(24));

        $metadata = array_merge(
            $this->metadata($campaign, $donorName, $donorType, $comment, $donorEmail, $donorPhone, 'Recurring'),
            [
                'stripe_subscription_id' => $subscriptionId,
                'stripe_customer_id' => $customerId,
            ],
        );

        $this->storeIntent($paymentIntentId, [
            'payment_intent_id' => $paymentIntentId,
            'amount_cents' => $amountCents,
            'currency' => strtolower($campaign->currency),
            'description' => $this->donationDescription($campaign, $donorName, 'Recurring'),
            'metadata' => $metadata,
            'status' => 'requires_payment_method',
            'created' => now('UTC')->timestamp,
            'customer_id' => $customerId,
            'subscription_id' => $subscriptionId,
            'payment_method_type' => 'card',
        ]);

        return [
            'client_secret' => $clientSecret,
            'payment_intent_id' => $paymentIntentId,
            'subscription_id' => $subscriptionId,
            'customer_id' => $customerId,
        ];
    }

    public function attachPrimaNotaLinkToPaymentIntent(string $paymentIntentId, string $primaNotaId): array
    {
        $paymentIntentId = trim($paymentIntentId);
        $primaNotaId = trim($primaNotaId);
        if ($paymentIntentId === '' || $primaNotaId === '') {
            return ['updated' => false, 'reason' => 'invalid_ids'];
        }

        $stored = $this->loadIntent($paymentIntentId);
        if ($stored === null) {
            return ['updated' => false, 'reason' => 'not_found'];
        }

        $url = self::primaNotaCrmUrl($primaNotaId);
        $meta = is_array($stored['metadata'] ?? null) ? $stored['metadata'] : [];
        $meta['crm_prima_nota_id'] = $primaNotaId;
        if ($url !== '') {
            $meta['crm_prima_nota_url'] = $url;
        }
        $stored['metadata'] = $meta;
        if (trim((string) ($stored['description'] ?? '')) === '') {
            $stored['description'] = $this->donationDescriptionFromStoredMeta($meta);
        }
        $this->storeIntent($paymentIntentId, $stored);

        return ['updated' => true, 'reason' => null];
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    private function donationDescriptionFromStoredMeta(array $meta): string
    {
        $campaignTitle = (string) ($meta['campaign_title'] ?? 'Donazione');
        $donorName = (string) ($meta['donor_name'] ?? '');
        $freq = (($meta['donation_frequency'] ?? '') === 'Recurring') ? 'ricorrente' : 'una tantum';

        return mb_substr(
            $donorName !== ''
                ? sprintf('Donazione %s — %s — %s', $freq, $campaignTitle, $donorName)
                : sprintf('Donazione %s — %s', $freq, $campaignTitle),
            0,
            1000,
        );
    }

    public function constructWebhookEvent(string $payload, ?string $signature): Event
    {
        throw new RuntimeException('Stripe webhooks are disabled in mock mode. Use the mock complete endpoint.');
    }

    public function completeIntent(string $paymentIntentId, DonationIngestPayloadMapper $mapper): DonationIngestPayload
    {
        if (! str_starts_with($paymentIntentId, 'pi_mock_')) {
            throw new RuntimeException('Invalid mock payment intent id.');
        }

        $stored = Cache::get(self::CACHE_PREFIX.$paymentIntentId);
        if (! is_array($stored)) {
            throw new RuntimeException('Mock payment intent not found or expired.');
        }

        if (($stored['status'] ?? '') === 'succeeded') {
            throw new RuntimeException('Mock payment intent already completed.');
        }

        $stored['status'] = 'succeeded';
        // Mock Stripe SoT: fee/net must be explicit on the stored intent (default fee 0 — never invent %).
        $grossCents = (int) ($stored['amount_cents'] ?? 0);
        $feeCents = (int) ($stored['fee_cents'] ?? 0);
        $stored['fee_cents'] = $feeCents;
        $stored['net_cents'] = (int) ($stored['net_cents'] ?? max(0, $grossCents - $feeCents));
        $this->storeIntent($paymentIntentId, $stored);

        return $mapper->fromMockStoredIntent($stored);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function storeIntent(string $paymentIntentId, array $data): void
    {
        Cache::put(
            self::CACHE_PREFIX.$paymentIntentId,
            $data,
            now()->addHours(self::CACHE_TTL_HOURS),
        );

        $index = Cache::get(self::CACHE_PREFIX.'index', []);
        if (! is_array($index)) {
            $index = [];
        }
        $index[$paymentIntentId] = true;
        Cache::put(
            self::CACHE_PREFIX.'index',
            $index,
            now()->addHours(self::CACHE_TTL_HOURS),
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    private function loadIntent(string $paymentIntentId): ?array
    {
        $stored = Cache::get(self::CACHE_PREFIX.$paymentIntentId);

        return is_array($stored) ? $stored : null;
    }

    /**
     * @return array{items: list<PaymentIntent>, has_more: bool}
     */
    public function listPaymentIntentsPage(?int $createdGte, ?string $startingAfter = null, int $limit = 100): array
    {
        $index = Cache::get(self::CACHE_PREFIX.'index', []);
        if (! is_array($index)) {
            $index = [];
        }

        $rows = [];
        foreach (array_keys($index) as $paymentIntentId) {
            if (! is_string($paymentIntentId) || $paymentIntentId === '') {
                continue;
            }
            $stored = $this->loadIntent($paymentIntentId);
            if ($stored === null) {
                continue;
            }
            $created = (int) ($stored['created'] ?? 0);
            if ($createdGte !== null && $createdGte > 0 && $created < $createdGte) {
                continue;
            }
            $rows[] = $stored;
        }

        usort($rows, static fn (array $a, array $b): int => ((int) ($b['created'] ?? 0)) <=> ((int) ($a['created'] ?? 0)));

        $limit = max(1, min(100, $limit));
        $offset = 0;
        if (is_string($startingAfter) && $startingAfter !== '') {
            foreach ($rows as $i => $row) {
                if ((string) ($row['payment_intent_id'] ?? '') === $startingAfter) {
                    $offset = $i + 1;
                    break;
                }
            }
        }

        $slice = array_slice($rows, $offset, $limit);
        $hasMore = ($offset + count($slice)) < count($rows);

        $items = [];
        foreach ($slice as $stored) {
            $items[] = PaymentIntent::constructFrom([
                'id' => (string) ($stored['payment_intent_id'] ?? ''),
                'object' => 'payment_intent',
                'amount' => (int) ($stored['amount_cents'] ?? 0),
                'currency' => (string) ($stored['currency'] ?? 'eur'),
                'status' => (string) ($stored['status'] ?? 'requires_payment_method'),
                'created' => (int) ($stored['created'] ?? time()),
                'metadata' => is_array($stored['metadata'] ?? null) ? $stored['metadata'] : [],
                'description' => (string) ($stored['description'] ?? ''),
                'latest_charge' => [
                    'id' => 'ch_mock_'.(string) ($stored['payment_intent_id'] ?? 'x'),
                    'object' => 'charge',
                    'balance_transaction' => [
                        'id' => 'txn_mock_'.(string) ($stored['payment_intent_id'] ?? 'x'),
                        'object' => 'balance_transaction',
                        'fee' => (int) ($stored['fee_cents'] ?? 0),
                        'net' => (int) ($stored['net_cents'] ?? max(0, ((int) ($stored['amount_cents'] ?? 0)) - ((int) ($stored['fee_cents'] ?? 0)))),
                        'amount' => (int) ($stored['amount_cents'] ?? 0),
                        'currency' => (string) ($stored['currency'] ?? 'eur'),
                    ],
                ],
            ]);
        }

        return [
            'items' => $items,
            'has_more' => $hasMore,
        ];
    }

    /**
     * @return list<object>
     */
    public function listPaidPayouts(int $limit = 40): array
    {
        return [];
    }

    /**
     * @return list<object>
     */
    public function listBalanceTransactionsForPayout(string $payoutId): array
    {
        return [];
    }
}
