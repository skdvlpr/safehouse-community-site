<?php

namespace App\Services\EspoCrm;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class EspoCrmCaseTypeOptions
{
    private const CACHE_KEY = 'espocrm:case.type.options';

    private const CACHE_SECONDS = 300;

    /**
     * Options for Filament Select: value => label.
     * Never throws — falls back to default desk case types when CRM is offline.
     *
     * @return array<string, string>
     */
    public function optionsForSelect(): array
    {
        $fromCrm = $this->fetchFromCrmCached();

        if ($fromCrm !== []) {
            return $fromCrm;
        }

        return $this->fallbackOptions();
    }

    public function isLoadedFromCrm(): bool
    {
        return $this->fetchFromCrmCached() !== [];
    }

    /**
     * @return array<string, string>
     */
    private function fetchFromCrmCached(): array
    {
        try {
            /** @var array<string, string> $cached */
            $cached = Cache::remember(self::CACHE_KEY, self::CACHE_SECONDS, function (): array {
                return $this->fetchFromCrm();
            });

            return $cached;
        } catch (Throwable $exception) {
            Log::warning('EspoCRM Case.type options cache failed', [
                'error' => $exception->getMessage(),
            ]);

            return $this->fetchFromCrm();
        }
    }

    /**
     * @return array<string, string>
     */
    private function fetchFromCrm(): array
    {
        $client = EspoCrmClient::tryFromConfig();

        if ($client === null) {
            return [];
        }

        try {
            $field = $client->metadata('entityDefs.Case.fields.type');
            $options = $field['options'] ?? null;

            if (! is_array($options) || $options === []) {
                return [];
            }

            $mapped = [];

            foreach ($options as $option) {
                if (! is_string($option) || trim($option) === '') {
                    continue;
                }

                $mapped[$option] = $option;
            }

            return $mapped;
        } catch (Throwable $exception) {
            Log::warning('EspoCRM Case.type options fetch failed', [
                'error' => $exception->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * @return array<string, string>
     */
    private function fallbackOptions(): array
    {
        $mapped = [];

        /** @var list<array<string, mixed>> $defaults */
        $defaults = config('contact_mail.default_desks', []);

        foreach ($defaults as $desk) {
            $caseType = trim((string) ($desk['case_type'] ?? ''));

            if ($caseType === '') {
                continue;
            }

            $mapped[$caseType] = $caseType;
        }

        $mapped['Other'] = 'Other';

        return $mapped;
    }

    public function forgetCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
