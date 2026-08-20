<?php

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class SocialLinksSettings
{
    private const CACHE_KEY = 'social_links:filled';

    /**
     * @return list<array{key: string, label: string, href: string}>
     */
    public function filled(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function (): array {
            $values = $this->all();
            $filled = [];

            foreach (config('social_links.networks', []) as $key => $definition) {
                if (! is_string($key) || ! is_array($definition)) {
                    continue;
                }

                $raw = trim((string) ($values[$key] ?? ''));
                $href = $this->normalizeHref($key, $raw, (string) ($definition['type'] ?? 'url'));

                if ($href === null) {
                    continue;
                }

                $filled[] = [
                    'key' => $key,
                    'label' => (string) ($definition['label'] ?? $key),
                    'href' => $href,
                ];
            }

            return $filled;
        });
    }

    /**
     * @return array<string, string>
     */
    public function all(): array
    {
        $defaults = [];

        foreach (array_keys(config('social_links.networks', [])) as $key) {
            $defaults[$key] = '';
        }

        return array_merge($defaults, $this->readFromDatabase());
    }

    /**
     * @return array<string, mixed>
     */
    public function nestedFormValues(): array
    {
        return ['social' => $this->all()];
    }

    /**
     * @param  array<string, mixed>  $formState
     */
    public function saveFromFormState(array $formState): void
    {
        $social = $formState['social'] ?? [];

        if (! is_array($social)) {
            $social = [];
        }

        $normalized = [];

        foreach (array_keys(config('social_links.networks', [])) as $key) {
            $value = trim((string) ($social[$key] ?? ''));
            $normalized[$key] = $value;
        }

        $this->save($normalized);
    }

    /**
     * @param  array<string, string>  $links
     */
    public function save(array $links): void
    {
        if (! Schema::hasTable('site_settings')) {
            Cache::forever(self::CACHE_KEY, []);

            return;
        }

        $payload = [];

        foreach (array_keys(config('social_links.networks', [])) as $key) {
            $payload[$key] = trim((string) ($links[$key] ?? ''));
        }

        $setting = SiteSetting::query()->firstOrNew([
            'key' => (string) config('social_links.storage_key', 'social.links'),
        ]);
        $setting->storePlaintext(json_encode($payload, JSON_UNESCAPED_UNICODE), false);
        $setting->save();

        $this->forgetCache();
    }

    public function forgetCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * @return array<string, string>
     */
    private function readFromDatabase(): array
    {
        if (! Schema::hasTable('site_settings')) {
            return [];
        }

        $stored = SiteSetting::query()
            ->where('key', (string) config('social_links.storage_key', 'social.links'))
            ->first();
        $raw = $stored?->decryptedValue();

        if (! is_string($raw) || $raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);

        if (! is_array($decoded)) {
            return [];
        }

        $normalized = [];

        foreach ($decoded as $key => $value) {
            if (is_string($key) && is_string($value)) {
                $normalized[$key] = trim($value);
            }
        }

        return $normalized;
    }

    private function normalizeHref(string $key, string $raw, string $type): ?string
    {
        if ($raw === '') {
            return null;
        }

        if ($type === 'email' || $key === 'email') {
            $email = preg_replace('/^mailto:/i', '', $raw) ?? $raw;
            $email = trim($email);

            if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
                return null;
            }

            return 'mailto:'.$email;
        }

        if (! preg_match('#^https?://#i', $raw)) {
            $raw = 'https://'.$raw;
        }

        if (filter_var($raw, FILTER_VALIDATE_URL) === false) {
            return null;
        }

        $host = parse_url($raw, PHP_URL_HOST);

        if (! is_string($host) || $host === '' || ! str_contains($host, '.')) {
            return null;
        }

        return $raw;
    }
}
