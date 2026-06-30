<?php

namespace App\Http\Controllers;

use App\Services\Donations\DonationIngestPayloadMapper;
use App\Services\Donations\DonationIngestService;
use App\Services\Payments\StripePaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use RuntimeException;

class StripeWebhookController extends Controller
{
    public function __construct(
        private readonly StripePaymentService $stripePaymentService,
        private readonly DonationIngestService $donationIngestService,
        private readonly DonationIngestPayloadMapper $payloadMapper,
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
            $this->donationIngestService->ingest($this->payloadMapper->fromPaymentIntent($intent));
        } catch (RuntimeException $exception) {
            report($exception);

            return response('CRM ingest failed', 502);
        }

        return response('OK', 200);
    }
}
