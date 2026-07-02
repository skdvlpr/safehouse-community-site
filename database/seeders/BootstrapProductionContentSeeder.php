<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\DonationCampaign;
use App\Models\Page;
use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

/**
 * One-time production bootstrap: seeds demo/core content only when tables are empty.
 * Never run automatically on deploy — CMS edits must persist across releases.
 */
class BootstrapProductionContentSeeder extends Seeder
{
    public function run(): void
    {
        if (! Page::query()->exists()) {
            $this->command?->info('Bootstrap: seeding core pages…');
            $this->call(PageSeeder::class);
        }

        if (! DonationCampaign::query()->exists()) {
            $this->command?->info('Bootstrap: seeding donation campaigns…');
            $this->call(DonationCampaignSeeder::class);
        }

        if (! Article::query()->exists()) {
            $this->command?->info('Bootstrap: seeding articles…');
            $this->call(DeployArticleSeeder::class);
        }

        if (! $this->hasPrimaryTagline()) {
            $this->command?->info('Bootstrap: seeding site content…');
            $this->call(DeploySiteContentSeeder::class);
        }

        $this->command?->info('Production bootstrap finished (existing CMS data was left unchanged).');
    }

    private function hasPrimaryTagline(): bool
    {
        $setting = SiteSetting::query()->where('key', 'content.primary_tagline')->first();
        $raw = $setting?->decryptedValue();

        if (! is_string($raw) || $raw === '') {
            return false;
        }

        $decoded = json_decode($raw, true);

        if (! is_array($decoded)) {
            return false;
        }

        foreach ($decoded as $text) {
            if (is_string($text) && trim($text) !== '') {
                return true;
            }
        }

        return false;
    }
}
