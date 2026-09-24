<?php

namespace Tests\Feature;

use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FaqHeadingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PageSeeder::class);
    }

    public function test_faq_heading_matches_chi_siamo_pattern(): void
    {
        $this->get('/it/frequently-asked-questions')
            ->assertOk()
            ->assertSee('page-hero__title', false)
            ->assertSee('Domande frequenti', false)
            ->assertSee('Risposte a tutte le domande frequenti, raccolte qui.', false)
            ->assertDontSee('template-eyebrow', false);

        $this->get('/en/frequently-asked-questions')
            ->assertOk()
            ->assertSee('page-hero__tagline', false)
            ->assertSee('Answers to all the questions people ask most often, gathered here.', false)
            ->assertDontSee('template-eyebrow', false);
    }
}
