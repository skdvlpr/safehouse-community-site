<?php

namespace App\Http\Controllers;

use App\Models\DonationCampaign;
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

    public function show(string $donationCampaign): View
    {
        $campaign = DonationCampaign::query()->where('slug', $donationCampaign)->firstOrFail();
        abort_unless($campaign->is_active, 404);

        return view('donations.show', ['campaign' => $campaign]);
    }

    public function privacy(string $donationCampaign): View
    {
        $campaign = DonationCampaign::query()->where('slug', $donationCampaign)->firstOrFail();
        abort_unless($campaign->is_active, 404);

        return view('donations.privacy', ['campaign' => $campaign]);
    }

    public function thankYou(string $donationCampaign): View
    {
        $campaign = DonationCampaign::query()->where('slug', $donationCampaign)->firstOrFail();
        abort_unless($campaign->is_active, 404);

        return view('donations.thank-you', ['campaign' => $campaign]);
    }
}
