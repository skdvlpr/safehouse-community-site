<?php

namespace App\Services;

use App\Models\GdprConsent;
use Illuminate\Http\Request;

class GdprConsentService
{
    public function recordCookieBanner(Request $request, string $level): GdprConsent
    {
        return GdprConsent::query()->create([
            'consent_type' => $level === 'all' ? 'cookie_banner_analytics' : 'cookie_banner_essential',
            'granted' => true,
            'ip_hash' => ContactSubmissionService::hashIp($request->ip()) ?? hash('sha256', 'unknown'),
            'consented_at' => now(),
        ]);
    }
}
