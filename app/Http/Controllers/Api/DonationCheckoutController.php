<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateDonationIntentRequest;
use App\Models\DonationCampaign;
use App\Services\Payments\StripePaymentService;
use App\Support\IntegrationConfig;
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
            $contact = $request->donorContact();
            $amountCents = (int) $request->validated('amount_cents');
            $donorName = (string) $request->validated('donor_name');
            $donorType = (string) $request->validated('donor_type');
            $comment = $request->validated('comment');

            $result = $campaign->allowsRecurring()
                ? $this->stripePaymentService->createDonationSubscription(
                    $campaign,
                    $amountCents,
                    $donorName,
                    $donorType,
                    $comment,
                    $contact->email,
                    $contact->phone,
                )
                : $this->stripePaymentService->createDonationIntent(
                    $campaign,
                    $amountCents,
                    $donorName,
                    $donorType,
                    $comment,
                    $contact->email,
                    $contact->phone,
                );
        } catch (RuntimeException $exception) {
            report($exception);

            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json([
            'client_secret' => $result['client_secret'],
            'payment_intent_id' => $result['payment_intent_id'],
            'subscription_id' => $result['subscription_id'] ?? null,
            'publishable_key' => IntegrationConfig::string('stripe.key') ?: config('stripe.mock_publishable_key'),
            'mock' => StripePaymentService::mockModeEnabled(),
            'complete_url' => StripePaymentService::mockModeEnabled()
                ? route('api.donations.mock.complete', ['paymentIntent' => $result['payment_intent_id']])
                : null,
        ]);
    }
}
