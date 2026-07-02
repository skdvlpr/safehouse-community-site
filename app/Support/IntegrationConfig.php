<?php

namespace App\Support;

use App\Services\SiteSettingsService;

class IntegrationConfig
{
    public static function get(string $key, mixed $default = null): mixed
    {
        $definitions = config('site_settings.keys', []);

        if (isset($definitions[$key])) {
            $value = app(SiteSettingsService::class)->get($key);

            if ($value !== '') {
                return $value;
            }
        }

        $configKey = $definitions[$key]['config'] ?? $key;

        return config($configKey, $default);
    }

    public static function string(string $key, string $default = ''): string
    {
        $value = self::get($key, $default);

        return is_string($value) ? $value : (string) $value;
    }
}
