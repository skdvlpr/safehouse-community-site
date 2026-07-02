<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Throwable;

class CmsHealthCommand extends Command
{
    protected $signature = 'cms:health';

    protected $description = 'Probe Filament CMS boot (use on production when /cms-safehouse returns 500)';

    public function handle(): int
    {
        $this->line('PHP '.PHP_VERSION.' ('.PHP_SAPI.')');

        foreach (['mbstring', 'intl', 'curl', 'dom', 'xml', 'tokenizer'] as $extension) {
            $this->line(sprintf('  ext-%s: %s', $extension, extension_loaded($extension) ? 'yes' : 'MISSING'));
        }

        $route = Route::getRoutes()->getByName('filament.cms-safehouse.auth.login');

        if ($route === null) {
            $this->error('Filament login route not registered.');

            return self::FAILURE;
        }

        try {
            $response = app()->handle(\Illuminate\Http\Request::create('/cms-safehouse/login', 'GET'));
            $status = $response->getStatusCode();
            $this->info("GET /cms-safehouse/login → HTTP {$status}");

            return $status === 200 ? self::SUCCESS : self::FAILURE;
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());
            $this->line($exception->getFile().':'.$exception->getLine());

            return self::FAILURE;
        }
    }
}
