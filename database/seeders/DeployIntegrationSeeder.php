<?php

namespace Database\Seeders;

use App\Services\SiteSettingsService;
use Illuminate\Database\Seeder;

class DeployIntegrationSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            return;
        }

        $path = database_path('seeders/data/local-integrations.php');

        if (! is_file($path)) {
            return;
        }

        /** @var array<string, string|null> $settings */
        $settings = require $path;

        $normalized = [];

        foreach ($settings as $key => $value) {
            if (! is_string($key)) {
                continue;
            }

            $plaintext = is_string($value) ? trim($value) : '';

            if ($plaintext === '') {
                continue;
            }

            $normalized[$key] = $plaintext;
        }

        if ($normalized === []) {
            return;
        }

        app(SiteSettingsService::class)->updateMany($normalized);
        app(SiteSettingsService::class)->forgetCache();
    }
}
