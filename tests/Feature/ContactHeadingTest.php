<?php

namespace Tests\Feature;

use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactHeadingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PageSeeder::class);
    }

    public function test_contact_heading_is_the_short_sentence(): void
    {
        $this->get('/it/contact')
            ->assertOk()
            ->assertSee('page-hero__title', false)
            ->assertSee('Contatti', false)
            ->assertSee('Per contattarci compila il modulo e scegli lo sportello più adatto.', false)
            ->assertSee('template-contact-desks', false)
            ->assertSee('Richiesta generica per ogni altra informazione.', false)
            ->assertDontSee('adatto: Sportello digitale', false)
            ->assertSee('name="last_name"', false)
            ->assertSee(__('site.pages.contact_last_name', [], 'it'), false);

        $this->get('/en/contact')
            ->assertOk()
            ->assertSee('Fill in the form and choose the most suitable desk.', false)
            ->assertSee('General request for anything else.', false);
    }
}
