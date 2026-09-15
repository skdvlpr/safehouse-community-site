<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Donations\DonationIngestPayloadMapper;
use App\Services\Donations\DonationIngestService;
use App\Services\Payments\MockStripePaymentService;
use App\Services\Payments\StripePaymentService;
use App\Support\PublicRequestLocale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class MockDonationCompleteController extends Controller
{
    public function __construct(
        private readonly DonationIngestService $donationIngestService,
        private readonly DonationIngestPayloadMapper $payloadMapper,
    ) {}

    public function __invoke(Request $request, string $paymentIntent): JsonResponse
    {
        PublicRequestLocale::apply($request);

        abort_unless(StripePaymentService::mockModeEnabled(), 404);

        $stripe = app(StripePaymentService::class);
        if (! $stripe instanceof MockStripePaymentService) {
            abort(404);
        }

        try {
            $payload = $stripe->completeIntent($paymentIntent, $this->payloadMapper);
            $result = $this->donationIngestService->ingest($payload);
        } catch (RuntimeException $exception) {
            report($exception);

            return response()->json(['message' => __('site.donations.checkout_failed')], 422);
        }

        return response()->json([
            'status' => $result['status'],
            'prima_nota_id' => $result['prima_nota_id'],
            'payment_intent_id' => $paymentIntent,
        ]);
    }
}
