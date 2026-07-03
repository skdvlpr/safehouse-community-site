<?php

namespace App\Services\Donations;

use App\Services\Payments\StripePaymentService;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

/**
 * Idempotent CRM sync after a succeeded Stripe PaymentIntent.
 * Fallback when the webhook is delayed or missed; also used in local dev without stripe listen.
 */
class StripeDonationThankYouSync
{
    public function __construct(
        private readonly StripePaymentService $stripePaymentService,
        private readonly DonationIngestService $donationIngestService,
        private readonly DonationIngestPayloadMapper $payloadMapper,
    ) {}

    public function ingestSucceededPaymentIntent(string $paymentIntentId): void
    {
        if (StripePaymentService::mockModeEnabled()) {
            return;
        }

        $paymentIntentId = trim($paymentIntentId);
        if ($paymentIntentId === '') {
            return;
        }

        try {
            $intent = $this->stripePaymentService->retrievePaymentIntent($paymentIntentId);
            $this->donationIngestService->ingest($this->payloadMapper->fromPaymentIntent($intent));
        } catch (RuntimeException $exception) {
            Log::warning('Stripe donation thank-you sync failed.', [
                'payment_intent_id' => $paymentIntentId,
                'reason' => $exception->getMessage(),
            ]);
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
