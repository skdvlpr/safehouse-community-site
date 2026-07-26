<?php

namespace App\Http\Controllers;

use App\Models\DonationCampaign;
use App\Services\Donations\CampaignFundraisingProgressService;
use App\Services\Donations\StripeDonationThankYouSync;
use App\Services\DonationSettingsService;
use App\Services\Payments\StripeCustomerPortalService;
use App\Services\Payments\StripePaymentService;
use App\Services\RecurringDonationCampaignService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DonationCampaignController extends Controller
{
    public function index(
        Request $request,
        CampaignFundraisingProgressService $progressService,
        DonationSettingsService $donationSettings,
        RecurringDonationCampaignService $recurringDonations,
    ): View {
        $campaigns = DonationCampaign::query()
            ->active()
            ->oneTime()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('donations.index', [
            'campaigns' => $campaigns,
            'progressBySlug' => $progressService->forCampaigns($campaigns),
            'donationSettings' => $donationSettings,
            'recurringCampaign' => $recurringDonations->activeCampaign(),
        ]);
    }

    public function fivePerMille(string $locale, DonationSettingsService $donationSettings): View
    {
        abort_unless($donationSettings->fivePerMilleEnabled(), 404);

        return view('donations.five-per-mille', [
            'donationSettings' => $donationSettings,
            'locale' => $locale,
        ]);
    }

    public function show(string $locale, string $campaignSlug, CampaignFundraisingProgressService $progressService): View
    {
        $campaign = DonationCampaign::query()->where('slug', $campaignSlug)->firstOrFail();
        abort_unless($campaign->is_active, 404);

        return view('donations.show', [
            'campaign' => $campaign,
            'stripeMock' => StripePaymentService::mockModeEnabled(),
            'fundraisingProgress' => $campaign->allowsRecurring()
                ? null
                : $progressService->forCampaign($campaign),
            'customerPortalLoginUrl' => $this->customerPortalLoginUrl(),
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
            'isRecurring' => $campaign->allowsRecurring(),
            'customerPortalLoginUrl' => $this->customerPortalLoginUrl(),
        ]);
    }

    private function customerPortalLoginUrl(): ?string
    {
        return StripeCustomerPortalService::fromConfig()->loginUrl();
    }
}
