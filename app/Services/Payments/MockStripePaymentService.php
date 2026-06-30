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
    ): array {
        $this->assertDonationIntentAllowed($campaign, $amountCents);

        $paymentIntentId = 'pi_mock_'.Str::lower(Str::ulid());
        $clientSecret = $paymentIntentId.'_secret_mock_'.Str::lower(Str::random(24));

        $this->storeIntent($paymentIntentId, [
            'payment_intent_id' => $paymentIntentId,
            'amount_cents' => $amountCents,
            'currency' => strtolower($campaign->currency),
            'metadata' => $this->metadata($campaign, $donorName, $donorType, $comment),
            'status' => 'requires_payment_method',
        ]);

        return [
            'client_secret' => $clientSecret,
            'payment_intent_id' => $paymentIntentId,
        ];
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
}
