<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class BootstrapProductionContentCommand extends Command
{
    protected $signature = 'db:bootstrap-production';

    protected $description = 'Seed core CMS content on production only when the database is still empty';

    public function handle(): int
    {
        $this->warn('This command only adds missing bootstrap content. It does not overwrite existing CMS data.');

        $this->call('db:seed', [
            '--class' => 'BootstrapProductionContentSeeder',
            '--force' => true,
        ]);

        return self::SUCCESS;
    }
}
