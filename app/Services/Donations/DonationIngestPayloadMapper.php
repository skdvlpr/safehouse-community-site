<?php

namespace App\Services\Donations;

use App\DataTransferObjects\DonationIngestPayload;
use App\Models\DonationCampaign;
use Stripe\PaymentIntent;

class DonationIngestPayloadMapper
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function fromMockStoredIntent(array $stored): DonationIngestPayload
    {
        return $this->build(
            externalId: (string) $stored['payment_intent_id'],
            amountCents: (int) $stored['amount_cents'],
            currency: (string) $stored['currency'],
            metadata: (array) ($stored['metadata'] ?? []),
        );
    }

    public function fromPaymentIntent(PaymentIntent $intent): DonationIngestPayload
    {
        return $this->build(
            externalId: $intent->id,
            amountCents: (int) ($intent->amount_received ?? $intent->amount),
            currency: (string) $intent->currency,
            metadata: $intent->metadata->toArray(),
        );
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    private function build(string $externalId, int $amountCents, string $currency, array $metadata): DonationIngestPayload
    {
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
            amount: $amountCents / 100,
            currency: strtoupper($currency),
            campaignTitle: $campaignTitle,
            donorName: (string) ($metadata['donor_name'] ?? ''),
            comment: isset($metadata['comment']) ? (string) $metadata['comment'] : null,
            donorType: isset($metadata['donor_type']) ? (string) $metadata['donor_type'] : null,
            donatedAt: now()->toIso8601String(),
            financingGoalAmount: $financingGoalAmount,
        );
    }
}
