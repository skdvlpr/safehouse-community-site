<?php

namespace App\Services\Donations;

use App\DataTransferObjects\DonationIngestPayload;
use App\DataTransferObjects\StripeSettlementAmounts;
use App\Models\DonationCampaign;
use App\Services\Payments\StripePaymentService;
use App\Support\DonorContact;
use Illuminate\Support\Carbon;
use Stripe\PaymentIntent;

class DonationIngestPayloadMapper
{
    public function __construct(
        private readonly StripePaymentService $stripePaymentService,
    ) {}

    /**
     * @param  array<string, mixed>  $stored
     */
    public function fromMockStoredIntent(array $stored): DonationIngestPayload
    {
        $settlement = $this->stripePaymentService->settlementFromMockStoredIntent($stored);

        return $this->build(
            externalId: (string) $stored['payment_intent_id'],
            settlement: $settlement,
            metadata: (array) ($stored['metadata'] ?? []),
            donatedAt: $this->donatedAtFromMockStoredIntent($stored),
        );
    }

    public function fromPaymentIntent(PaymentIntent $intent): DonationIngestPayload
    {
        $settlement = $this->stripePaymentService->settlementFromPaymentIntent($intent);

        return $this->build(
            externalId: $intent->id,
            settlement: $settlement,
            metadata: $intent->metadata->toArray(),
            donatedAt: $this->donatedAtFromPaymentIntent($intent),
        );
    }

    /**
     * Stripe SoT: PaymentIntent.created (unix seconds), not website now().
     */
    public function donatedAtFromPaymentIntent(PaymentIntent $intent): string
    {
        $created = $intent->created ?? null;
        if (is_numeric($created)) {
            return Carbon::createFromTimestamp((int) $created, 'UTC')->toIso8601String();
        }

        return now('UTC')->toIso8601String();
    }

    /**
     * @param  array<string, mixed>  $stored
     */
    public function donatedAtFromMockStoredIntent(array $stored): string
    {
        if (isset($stored['created']) && is_numeric($stored['created'])) {
            return Carbon::createFromTimestamp((int) $stored['created'], 'UTC')->toIso8601String();
        }

        if (isset($stored['created_at']) && is_string($stored['created_at']) && $stored['created_at'] !== '') {
            return Carbon::parse($stored['created_at'])->utc()->toIso8601String();
        }

        return now('UTC')->toIso8601String();
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    private function build(
        string $externalId,
        StripeSettlementAmounts $settlement,
        array $metadata,
        string $donatedAt,
    ): DonationIngestPayload {
        $campaignTitle = (string) ($metadata['campaign_title'] ?? 'Donazioni online');
        $financingGoalAmount = null;

        if (isset($metadata['campaign_id'])) {
            $campaign = DonationCampaign::query()->find($metadata['campaign_id']);
            if ($campaign !== null) {
                $campaignTitle = $campaign->finanziamentoTitle();
                $financingGoalAmount = $campaign->fundraisingGoalAmount();
            }
        }

        return new DonationIngestPayload(
            provider: 'stripe',
            externalId: $externalId,
            amountGross: $settlement->gross,
            commissionAmount: $settlement->fee,
            commissionPercent: $settlement->feePercent,
            netAmount: $settlement->net,
            currency: $settlement->currency,
            campaignTitle: $campaignTitle,
            donorName: (string) ($metadata['donor_name'] ?? ''),
            comment: isset($metadata['comment']) ? (string) $metadata['comment'] : null,
            donorType: isset($metadata['donor_type']) ? (string) $metadata['donor_type'] : null,
            donatedAt: $donatedAt,
            financingGoalAmount: $financingGoalAmount,
            donorEmail: isset($metadata['donor_email']) ? DonorContact::normalizeEmail((string) $metadata['donor_email']) : null,
            donorPhone: isset($metadata['donor_phone']) ? DonorContact::normalizePhone((string) $metadata['donor_phone']) : null,
        );
    }
}
