<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TurnstileVerifier
{
    public function __construct(
        private readonly SiteSettingsService $settings,
    ) {}

    public function enabled(): bool
    {
        if (! $this->settings->has('turnstile.enabled')) {
            return false;
        }

        return $this->settings->isTruthy('turnstile.enabled')
            && $this->siteKey() !== ''
            && $this->secretKey() !== '';
    }

    public function siteKey(): string
    {
        $fromDb = trim((string) ($this->settings->getRaw('turnstile.site_key') ?? ''));

        return $fromDb !== '' ? $fromDb : trim((string) config('turnstile.site_key', ''));
    }

    public function verify(?string $response, ?string $remoteIp = null): bool
    {
        if (! $this->enabled()) {
            return true;
        }

        $response = trim((string) $response);

        if ($response === '') {
            return false;
        }

        try {
            $payload = [
                'secret' => $this->secretKey(),
                'response' => $response,
            ];

            if ($remoteIp !== null && $remoteIp !== '') {
                $payload['remoteip'] = $remoteIp;
            }

            $result = Http::asForm()
                ->timeout(10)
                ->post((string) config('turnstile.verify_url'), $payload)
                ->json();

            return is_array($result) && ($result['success'] ?? false) === true;
        } catch (\Throwable $exception) {
            Log::warning('Turnstile verification failed', [
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    private function secretKey(): string
    {
        $fromDb = trim((string) ($this->settings->getRaw('turnstile.secret_key') ?? ''));

        return $fromDb !== '' ? $fromDb : trim((string) config('turnstile.secret_key', ''));
    }
}
