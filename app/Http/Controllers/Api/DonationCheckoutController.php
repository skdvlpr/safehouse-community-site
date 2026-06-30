<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateDonationIntentRequest;
use App\Models\DonationCampaign;
use App\Services\Payments\StripePaymentService;
use Illuminate\Http\JsonResponse;
use RuntimeException;

class DonationCheckoutController extends Controller
{
    public function __construct(
        private readonly StripePaymentService $stripePaymentService,
    ) {}

    public function store(CreateDonationIntentRequest $request, string $donationCampaign): JsonResponse
    {
        $campaign = DonationCampaign::query()->where('slug', $donationCampaign)->firstOrFail();

        if (! $campaign->is_active) {
            return response()->json(['message' => 'Campaign is not active.'], 404);
        }

        try {
            $result = $this->stripePaymentService->createDonationIntent(
                $campaign,
                (int) $request->validated('amount_cents'),
                (string) $request->validated('donor_name'),
                (string) $request->validated('donor_type'),
                $request->validated('comment'),
            );
        } catch (RuntimeException $exception) {
            report($exception);

            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json([
            'client_secret' => $result['client_secret'],
            'payment_intent_id' => $result['payment_intent_id'],
            'publishable_key' => config('stripe.key') ?: config('stripe.mock_publishable_key'),
            'mock' => StripePaymentService::mockModeEnabled(),
            'complete_url' => StripePaymentService::mockModeEnabled()
                ? route('api.donations.mock.complete', ['paymentIntent' => $result['payment_intent_id']])
                : null,
        ]);
    }
}
