<?php

namespace App\Console\Commands;

use App\Models\Page;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Console\Command;

class SeedIfEmptyCommand extends Command
{
    protected $signature = 'db:seed-if-empty';

    protected $description = 'Seed demo CMS content when local database is missing pages or admin (DDEV only)';

    public function handle(): int
    {
        if (! app()->environment('local')) {
            $this->error('db:seed-if-empty is for local development only. It is not run on production deploy.');

            return self::FAILURE;
        }

        $needsContent = ! Page::query()->exists();
        $needsAdmin = ! User::query()
            ->where('email', 'admin@safehouse.community')
            ->exists();
        $needsIntegrations = ! SiteSetting::query()
            ->where('key', 'stripe.key')
            ->exists();

        if (! $needsContent && ! $needsAdmin && ! $needsIntegrations) {
            $this->line('Demo content, admin user, and integrations already present — skipping seed.');

            return self::SUCCESS;
        }

        if ($needsContent) {
            $this->warn('No pages found. Seeding demo content…');
            $this->call('db:seed', ['--force' => true]);
        } else {
            if ($needsAdmin) {
                $this->warn('Admin user missing. Restoring roles and admin account…');
                $this->call('db:seed', ['--class' => 'RoleSeeder', '--force' => true]);
                $this->call('db:seed', ['--class' => 'AdminUserSeeder', '--force' => true]);
            }

            if ($needsIntegrations) {
                $this->warn('Integration settings missing. Restoring Stripe test defaults…');
                $this->call('db:seed', ['--class' => 'DeploySiteContentSeeder', '--force' => true]);
                $this->call('db:seed', ['--class' => 'DeployIntegrationSeeder', '--force' => true]);
            }
        }

        $this->info('Local demo data restored.');

        return self::SUCCESS;
    }
}
