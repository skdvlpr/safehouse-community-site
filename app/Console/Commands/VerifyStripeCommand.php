<?php

namespace App\Console\Commands;

use App\Services\Payments\StripeAccountVerifier;
use Illuminate\Console\Command;

class VerifyStripeCommand extends Command
{
    protected $signature = 'stripe:verify';

    protected $description = 'Verify Stripe API keys, account readiness, and webhook configuration for donations';

    public function handle(StripeAccountVerifier $verifier): int
    {
        $checks = $verifier->runChecks();

        $this->components->info('Stripe donation account checks');
        $this->newLine();

        foreach ($checks as $check) {
            $line = "{$check['label']}: {$check['detail']}";

            match ($check['status']) {
                'pass' => $this->components->twoColumnDetail($check['label'], $check['detail']),
                default => $this->components->error($line),
            };
        }

        $this->newLine();

        if ($verifier->allPassed($checks)) {
            $this->components->info('All checks passed. You can accept donations with this Stripe account.');

            return self::SUCCESS;
        }

        $this->components->warn('Some checks failed. See docs/STRIPE-SETUP.md for association onboarding steps.');

        return self::FAILURE;
    }
}
