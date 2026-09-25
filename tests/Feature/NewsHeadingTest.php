<?php

namespace Tests\Feature;

use App\Enums\ArticleSection;
use App\Models\Article;
use App\Models\ArticleCategory;
use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsHeadingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PageSeeder::class);

        $newsCategory = ArticleCategory::factory()->create([
            'section' => ArticleSection::News,
            'name' => ['it' => 'Comunità', 'en' => 'Community'],
            'slug' => ['it' => 'comunita', 'en' => 'community'],
        ]);
        $editorialCategory = ArticleCategory::factory()->create([
            'section' => ArticleSection::Editorial,
            'name' => ['it' => 'Rassegna', 'en' => 'Review'],
            'slug' => ['it' => 'rassegna', 'en' => 'review'],
        ]);

        Article::factory()->published()->create([
            'article_category_id' => $newsCategory->id,
            'section' => ArticleSection::News,
        ]);
        Article::factory()->editorial()->published()->create([
            'article_category_id' => $editorialCategory->id,
        ]);
    }

    public function test_news_heading_and_narrow_filters(): void
    {
        $this->get('/it/news')
            ->assertOk()
            ->assertSee('page-hero__title', false)
            ->assertSee('page-hero__headline--center', false)
            ->assertSee('Notizie', false)
            ->assertSee('Aggiornamenti dall\'associazione e dal territorio.')
            ->assertDontSee('Tutte le notizie', false)
            ->assertDontSee('news-toolbar--narrow', false)
            ->assertSee('news-toolbar__group--dates', false)
            ->assertSee('news-date-filters', false)
            ->assertSee('news-cat-menu__summary', false)
            ->assertDontSee('template-eyebrow', false);

        $this->get('/en/news')
            ->assertOk()
            ->assertSee('News', false)
            ->assertSee('Updates from the association and the community.', false)
            ->assertDontSee('All news', false);

        $this->get('/it/articles')
            ->assertOk()
            ->assertSee('page-hero__title', false)
            ->assertSee('page-hero__headline--center', false)
            ->assertSee('Articoli', false)
            ->assertSee('Approfondimenti, testimonianze e contenuti editoriali.', false)
            ->assertSee('news-toolbar__group--dates', false)
            ->assertSee('news-date-filters', false)
            ->assertSee('news-cat-menu__summary', false)
            ->assertDontSee('Tutti gli articoli', false);
    }

    public function test_home_story_buttons_keep_all_labels(): void
    {
        Article::factory()->published()->create();
        Article::factory()->published()->editorial()->create();

        $this->get('/it')
            ->assertOk()
            ->assertSee('Tutte le notizie', false)
            ->assertSee('Tutti gli articoli', false);

        $this->get('/en')
            ->assertOk()
            ->assertSee('All news', false)
            ->assertSee('All articles', false);
    }
}
