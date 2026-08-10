<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Page;
use Database\Seeders\Data\LegalPagesContent;
use Illuminate\Database\Seeder;

/**
 * Upserts only privacy + cookie legal pages (stable slugs for Google OAuth).
 * Safe to re-run on existing CMS data without rewriting other pages.
 */
class LegalPagesSeeder extends Seeder
{
    public function run(): void
    {
        // Skip UrlSlugSynchronizer so titles can be localised without rewriting
        // the public privacy-policy / cookie-policy URLs.
        Page::withoutEvents(function (): void {
            foreach (LegalPagesContent::pages() as $key => $attributes) {
                Page::query()->updateOrCreate(
                    ['key' => $key],
                    $attributes,
                );
            }
        });
    }
}
