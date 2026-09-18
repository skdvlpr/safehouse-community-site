<?php

namespace App\Services\Donations;

use App\Exceptions\StripeSettlementNotReadyException;
use App\Services\Payments\StripePaymentService;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

/**
 * Optional one-shot CRM sync on thank-you. Must not wait for BalanceTransaction.
 * Webhook `charge.updated` is the settlement SoT when fee is not ready yet.
 *
 * @see https://docs.stripe.com/payments/payment-intents/asynchronous-capture
 * @see https://laravel.com/docs/13.x/logging
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
        if ($this->stripePaymentService->mockModeEnabled()) {
            return;
        }

        $paymentIntentId = trim($paymentIntentId);
        if ($paymentIntentId === '') {
            return;
        }

        try {
            $intent = $this->stripePaymentService->retrievePaymentIntentRecord($paymentIntentId);
            if (! $this->stripePaymentService->hasUsableBalanceTransaction($intent)) {
                Log::info('Stripe donation thank-you sync skipped: settlement not ready.', [
                    'payment_intent_id' => $paymentIntentId,
                ]);

                return;
            }

            $metadata = $this->stripePaymentService->donationMetadataFromPaymentIntent($intent);
            if (! $this->isSiteDonation($metadata)) {
                Log::info('Stripe donation thank-you sync skipped: not a site donation.', [
                    'payment_intent_id' => $paymentIntentId,
                ]);

                return;
            }

            $this->donationIngestService->ingest($this->payloadMapper->fromPaymentIntent($intent));
        } catch (StripeSettlementNotReadyException $exception) {
            Log::info('Stripe donation thank-you sync skipped: settlement not ready.', [
                'payment_intent_id' => $paymentIntentId,
            ]);
        } catch (RuntimeException $exception) {
            Log::warning('Stripe donation thank-you sync failed.', [
                'payment_intent_id' => $paymentIntentId,
                'reason' => $exception->getMessage(),
            ]);
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    private function isSiteDonation(array $metadata): bool
    {
        foreach (['campaign_id', 'stripe_subscription_id'] as $key) {
            $value = $metadata[$key] ?? null;
            if (is_string($value) && trim($value) !== '') {
                return true;
            }
        }

        return false;
    }
}
