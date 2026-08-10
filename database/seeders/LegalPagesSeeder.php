<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Page;
use Database\Seeders\Data\LegalPagesContent;
use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * One-shot upsert of privacy + cookie legal pages.
 *
 * After a successful non-test run, this seeder and LegalPagesContent self-delete
 * so a later accidental `db:seed --class=LegalPagesSeeder` cannot overwrite CMS edits.
 *
 * Debug keep: SAFEHOUSE_KEEP_ONESHOT=1
 * PHPUnit: files are kept (runningUnitTests).
 */
class LegalPagesSeeder extends Seeder
{
    public function run(): void
    {
        if (! class_exists(LegalPagesContent::class)) {
            throw new RuntimeException(
                'LegalPagesContent missing — legal oneshot already applied or files were removed. '.
                'Edit privacy/cookie pages in Filament CMS instead of re-seeding.'
            );
        }

        Page::withoutEvents(function (): void {
            foreach (LegalPagesContent::pages() as $key => $attributes) {
                Page::query()->updateOrCreate(
                    ['key' => $key],
                    $attributes,
                );
            }
        });

        $this->command?->info('Legal privacy/cookie pages upserted.');

        $this->selfDestructAfterSuccess();
    }

    private function selfDestructAfterSuccess(): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        if (getenv('SAFEHOUSE_KEEP_ONESHOT') === '1') {
            $this->command?->warn('SAFEHOUSE_KEEP_ONESHOT=1 — keeping LegalPagesSeeder files.');

            return;
        }

        $files = [
            __DIR__.'/LegalPagesSeeder.php',
            __DIR__.'/Data/LegalPagesContent.php',
        ];

        foreach ($files as $file) {
            $real = realpath($file) ?: $file;
            if (! is_file($real)) {
                continue;
            }

            if (@unlink($real)) {
                $this->command?->warn("ONESHOT: deleted {$real}");
            } else {
                $this->command?->error("WARN: failed to delete {$real}");
            }
        }
    }
}
