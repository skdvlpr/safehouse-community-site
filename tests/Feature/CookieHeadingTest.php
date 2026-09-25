<?php

namespace Tests\Feature;

use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CookieHeadingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PageSeeder::class);
    }

    public function test_cookie_heading_matches_transparency_layout(): void
    {
        $this->get('/it/cookie-policy')
            ->assertOk()
            ->assertSee('page-hero__title', false)
            ->assertSee('Cookie', false)
            ->assertSee('Informativa sui cookie e sul consenso di Safe House ETS.', false)
            ->assertSee('data-cookie-reopen', false)
            ->assertSee('template-legal-doc', false)
            ->assertDontSee('template-legal-hero', false)
            ->assertDontSee('template-eyebrow', false);

        $this->get('/en/cookie-policy')
            ->assertOk()
            ->assertSee('Cookie and consent notice for Safe House ETS.', false);
    }
}
