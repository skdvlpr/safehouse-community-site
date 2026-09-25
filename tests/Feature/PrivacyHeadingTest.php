<?php

namespace Tests\Feature;

use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrivacyHeadingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PageSeeder::class);
    }

    public function test_privacy_heading_matches_transparency_layout(): void
    {
        $this->get('/it/privacy-policy')
            ->assertOk()
            ->assertSee('page-hero__title', false)
            ->assertSee('Privacy', false)
            ->assertSee('Informativa sul trattamento dei dati personali di Safe House ETS.', false)
            ->assertSee('template-legal-doc', false)
            ->assertDontSee('template-legal-hero', false)
            ->assertDontSee('template-eyebrow', false);

        $this->get('/en/privacy-policy')
            ->assertOk()
            ->assertSee('Personal data notice for Safe House ETS.', false);
    }
}
