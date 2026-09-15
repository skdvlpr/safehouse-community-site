<?php

namespace App\Console\Commands;

use App\Models\Page;
use Illuminate\Console\Command;

/**
 * Import CMS pages from deploy-pages.php. Never runs on production.
 *
 * @see https://laravel.com/docs/13.x/artisan
 */
class ImportCmsPagesCommand extends Command
{
    protected $signature = 'site:import-cms-pages
                            {--force : Allow non-local non-production environments}';

    protected $description = 'Upsert CMS pages from deploy-pages.php (local only)';

    public function handle(): int
    {
        if (app()->environment('production')) {
            $this->error('Refusing to import CMS pages on production.');

            return self::FAILURE;
        }

        if (! app()->environment('local') && ! $this->option('force')) {
            $this->error('Refusing: APP_ENV is not local. Pass --force only on non-production.');

            return self::FAILURE;
        }

        $path = database_path('seeders/data/deploy-pages.php');

        if (! is_file($path)) {
            $this->error('Missing '.$path);

            return self::FAILURE;
        }

        /** @var mixed $pages */
        $pages = require $path;

        if (! is_array($pages)) {
            $this->error('deploy-pages.php must return a list of pages.');

            return self::FAILURE;
        }

        $count = 0;

        Page::withoutEvents(function () use ($pages, &$count): void {
            foreach ($pages as $attributes) {
                if (! is_array($attributes) || ! is_string($attributes['key'] ?? null)) {
                    continue;
                }

                Page::query()->updateOrCreate(
                    ['key' => $attributes['key']],
                    [
                        'template' => $attributes['template'] ?? 'default',
                        'is_published' => (bool) ($attributes['is_published'] ?? false),
                        'title' => $attributes['title'] ?? [],
                        'slug' => $attributes['slug'] ?? [],
                        'body' => $attributes['body'] ?? [],
                        'meta' => $attributes['meta'] ?? null,
                    ],
                );
                $count++;
                $this->line('Upserted '.$attributes['key']);
            }
        });

        $this->info("Imported {$count} pages. Integration settings were not touched.");

        return self::SUCCESS;
    }
}
