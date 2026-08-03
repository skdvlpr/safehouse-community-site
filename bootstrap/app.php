<?php

use App\Http\Middleware\DisableHttpCacheWhenEnabled;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(SecurityHeaders::class);
        $middleware->append(DisableHttpCacheWhenEnabled::class);

        $middleware->trustProxies(at: '*');

        $middleware->validateCsrfTokens(except: [
            'webhooks/stripe',
        ]);

        $middleware->alias([
            'setlocale' => SetLocale::class,
            'crm.sync' => \App\Http\Middleware\VerifyCrmSyncToken::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        $exceptions->report(function (\Throwable $exception): void {
            $request = request();

            if ($request === null || ! $request->is('cms-safehouse*')) {
                return;
            }

            $message = sprintf(
                "[%s] %s\n%s:%d\n%s\n",
                now()->toDateTimeString(),
                $exception->getMessage(),
                $exception->getFile(),
                $exception->getLine(),
                $exception->getTraceAsString()
            );

            @file_put_contents(storage_path('logs/cms-last-error.txt'), $message, LOCK_EX);
        });
    })->create();
