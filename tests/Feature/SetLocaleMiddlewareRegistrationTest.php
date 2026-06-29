<?php

namespace Tests\Feature;

use App\Http\Middleware\SetLocale;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SetLocaleMiddlewareRegistrationTest extends TestCase
{
    public function test_setlocale_middleware_alias_is_registered(): void
    {
        $aliases = app(Kernel::class)->getMiddlewareAliases();

        $this->assertArrayHasKey('setlocale', $aliases);
        $this->assertSame(SetLocale::class, $aliases['setlocale']);
    }

    public function test_setlocale_middleware_appears_on_routes_that_use_it(): void
    {
        Route::middleware('setlocale')->get('/_test-setlocale-registration', fn () => 'ok');

        $route = collect(app('router')->getRoutes())->first(
            fn ($route) => $route->uri() === '_test-setlocale-registration',
        );

        $this->assertNotNull($route);
        $this->assertContains('setlocale', $route->gatherMiddleware());
    }
}
