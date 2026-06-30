<?php

namespace App\Providers;

use App\Services\EspoCrm\EspoCrmClient;
use App\Services\Payments\StripePaymentService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(EspoCrmClient::class, fn () => EspoCrmClient::fromConfig());
        $this->app->singleton(StripePaymentService::class, fn () => StripePaymentService::fromConfig());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();
        $this->configurePasswordDefaults();
    }

    protected function configurePasswordDefaults(): void
    {
        Password::defaults(fn () => Password::min(12)
            ->letters()
            ->mixedCase()
            ->numbers()
            ->symbols());
    }

    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', fn (Request $request) => Limit::perMinute(60)->by($request->ip()));

        RateLimiter::for('contact', fn (Request $request) => Limit::perHour(5)->by($request->ip()));

        RateLimiter::for('volunteers', fn (Request $request) => Limit::perHour(3)->by($request->ip()));

        RateLimiter::for('donations', fn (Request $request) => Limit::perHour(30)->by($request->ip()));
    }
}
