<?php

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class SiteContentService
{
    private const CACHE_PREFIX = 'site_content:';

    public function primaryTagline(?string $locale = null): string
    {
        return $this->translatable('content.primary_tagline', $locale);
    }

    public function translatable(string $key, ?string $locale = null): string
    {
        $locale ??= app()->getLocale();
        $definitions = config('site_content.keys', []);
        $definition = is_array($definitions[$key] ?? null) ? $definitions[$key] : null;

        if (! is_array($definition)) {
            return '';
        }

        $stored = $this->decodedTranslationMap($key);

        if (isset($stored[$locale]) && $stored[$locale] !== '') {
            return $stored[$locale];
        }

        if (isset($stored['it']) && $stored['it'] !== '') {
            return $stored['it'];
        }

        $fallbackLang = $definition['fallback_lang'] ?? null;

        if (is_string($fallbackLang) && $fallbackLang !== '') {
            return (string) __($fallbackLang, [], $locale);
        }

        return '';
    }

    /**
     * @param  array<string, mixed>  $formState
     */
    public function updateFromFormState(array $formState): void
    {
        $this->updateMany($this->flattenFormState($formState));
    }

    /**
     * @return array<string, mixed>
     */
    public function nestedFormValues(): array
    {
        return $this->nestFormState($this->formValues());
    }

    /**
     * @param  array<string, string|null>  $input  e.g. content.primary_tagline.it => text
     */
    public function updateMany(array $input): void
    {
        if (! Schema::hasTable('site_settings')) {
            return;
        }

        $grouped = [];

        foreach ($input as $flatKey => $value) {
            if (! is_string($flatKey)) {
                continue;
            }

            foreach (array_keys(config('site_content.keys', [])) as $contentKey) {
                $prefix = $contentKey.'.';

                if (! str_starts_with($flatKey, $prefix)) {
                    continue;
                }

                $locale = substr($flatKey, strlen($prefix));
                $grouped[$contentKey][$locale] = is_string($value) ? trim($value) : '';
            }
        }

        foreach ($grouped as $contentKey => $translations) {
            $filtered = array_filter(
                $translations,
                static fn (string $text): bool => $text !== '',
            );

            $setting = SiteSetting::query()->firstOrNew(['key' => $contentKey]);
            $setting->storePlaintext(
                $filtered === [] ? null : json_encode($filtered, JSON_UNESCAPED_UNICODE),
                false,
            );
            $setting->save();

            Cache::forget(self::CACHE_PREFIX.$contentKey);
        }
    }

    /**
     * @return array<string, string>
     */
    public function formValues(): array
    {
        $values = [];

        foreach (array_keys(config('site_content.keys', [])) as $key) {
            $map = $this->decodedTranslationMap($key);

            foreach (config('locales.available', ['it', 'ru', 'en']) as $locale) {
                $values["{$key}.{$locale}"] = $map[$locale] ?? '';
            }
        }

        return $values;
    }

    public function forgetCache(): void
    {
        foreach (array_keys(config('site_content.keys', [])) as $key) {
            Cache::forget(self::CACHE_PREFIX.$key);
        }
    }

    /**
     * @return array<string, string>
     */
    private function decodedTranslationMap(string $key): array
    {
        return Cache::rememberForever(self::CACHE_PREFIX.$key, function () use ($key): array {
            if (! Schema::hasTable('site_settings')) {
                return [];
            }

            $stored = SiteSetting::query()->where('key', $key)->first();
            $raw = $stored?->decryptedValue();

            if (! is_string($raw) || $raw === '') {
                return [];
            }

            $decoded = json_decode($raw, true);

            if (! is_array($decoded)) {
                return [];
            }

            $normalized = [];

            foreach ($decoded as $locale => $text) {
                if (is_string($locale) && is_string($text) && $text !== '') {
                    $normalized[$locale] = $text;
                }
            }

            return $normalized;
        });
    }

    /**
     * @param  array<string, mixed>  $formState
     * @return array<string, string|null>
     */
    private function flattenFormState(array $formState): array
    {
        /** @var array<string, mixed> $flat */
        $flat = Arr::dot($formState);

        $normalized = [];

        foreach ($flat as $key => $value) {
            if (! is_string($key)) {
                continue;
            }

            if ($value === null) {
                $normalized[$key] = null;

                continue;
            }

            $normalized[$key] = is_string($value) ? $value : (string) $value;
        }

        return $normalized;
    }

    /**
     * @param  array<string, string>  $flat
     * @return array<string, mixed>
     */
    private function nestFormState(array $flat): array
    {
        /** @var array<string, mixed> $nested */
        $nested = Arr::undot($flat);

        return $nested;
    }
}
