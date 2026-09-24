<?php

namespace Tests\Feature;

use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServicesHeadingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PageSeeder::class);
    }

    public function test_services_heading_matches_chi_siamo_pattern(): void
    {
        $this->get('/it/services')
            ->assertOk()
            ->assertSee('page-hero__title', false)
            ->assertSee('Servizi', false)
            ->assertSee('Interveniamo con servizi integrati per rispondere alle emergenze e accompagnare percorsi di autonomia e dignità.', false)
            ->assertSee('landing-reveal', false)
            ->assertSee('data-reveal-from="left"', false)
            ->assertSee('data-reveal-from="right"', false)
            ->assertDontSee('template-eyebrow', false);

        $this->get('/en/services')
            ->assertOk()
            ->assertSee('page-hero__tagline', false)
            ->assertSee('We step in with integrated services to meet emergencies and support paths of autonomy and dignity.', false)
            ->assertDontSee('template-eyebrow', false);
    }
}
