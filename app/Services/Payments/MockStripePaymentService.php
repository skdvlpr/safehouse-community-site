<?php

namespace App\Services\Payments;

use App\DataTransferObjects\DonationIngestPayload;
use App\Models\DonationCampaign;
use App\Services\Donations\DonationIngestPayloadMapper;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use RuntimeException;

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

    public function constructWebhookEvent(string $payload, ?string $signature): \Stripe\Event
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
    }

    /**
     * @return array<string, mixed>|null
     */
    private function loadIntent(string $paymentIntentId): ?array
    {
        $stored = Cache::get(self::CACHE_PREFIX.$paymentIntentId);

        return is_array($stored) ? $stored : null;
    }
}
