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
            'ip_hash' => ContactSubmissionService::hashIp($request->ip()) ?? hash_hmac('sha256', 'unknown', (string) config('app.key')),
            'user_agent_hash' => ContactSubmissionService::hashUserAgent($request->userAgent()),
            'consented_at' => now(),
        ]);
    }
}
