<?php

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

class DonationSettingsService
{
    private const CACHE_KEY = 'donations_settings:payload';

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function (): array {
            $stored = $this->readFromDatabase();

            return $this->mergeWithDefaults($stored);
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function fivePerMille(): array
    {
        /** @var array<string, mixed> $section */
        $section = $this->all()['five_per_mille'] ?? [];

        return $section;
    }

    /**
     * @return array<string, mixed>
     */
    public function bankTransfer(): array
    {
        /** @var array<string, mixed> $section */
        $section = $this->all()['bank_transfer'] ?? [];

        return $section;
    }

    public function fivePerMilleEnabled(): bool
    {
        return (bool) ($this->fivePerMille()['enabled'] ?? true);
    }

    public function bankTransferEnabled(): bool
    {
        return (bool) ($this->bankTransfer()['enabled'] ?? true);
    }

    public function localized(array $section, string $field, ?string $locale = null): string
    {
        $locale ??= app()->getLocale();
        $map = $section[$field] ?? [];

        if (! is_array($map)) {
            return is_string($map) ? trim($map) : '';
        }

        $value = trim((string) ($map[$locale] ?? ''));

        if ($value !== '') {
            return $value;
        }

        return trim((string) ($map['it'] ?? ''));
    }

    public function codiceFiscale(): string
    {
        return strtoupper(trim((string) ($this->fivePerMille()['codice_fiscale'] ?? '')));
    }

    public function iban(): string
    {
        return strtoupper(preg_replace('/\s+/', '', trim((string) ($this->bankTransfer()['iban'] ?? ''))) ?? '');
    }

    /**
     * @param  array<string, mixed>  $formState
     */
    public function saveFromFormState(array $formState): void
    {
        $donations = is_array($formState['donations'] ?? null) ? $formState['donations'] : [];

        $normalized = [
            'five_per_mille' => $this->normalizeFivePerMille(is_array($donations['five_per_mille'] ?? null) ? $donations['five_per_mille'] : []),
            'bank_transfer' => $this->normalizeBankTransfer(is_array($donations['bank_transfer'] ?? null) ? $donations['bank_transfer'] : []),
        ];

        if (! Schema::hasTable('site_settings')) {
            Cache::forever(self::CACHE_KEY, $this->mergeWithDefaults($normalized));

            return;
        }

        $setting = SiteSetting::query()->firstOrNew(['key' => (string) config('donations.storage_key')]);
        $setting->storePlaintext(json_encode($normalized, JSON_THROW_ON_ERROR), false);
        $setting->save();

        Cache::forget(self::CACHE_KEY);
    }

    /**
     * @return array{donations: array<string, mixed>}
     */
    public function nestedFormValues(): array
    {
        return [
            'donations' => $this->all(),
        ];
    }

    public function forgetCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * @return array<string, mixed>
     */
    private function readFromDatabase(): array
    {
        if (! Schema::hasTable('site_settings')) {
            return [];
        }

        $stored = SiteSetting::query()
            ->where('key', (string) config('donations.storage_key'))
            ->first();

        $raw = $stored?->decryptedValue();

        if ($raw === null || trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @param  array<string, mixed>  $stored
     * @return array<string, mixed>
     */
    private function mergeWithDefaults(array $stored): array
    {
        /** @var array<string, mixed> $defaults */
        $defaults = config('donations', []);

        return [
            'five_per_mille' => array_replace_recursive(
                is_array($defaults['five_per_mille'] ?? null) ? $defaults['five_per_mille'] : [],
                is_array($stored['five_per_mille'] ?? null) ? $stored['five_per_mille'] : [],
            ),
            'bank_transfer' => array_replace_recursive(
                is_array($defaults['bank_transfer'] ?? null) ? $defaults['bank_transfer'] : [],
                is_array($stored['bank_transfer'] ?? null) ? $stored['bank_transfer'] : [],
            ),
        ];
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    private function normalizeFivePerMille(array $input): array
    {
        return [
            'enabled' => filter_var($input['enabled'] ?? true, FILTER_VALIDATE_BOOL),
            'codice_fiscale' => strtoupper(trim((string) ($input['codice_fiscale'] ?? ''))),
            'menu_label' => $this->normalizeLocaleMap($input['menu_label'] ?? [], 80),
            'heading' => $this->normalizeLocaleMap($input['heading'] ?? [], 120),
            'lead' => $this->normalizeLocaleMap($input['lead'] ?? [], 500),
            'body' => $this->normalizeLocaleMap($input['body'] ?? [], 65535, sanitizeHtml: true),
            'instructions' => $this->normalizeLocaleMap($input['instructions'] ?? [], 65535, sanitizeHtml: true),
            'codice_label' => $this->normalizeLocaleMap($input['codice_label'] ?? [], 120),
        ];
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    private function normalizeBankTransfer(array $input): array
    {
        return [
            'enabled' => filter_var($input['enabled'] ?? true, FILTER_VALIDATE_BOOL),
            'iban' => strtoupper(preg_replace('/\s+/', '', trim((string) ($input['iban'] ?? ''))) ?? ''),
            'beneficiary' => trim((string) ($input['beneficiary'] ?? '')),
            'heading' => $this->normalizeLocaleMap($input['heading'] ?? [], 120),
            'body' => $this->normalizeLocaleMap($input['body'] ?? [], 65535, sanitizeHtml: true),
            'iban_label' => $this->normalizeLocaleMap($input['iban_label'] ?? [], 40),
            'beneficiary_label' => $this->normalizeLocaleMap($input['beneficiary_label'] ?? [], 80),
        ];
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, string>
     */
    private function normalizeLocaleMap(array $values, int $maxLength, bool $sanitizeHtml = false): array
    {
        $normalized = [];

        foreach ($values as $locale => $value) {
            if (! is_string($locale) || ! is_string($value)) {
                continue;
            }

            $value = trim($value);

            if ($value === '') {
                continue;
            }

            if ($sanitizeHtml) {
                $value = $this->sanitizeHtml($value);
            }

            $normalized[$locale] = mb_substr($value, 0, $maxLength);
        }

        return $normalized;
    }

    private function sanitizeHtml(string $html): string
    {
        $config = (new HtmlSanitizerConfig)
            ->allowSafeElements()
            ->allowRelativeLinks()
            ->allowRelativeMedias();

        return (new HtmlSanitizer($config))->sanitize($html);
    }
}
