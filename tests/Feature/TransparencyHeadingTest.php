<?php

namespace Tests\Feature;

use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransparencyHeadingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PageSeeder::class);
    }

    public function test_transparency_heading_is_outside_one_glass_block(): void
    {
        $this->get('/it/transparency')
            ->assertOk()
            ->assertSee('page-hero__title', false)
            ->assertSee('Trasparenza', false)
            ->assertSee('Informativa Safe House ETS (GDPR / ePrivacy).', false)
            ->assertSee('template-legal-doc', false)
            ->assertDontSee('template-legal-hero', false)
            ->assertDontSee('template-eyebrow', false);

        $this->get('/en/transparency')
            ->assertOk()
            ->assertSee('page-hero__tagline', false)
            ->assertSee('Notice for Safe House ETS (GDPR / ePrivacy).', false);

        $this->get('/it/cookie-policy')
            ->assertOk()
            ->assertSee('template-legal-hero', false)
            ->assertSee('template-legal-doc', false);
    }
}
