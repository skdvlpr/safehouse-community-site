<?php

namespace App\Http\Controllers;

use App\Exceptions\StripeSettlementNotReadyException;
use App\Exceptions\UnsupportedCurrencyException;
use App\Services\Donations\DonationIngestPayloadMapper;
use App\Services\Donations\DonationIngestService;
use App\Services\Donations\PrimaNotaPaymentStatusService;
use App\Services\Payments\StripePaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Stripe\Exception\SignatureVerificationException;
use UnexpectedValueException;

class StripeWebhookController extends Controller
{
    public function __construct(
        private readonly StripePaymentService $stripePaymentService,
        private readonly DonationIngestService $donationIngestService,
        private readonly DonationIngestPayloadMapper $payloadMapper,
        private readonly PrimaNotaPaymentStatusService $paymentStatusService,
    ) {}

    public function __invoke(Request $request): Response
    {
        try {
            $event = $this->stripePaymentService->constructWebhookEvent(
                $request->getContent(),
                $request->header('Stripe-Signature'),
            );
        } catch (SignatureVerificationException|UnexpectedValueException|RuntimeException $exception) {
            report($exception);

            return response('Invalid signature', 400);
        }

        $intentId = null;

        try {
            $statusResult = $this->paymentStatusService->applyFromStripeEvent($event);
            if ($statusResult['handled']) {
                return response('OK', 200);
            }

            $intentId = $this->stripePaymentService->paymentIntentIdFromWebhookEvent($event);
            if ($intentId === null) {
                return response('Ignored', 200);
            }

            // Single attempt, no sleep loop: Stripe wants a fast 2xx and `charge.updated`
            // re-delivers once the balance transaction exists (asynchronous capture).
            // https://docs.stripe.com/webhooks
            // https://docs.stripe.com/payments/payment-intents/asynchronous-capture
            $settledIntent = $this->stripePaymentService->retrieveSettledPaymentIntent($intentId, 1);

            // FR-009: only site donations (campaign or subscription metadata) reach Prima Nota.
            $metadata = $this->stripePaymentService->donationMetadataFromPaymentIntent($settledIntent);
            if (! $this->isSiteDonation($metadata)) {
                return response('Ignored', 200);
            }

            $this->donationIngestService->ingest($this->payloadMapper->fromPaymentIntent($settledIntent));
        } catch (StripeSettlementNotReadyException $exception) {
            // Must precede the generic RuntimeException catch: pending settlement is 200, not 502.
            // https://laravel.com/docs/13.x/logging
            Log::info('Stripe webhook donation pending: settlement not ready yet.', [
                'payment_intent_id' => $intentId,
            ]);

            return response('Pending settlement', 200);
        } catch (UnsupportedCurrencyException $exception) {
            Log::info('Stripe webhook donation skipped: unsupported currency.', [
                'error' => $exception->getMessage(),
            ]);

            return response('Skipped unsupported currency', 200);
        } catch (RuntimeException $exception) {
            report($exception);

            return response('CRM ingest failed', 502);
        }

        return response('OK', 200);
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
