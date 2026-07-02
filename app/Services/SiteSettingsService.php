<?php

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class SiteSettingsService
{
    private const CACHE_PREFIX = 'site_setting:';

    /**
     * Resolved value: DB override when non-empty, otherwise config / .env fallback.
     */
    public function get(string $key): string
    {
        return Cache::rememberForever(self::CACHE_PREFIX.$key, function () use ($key): string {
            $definition = $this->definition($key);

            if ($definition === null) {
                return '';
            }

            if (! Schema::hasTable('site_settings')) {
                return (string) config($definition['config'], '');
            }

            $stored = SiteSetting::query()->where('key', $key)->first();
            $dbValue = $stored?->decryptedValue();

            if ($dbValue !== null && $dbValue !== '') {
                return $dbValue;
            }

            return (string) config($definition['config'], '');
        });
    }

    /**
     * @return array<string, string>
     */
    public function formValues(): array
    {
        $values = [];

        foreach (array_keys($this->definitions()) as $key) {
            $definition = $this->definition($key);
            $resolved = $this->get($key);

            if ($definition !== null && (bool) $definition['encrypted'] && $resolved !== '') {
                $values[$key] = '';

                continue;
            }

            $values[$key] = $resolved;
        }

        return $values;
    }

    /**
     * Filament nests dotted field names (stripe.key → ['stripe' => ['key' => …]]).
     *
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
     * @param  array<string, mixed>  $formState
     * @return array<string, string|null>
     */
    public function flattenFormState(array $formState): array
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
     * @param  array<string, string|null>  $flat
     * @return array<string, mixed>
     */
    public function nestFormState(array $flat): array
    {
        /** @var array<string, mixed> $nested */
        $nested = Arr::undot($flat);

        return $nested;
    }

    /**
     * @param  array<string, string|null>  $input
     */
    public function updateMany(array $input): void
    {
        foreach ($this->definitions() as $key => $definition) {
            if (! array_key_exists($key, $input)) {
                continue;
            }

            $value = $input[$key];
            $plaintext = is_string($value) ? trim($value) : '';

            if ($plaintext === '' && (bool) $definition['encrypted']) {
                continue;
            }

            if (! Schema::hasTable('site_settings')) {
                continue;
            }

            $setting = SiteSetting::query()->firstOrNew(['key' => $key]);
            $setting->storePlaintext($plaintext === '' ? null : $plaintext, (bool) $definition['encrypted']);
            $setting->save();

            Cache::forget(self::CACHE_PREFIX.$key);
        }
    }

    public function forgetCache(): void
    {
        foreach (array_keys($this->definitions()) as $key) {
            Cache::forget(self::CACHE_PREFIX.$key);
        }
    }

    /**
     * @return array<string, array{label: string, group: string, encrypted: bool, config: string}>|null
     */
    public function definition(string $key): ?array
    {
        return $this->definitions()[$key] ?? null;
    }

    /**
     * @return array<string, array{label: string, group: string, encrypted: bool, config: string}>
     */
    public function definitions(): array
    {
        /** @var array<string, array{label: string, group: string, encrypted: bool, config: string}> $keys */
        $keys = config('site_settings.keys', []);

        return $keys;
    }
}
