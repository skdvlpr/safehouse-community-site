<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Throwable;

class CmsHealthCommand extends Command
{
    protected $signature = 'cms:health';

    protected $description = 'Probe Filament CMS boot (use on production when /cms-safehouse returns 500)';

    public function handle(): int
    {
        $this->line('PHP '.PHP_VERSION.' ('.PHP_SAPI.') as '.get_current_user());

        foreach (['mbstring', 'intl', 'curl', 'dom', 'xml', 'tokenizer'] as $extension) {
            $this->line(sprintf('  ext-%s: %s', $extension, extension_loaded($extension) ? 'yes' : 'MISSING'));
        }

        foreach (['storage/framework/views', 'storage/logs', 'bootstrap/cache'] as $directory) {
            $path = base_path($directory);
            $this->line(sprintf(
                '  %s: %s',
                $directory,
                is_writable($path) ? 'writable' : 'NOT WRITABLE'
            ));
        }

        $iconsCache = base_path('bootstrap/cache/blade-icons.php');
        $this->line(sprintf(
            '  filament icon cache: %s',
            is_file($iconsCache) ? 'present' : 'MISSING — run php artisan filament:optimize'
        ));

        $route = Route::getRoutes()->getByName('filament.cms-safehouse.auth.login');

        if ($route === null) {
            $this->error('Filament login route not registered.');

            return self::FAILURE;
        }

        $host = parse_url((string) config('app.url'), PHP_URL_HOST) ?: 'safehouse.community';

        try {
            $request = Request::create(
                '/cms-safehouse/login',
                'GET',
                [],
                [],
                [],
                [
                    'HTTPS' => 'on',
                    'HTTP_HOST' => $host,
                    'SERVER_NAME' => $host,
                    'SERVER_PORT' => 443,
                ],
            );

            $response = app()->handle($request);
            $status = $response->getStatusCode();
            $this->info("GET /cms-safehouse/login (https://{$host}) → HTTP {$status}");

            if ($status !== 200) {
                $this->printLastCmsError();

                return self::FAILURE;
            }

            $this->line('If HTTP still returns 500 in the browser, run: sudo -u www-data php artisan cms:health');

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());
            $this->line($exception->getFile().':'.$exception->getLine());
            $this->printLastCmsError();

            return self::FAILURE;
        }
    }

    private function printLastCmsError(): void
    {
        $path = storage_path('logs/cms-last-error.txt');

        if (! is_file($path)) {
            $this->warn('No cms-last-error.txt yet. Open /cms-safehouse/login in the browser once, then re-run this command.');

            return;
        }

        $this->warn('Last CMS exception (storage/logs/cms-last-error.txt):');
        $this->line((string) file_get_contents($path));
    }
}
