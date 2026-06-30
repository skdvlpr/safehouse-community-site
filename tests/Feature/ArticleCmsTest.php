<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use App\Services\ArticleService;
use Database\Seeders\ArticleSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
            ->assertSee('Benvenuti in Safe House Community', false);
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
