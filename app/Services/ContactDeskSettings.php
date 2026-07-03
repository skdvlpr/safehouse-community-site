<?php

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use InvalidArgumentException;

class ContactDeskSettings
{
    private const STORAGE_KEY = 'contact.desks';

    private const CACHE_KEY = 'contact_desks:all';

    /**
     * @return list<array{key: string, label: string, inbox: string, case_type: string}>
     */
    public function all(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function (): array {
            $stored = $this->readFromDatabase();

            if ($stored !== []) {
                return $stored;
            }

            return $this->normalizeList($this->defaults());
        });
    }

    /**
     * @return array<string, array{key: string, label: string, inbox: string, case_type: string}>
     */
    public function keyed(): array
    {
        $keyed = [];

        foreach ($this->all() as $desk) {
            $keyed[$desk['key']] = $desk;
        }

        return $keyed;
    }

    /**
     * @return array<string, string>
     */
    public function labelsForForm(): array
    {
        $options = [];

        foreach ($this->all() as $desk) {
            $options[$desk['key']] = $desk['label'];
        }

        return $options;
    }

    /**
     * @return array{key: string, label: string, inbox: string, case_type: string}|null
     */
    public function find(string $key): ?array
    {
        return $this->keyed()[$key] ?? null;
    }

    /**
     * @param  list<array<string, mixed>>|null  $desks
     */
    public function save(?array $desks): void
    {
        if ($desks === null || $desks === []) {
            throw new InvalidArgumentException('At least one sportello desk is required.');
        }

        $normalized = $this->normalizeList($desks);

        if ($normalized === []) {
            throw new InvalidArgumentException('At least one valid sportello desk is required.');
        }

        if (! Schema::hasTable('site_settings')) {
            Cache::forever(self::CACHE_KEY, $normalized);

            return;
        }

        $setting = SiteSetting::query()->firstOrNew(['key' => self::STORAGE_KEY]);
        $setting->storePlaintext(json_encode($normalized, JSON_THROW_ON_ERROR), false);
        $setting->save();

        Cache::forget(self::CACHE_KEY);
    }

    public function forgetCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * @return list<array{key: string, label: string, inbox: string, case_type: string}>
     */
    private function readFromDatabase(): array
    {
        if (! Schema::hasTable('site_settings')) {
            return [];
        }

        $stored = SiteSetting::query()->where('key', self::STORAGE_KEY)->first();
        $raw = $stored?->decryptedValue();

        if ($raw === null || trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);

        if (! is_array($decoded)) {
            return [];
        }

        return $this->normalizeList($decoded);
    }

    /**
     * @return list<array{key: string, label: string, inbox: string, case_type: string}>
     */
    private function defaults(): array
    {
        /** @var list<array<string, mixed>> $defaults */
        $defaults = config('contact_mail.default_desks', []);

        return $this->normalizeList($defaults);
    }

    /**
     * @param  list<array<string, mixed>>  $desks
     * @return list<array{key: string, label: string, inbox: string, case_type: string}>
     */
    private function normalizeList(array $desks): array
    {
        $normalized = [];
        $seenKeys = [];

        foreach ($desks as $desk) {
            if (! is_array($desk)) {
                continue;
            }

            $key = $this->normalizeKey((string) ($desk['key'] ?? ''));
            $label = trim((string) ($desk['label'] ?? ''));
            $inbox = strtolower(trim((string) ($desk['inbox'] ?? '')));
            $caseType = trim((string) ($desk['case_type'] ?? ''));

            if ($key === '' || $label === '' || $inbox === '' || $caseType === '') {
                continue;
            }

            if (filter_var($inbox, FILTER_VALIDATE_EMAIL) === false) {
                continue;
            }

            if (isset($seenKeys[$key])) {
                continue;
            }

            $seenKeys[$key] = true;

            $normalized[] = [
                'key' => $key,
                'label' => $label,
                'inbox' => $inbox,
                'case_type' => $caseType,
            ];
        }

        return $normalized;
    }

    private function normalizeKey(string $key): string
    {
        $key = Str::slug($key, '_');

        return preg_replace('/[^a-z0-9_]/', '', $key) ?? '';
    }
}
