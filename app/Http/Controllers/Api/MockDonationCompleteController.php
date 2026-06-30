<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Donations\DonationIngestPayloadMapper;
use App\Services\Donations\DonationIngestService;
use App\Services\Payments\MockStripePaymentService;
use App\Services\Payments\StripePaymentService;
use Illuminate\Http\JsonResponse;
use RuntimeException;

class MockDonationCompleteController extends Controller
{
    public function __construct(
        private readonly DonationIngestService $donationIngestService,
        private readonly DonationIngestPayloadMapper $payloadMapper,
    ) {}

    public function __invoke(string $paymentIntent): JsonResponse
    {
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

            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json([
            'status' => $result['status'],
            'prima_nota_id' => $result['prima_nota_id'],
            'payment_intent_id' => $paymentIntent,
        ]);
    }
}
