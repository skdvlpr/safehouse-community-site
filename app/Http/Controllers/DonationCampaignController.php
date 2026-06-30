<?php

namespace App\Http\Controllers;

use App\Models\DonationCampaign;
use App\Services\Payments\StripePaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DonationCampaignController extends Controller
{
    public function index(Request $request): View
    {
        $campaigns = DonationCampaign::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('donations.index', [
            'campaigns' => $campaigns,
        ]);
    }

    public function show(string $locale, string $campaignSlug): View
    {
        $campaign = DonationCampaign::query()->where('slug', $campaignSlug)->firstOrFail();
        abort_unless($campaign->is_active, 404);

        return view('donations.show', [
            'campaign' => $campaign,
            'stripeMock' => StripePaymentService::mockModeEnabled(),
        ]);
    }

    public function privacy(string $locale, string $campaignSlug): View
    {
        $campaign = DonationCampaign::query()->where('slug', $campaignSlug)->firstOrFail();
        abort_unless($campaign->is_active, 404);

        return view('donations.privacy', ['campaign' => $campaign]);
    }

    public function thankYou(Request $request, string $locale, string $campaignSlug): View|RedirectResponse
    {
        $campaign = DonationCampaign::query()->where('slug', $campaignSlug)->firstOrFail();
        abort_unless($campaign->is_active, 404);

        $paymentIntentId = (string) $request->query('payment_intent', '');
        if ($paymentIntentId === '') {
            return redirect()
                ->route('donations.show', ['locale' => $locale, 'campaignSlug' => $campaignSlug])
                ->with('donation_notice', __('Completa il pagamento dalla pagina della raccolta.'));
        }

        return view('donations.thank-you', [
            'campaign' => $campaign,
            'paymentIntentId' => $paymentIntentId,
        ]);
    }
}
