<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use App\Services\ArticleService;
use Database\Seeders\ArticleSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ArticleCmsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ArticleSeeder::class);
    }

    public function test_notizie_lists_seeded_article(): void
    {
        $this->get('/it/notizie')
            ->assertOk()
            ->assertSee('Benvenuti in Safe House Community', false)
            ->assertSee('news-toolbar', false)
            ->assertSee('news-feed', false)
            ->assertSee('Comunità', false);
    }

    public function test_notizie_filters_by_single_category(): void
    {
        $this->get('/it/notizie?categories[]=eventi')
            ->assertOk()
            ->assertSee('Open day volontari', false)
            ->assertDontSee('Benvenuti in Safe House Community', false);
    }

    public function test_notizie_filters_by_multiple_categories(): void
    {
        $this->get('/it/notizie?'.http_build_query(['categories' => ['comunita', 'eventi']]))
            ->assertOk()
            ->assertSee('Benvenuti in Safe House Community', false)
            ->assertSee('Open day volontari', false);
    }

    public function test_notizie_list_layout_renders_compact_rows(): void
    {
        $this->get('/it/notizie?layout=list')
            ->assertOk()
            ->assertSee('news-list', false)
            ->assertDontSee('news-feed__item', false);
    }

    public function test_notizie_feed_shows_article_carousel_cover(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('article-carousels/feed.jpg', 'fake');

        $article = Article::query()->where('slug->it', 'open-day-volontari')->firstOrFail();
        $article->update([
            'meta' => [
                'carousel' => [
                    ['path' => 'article-carousels/feed.jpg', 'alt' => ['it' => 'Open day']],
                ],
            ],
        ]);

        $this->get('/it/notizie')
            ->assertOk()
            ->assertSee('article-carousels/feed.jpg', false)
            ->assertSee('news-feed__cover', false);
    }

    public function test_article_show_renders_carousel(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('article-carousels/show.jpg', 'fake');

        $article = Article::query()->where('slug->it', 'benvenuti-safe-house')->firstOrFail();
        $article->update([
            'meta' => [
                'carousel' => [
                    ['path' => 'article-carousels/show.jpg', 'alt' => ['it' => 'Benvenuti']],
                ],
            ],
        ]);

        $this->get('/it/notizie/benvenuti-safe-house')
            ->assertOk()
            ->assertSee('data-page-carousel', false)
            ->assertSee('article-carousels/show.jpg', false);
    }

    public function test_notizie_date_filter_limits_results(): void
    {
        $today = now()->toDateString();

        $this->get('/it/notizie?from='.$today.'&to='.$today)
            ->assertOk()
            ->assertSee('Open day volontari', false)
            ->assertDontSee('Benvenuti in Safe House Community', false);
    }

    public function test_article_show_page_renders(): void
    {
        $this->get('/it/notizie/benvenuti-safe-house')
            ->assertOk()
            ->assertSee('Benvenuti in Safe House Community', false);
    }

    public function test_signed_article_preview_works_for_draft(): void
    {
        $article = Article::query()->firstOrFail();
        $article->update(['is_published' => false, 'published_at' => null]);

        $url = app(ArticleService::class)->previewUrl($article, 'it');

        $this->assertNotNull($url);

        $this->get($url)
            ->assertOk()
            ->assertSee(__('site.pages.preview_banner', [], 'it'), false)
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    }

    public function test_editor_can_open_articles_cms_route(): void
    {
        $this->seed(RoleSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('editor');

        $this->actingAs($user)
            ->get('/cms-safehouse/articles')
            ->assertOk();
    }
}
