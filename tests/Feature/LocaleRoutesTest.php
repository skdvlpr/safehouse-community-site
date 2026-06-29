<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\App;
use Tests\TestCase;

class LocaleRoutesTest extends TestCase
{
    public function test_locale_prefixed_home_route_is_registered(): void
    {
        $route = app('router')->getRoutes()->getByName('home');

        $this->assertNotNull($route);
        $this->assertSame('{locale}', $route->uri());
        $this->assertContains('setlocale', $route->gatherMiddleware());
    }

    public function test_locale_home_route_accepts_configured_locales(): void
    {
        foreach (config('locales.available') as $locale) {
            $response = $this->get("/{$locale}");

            $response->assertOk();
        }
    }

    public function test_locale_home_sets_application_locale(): void
    {
        $this->get('/it');

        $this->assertSame('it', App::getLocale());
    }

    public function test_unknown_locale_returns_not_found(): void
    {
        $response = $this->get('/xx');

        $response->assertNotFound();
    }
}
