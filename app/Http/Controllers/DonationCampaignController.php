<?php

namespace App\Http\Controllers;

use App\Models\DonationCampaign;
use App\Services\Donations\CampaignFundraisingProgressService;
use App\Services\Donations\StripeDonationThankYouSync;
use App\Services\Payments\StripePaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DonationCampaignController extends Controller
{
    public function index(Request $request, CampaignFundraisingProgressService $progressService): View
    {
        $campaigns = DonationCampaign::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('donations.index', [
            'campaigns' => $campaigns,
            'progressBySlug' => $progressService->forCampaigns($campaigns),
        ]);
    }

    public function show(string $locale, string $campaignSlug, CampaignFundraisingProgressService $progressService): View
    {
        $campaign = DonationCampaign::query()->where('slug', $campaignSlug)->firstOrFail();
        abort_unless($campaign->is_active, 404);

        return view('donations.show', [
            'campaign' => $campaign,
            'stripeMock' => StripePaymentService::mockModeEnabled(),
            'fundraisingProgress' => $progressService->forCampaign($campaign),
        ]);
    }

    public function privacy(string $locale, string $campaignSlug): View
    {
        $campaign = DonationCampaign::query()->where('slug', $campaignSlug)->firstOrFail();
        abort_unless($campaign->is_active, 404);

        return view('donations.privacy', ['campaign' => $campaign]);
    }

    public function thankYou(
        Request $request,
        string $locale,
        string $campaignSlug,
        StripeDonationThankYouSync $stripeDonationThankYouSync,
    ): View|RedirectResponse {
        $campaign = DonationCampaign::query()->where('slug', $campaignSlug)->firstOrFail();
        abort_unless($campaign->is_active, 404);

        $paymentIntentId = (string) $request->query('payment_intent', '');
        if ($paymentIntentId === '') {
            return redirect()
                ->route('donations.show', ['locale' => $locale, 'campaignSlug' => $campaignSlug])
                ->with('donation_notice', __('Completa il pagamento dalla pagina della raccolta.'));
        }

        $stripeDonationThankYouSync->ingestSucceededPaymentIntent($paymentIntentId);

        $donorName = trim((string) $request->query('donor_name', ''));

        return view('donations.thank-you', [
            'campaign' => $campaign,
            'paymentIntentId' => $paymentIntentId,
            'donorName' => mb_substr($donorName, 0, 255),
            'thankYouHeading' => $campaign->thankYouHeading($donorName),
            'thankYouBody' => $campaign->thankYouBody($locale),
        ]);
    }
}
