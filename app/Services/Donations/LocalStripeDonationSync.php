<?php

namespace App\Services\Donations;

use App\Services\Payments\StripePaymentService;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class LocalStripeDonationSync
{
    public function __construct(
        private readonly StripePaymentService $stripePaymentService,
        private readonly DonationIngestService $donationIngestService,
        private readonly DonationIngestPayloadMapper $payloadMapper,
    ) {}

    /**
     * Local dev fallback when stripe listen is not running.
     * Production still relies on signed webhooks only.
     */
    public function ingestSucceededPaymentIntent(string $paymentIntentId): void
    {
        if (! app()->isLocal() || StripePaymentService::mockModeEnabled()) {
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
            Log::warning('Local Stripe donation sync failed.', [
                'payment_intent_id' => $paymentIntentId,
                'reason' => $exception->getMessage(),
            ]);
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
