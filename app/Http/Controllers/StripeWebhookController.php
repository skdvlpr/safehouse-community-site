<?php

namespace App\Http\Controllers;

use App\DataTransferObjects\DonationIngestPayload;
use App\Models\DonationCampaign;
use App\Services\Donations\DonationIngestService;
use App\Services\Payments\StripePaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use RuntimeException;
use Stripe\PaymentIntent;

class StripeWebhookController extends Controller
{
    public function __construct(
        private readonly StripePaymentService $stripePaymentService,
        private readonly DonationIngestService $donationIngestService,
    ) {}

    public function __invoke(Request $request): Response
    {
        try {
            $event = $this->stripePaymentService->constructWebhookEvent(
                $request->getContent(),
                $request->header('Stripe-Signature'),
            );
        } catch (RuntimeException $exception) {
            report($exception);

            return response($exception->getMessage(), 400);
        }

        $intent = $this->stripePaymentService->paymentIntentFromEvent($event);
        if ($intent === null) {
            return response('Ignored', 200);
        }

        try {
            $this->donationIngestService->ingest($this->payloadFromIntent($intent));
        } catch (RuntimeException $exception) {
            report($exception);

            return response('CRM ingest failed', 502);
        }

        return response('OK', 200);
    }

    private function payloadFromIntent(PaymentIntent $intent): DonationIngestPayload
    {
        $metadata = $intent->metadata->toArray();
        $campaignTitle = (string) ($metadata['campaign_title'] ?? 'Donazioni online');

        if (isset($metadata['campaign_id'])) {
            $campaign = DonationCampaign::query()->find($metadata['campaign_id']);
            if ($campaign !== null) {
                $campaignTitle = $campaign->finanziamentoTitle();
            }
        }

        $amount = ($intent->amount_received ?? $intent->amount) / 100;

        return new DonationIngestPayload(
            provider: 'stripe',
            externalId: $intent->id,
            amount: (float) $amount,
            currency: strtoupper((string) $intent->currency),
            campaignTitle: $campaignTitle,
            donorName: (string) ($metadata['donor_name'] ?? ''),
            comment: isset($metadata['comment']) ? (string) $metadata['comment'] : null,
            donorType: isset($metadata['donor_type']) ? (string) $metadata['donor_type'] : null,
            donatedAt: now()->toIso8601String(),
        );
    }
}
