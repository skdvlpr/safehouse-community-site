<?php

namespace App\Console\Commands;

use App\Models\SiteSetting;
use App\Services\Payments\StripeCustomerPortalService;
use App\Services\SiteSettingsService;
use Illuminate\Console\Command;

class SyncStripeCustomerPortalUrlCommand extends Command
{
    protected $signature = 'stripe:sync-customer-portal-url {--persist : Save resolved URL into CMS site settings}';

    protected $description = 'Resolve Stripe Customer Portal login URL for the configured API key mode (test or live)';

    public function handle(SiteSettingsService $settings): int
    {
        $service = StripeCustomerPortalService::fromConfig();
        $service->forgetCache();
        $url = $service->loginUrl();

        if ($url === null || $url === '') {
            $this->error('Could not resolve Customer Portal login URL. Check Stripe secret key permissions and Dashboard portal settings.');

            return self::FAILURE;
        }

        $mode = str_contains($url, '/login/test_') ? 'TEST' : 'LIVE';
        $this->info("Resolved ({$mode}): {$url}");

        if ($this->option('persist')) {
            SiteSetting::query()->updateOrCreate(
                ['key' => 'stripe.customer_portal_login_url'],
                ['value' => $url],
            );
            $settings->forgetCache('stripe.customer_portal_login_url');
            $service->forgetCache();
            $this->info('Saved to CMS site setting stripe.customer_portal_login_url');
        }

        if ($mode === 'TEST') {
            $this->warn('This is a TEST portal URL (keys are sk_test_/pk_test_). For LIVE: put sk_live_ in CMS Integrations, then re-run with --persist.');
        }

        return self::SUCCESS;
    }
}
