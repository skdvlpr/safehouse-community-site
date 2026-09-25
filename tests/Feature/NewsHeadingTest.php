<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsHeadingTest extends TestCase
{
    use RefreshDatabase;

    public function test_news_heading_and_narrow_filters(): void
    {
        $this->get('/it/news')
            ->assertOk()
            ->assertSee('page-hero__title', false)
            ->assertSee('Notizie', false)
            ->assertSee('Aggiornamenti dall\'associazione e dal territorio.')
            ->assertDontSee('news-toolbar--narrow', false)
            ->assertDontSee('template-eyebrow', false);

        $this->get('/en/news')
            ->assertOk()
            ->assertSee('Updates from the association and the community.', false);

        $this->get('/it/articles')
            ->assertOk()
            ->assertSee('page-hero__title', false)
            ->assertSee('Articoli', false)
            ->assertSee('Approfondimenti, testimonianze e contenuti editoriali.', false);
    }
}
