<?php

namespace Tests\Feature;

use Tests\TestCase;

class VolunteerHeadingTest extends TestCase
{
    public function test_volunteer_heading_matches_chi_siamo_pattern(): void
    {
        $this->get('/it/volunteers')
            ->assertOk()
            ->assertSee('page-hero__title', false)
            ->assertSee('Volontariato', false)
            ->assertSee('page-hero__tagline', false)
            ->assertSee('Metti a disposizione tempo e competenze per l\'accoglienza e i servizi sul territorio.')
            ->assertDontSee('template-eyebrow', false)
            ->assertDontSee('page-title__lead', false);

        $this->get('/en/volunteers')
            ->assertOk()
            ->assertSee('page-hero__tagline', false)
            ->assertSee('Share your time and skills for welcome and services in the local area.', false);
    }
}
