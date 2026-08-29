<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Page;
use Database\Seeders\Data\LegalPagesContent;
use Illuminate\Console\Command;
use RuntimeException;

/**
 * Upsert privacy + cookie page bodies from LegalPagesContent without self-deleting source files.
 */
class SyncLegalPagesCommand extends Command
{
    protected $signature = 'site:sync-legal-pages
                            {--force : Overwrite existing privacy/cookie page content in the database}';

    protected $description = 'Apply LegalPagesContent to privacy and cookie CMS pages (no oneshot self-delete)';

    public function handle(): int
    {
        if (! class_exists(LegalPagesContent::class)) {
            throw new RuntimeException(
                'LegalPagesContent missing. Restore database/seeders/Data/LegalPagesContent.php first.'
            );
        }

        if (! $this->option('force') && ! $this->confirm('Overwrite privacy and cookie page content in the database?')) {
            $this->warn('Aborted.');

            return self::FAILURE;
        }

        Page::withoutEvents(function (): void {
            foreach (LegalPagesContent::pages() as $key => $attributes) {
                Page::query()->updateOrCreate(
                    ['key' => $key],
                    $attributes,
                );
                $this->info("Upserted page key={$key}");
            }
        });

        $this->info('Legal privacy/cookie pages synced.');

        return self::SUCCESS;
    }
}
