<?php

use App\Http\Middleware\DisableHttpCacheWhenEnabled;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\VerifyCrmSyncToken;
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

        $trustedProxies = env('TRUSTED_PROXIES');
        if ($trustedProxies === '*') {
            $middleware->trustProxies(at: '*');
        } elseif (is_string($trustedProxies) && $trustedProxies !== '') {
            $middleware->trustProxies(at: array_values(array_filter(array_map('trim', explode(',', $trustedProxies)))));
        } else {
            // DDEV docker + Caddy/loopback. Do not trust spoofed X-Forwarded-For from the public internet.
            // https://laravel.com/docs/13.x/requests#configuring-trusted-proxies
            $middleware->trustProxies(at: [
                '127.0.0.1',
                '::1',
                '10.0.0.0/8',
                '172.16.0.0/12',
                '192.168.0.0/16',
            ]);
        }

        $middleware->preventRequestForgery(except: [
            'api/webhooks/stripe',
        ]);

        $middleware->alias([
            'setlocale' => SetLocale::class,
            'crm.sync' => VerifyCrmSyncToken::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        $exceptions->report(function (Throwable $exception): void {
            $request = request();

            if ($request === null || ! $request->is('cms-safehouse*')) {
                return;
            }

            $rawMessage = $exception->getMessage();
            $rawMessage = preg_replace('/(sk_live_|sk_test_|rk_live_|rk_test_|pk_live_|pk_test_|whsec_)[A-Za-z0-9_]+/', '$1[redacted]', $rawMessage) ?? $rawMessage;
            $rawMessage = preg_replace('/Bearer\s+\S+/i', 'Bearer [redacted]', $rawMessage) ?? $rawMessage;
            $rawMessage = mb_substr($rawMessage, 0, 500);

            $message = sprintf(
                "[%s] %s\n%s\n%s:%d\n",
                now()->toDateTimeString(),
                $exception::class,
                $rawMessage,
                $exception->getFile(),
                $exception->getLine(),
            );

            @file_put_contents(storage_path('logs/cms-last-error.txt'), $message, LOCK_EX);
        });
    })->create();
