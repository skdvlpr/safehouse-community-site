<?php

namespace App\Http\Middleware;

use App\Services\SiteSettingsService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DisableHttpCacheWhenEnabled
{
    public function __construct(
        private SiteSettingsService $siteSettings,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $this->enabled()) {
            return $response;
        }

        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }

    private function enabled(): bool
    {
        if ($this->siteSettings->definition('developer.no_cache') === null) {
            return (bool) config('developer.no_cache');
        }

        if ($this->siteSettings->has('developer.no_cache')) {
            return $this->siteSettings->isTruthy('developer.no_cache');
        }

        return (bool) config('developer.no_cache');
    }
}
