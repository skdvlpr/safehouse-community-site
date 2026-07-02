<?php

namespace App\Console\Commands;

use App\Services\EspoCrm\EspoCrmVerifier;
use Illuminate\Console\Command;

class VerifyEspoCrmCommand extends Command
{
    protected $signature = 'espo:verify';

    protected $description = 'Verify EspoCRM API key, assigned user id, and Prima Nota access for donation ingest';

    public function handle(EspoCrmVerifier $verifier): int
    {
        $checks = $verifier->runChecks();

        $this->components->info('EspoCRM donation ingest checks');
        $this->newLine();

        foreach ($checks as $check) {
            $line = "{$check['label']}: {$check['detail']}";

            match ($check['status']) {
                'pass' => $this->components->twoColumnDetail($check['label'], $check['detail']),
                'warn' => $this->components->warn($line),
                default => $this->components->error($line),
            };
        }

        $this->newLine();

        if ($verifier->allPassed($checks)) {
            $this->components->info('EspoCRM is ready to receive donation Prima Nota records.');

            return self::SUCCESS;
        }

        $this->components->warn('Fix the items above before testing donations → CRM.');

        return self::FAILURE;
    }
}
