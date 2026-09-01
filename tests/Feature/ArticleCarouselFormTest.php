<?php

namespace Tests\Feature;

use App\Filament\Resources\ArticleResource\Pages\EditArticle;
use App\Models\Article;
use App\Models\User;
use Database\Seeders\ArticleSeeder;
use Database\Seeders\RoleSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ArticleCarouselFormTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ArticleSeeder::class);
        $this->seed(RoleSeeder::class);
    }

    public function test_article_show_renders_compact_carousel_and_back_link(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('article-carousels/show.jpg', 'fake');

        $article = Article::query()->where('slug->it', 'welcome-safe-house')->firstOrFail();
        $author = User::factory()->create(['name' => 'Maria Editor']);
        $article->update([
            'author_id' => $author->id,
            'meta' => [
                'carousel' => [
                    ['path' => 'article-carousels/show.jpg', 'alt' => ['it' => 'Benvenuti']],
                ],
            ],
        ]);

        $this->get('/it/news/welcome-safe-house')
            ->assertOk()
            ->assertSee('page-carousel--compact', false)
            ->assertSee('page-carousel__image--contain', false)
            ->assertSee('data-carousel-lightbox', false)
            ->assertSee('article-show__back', false)
            ->assertSee('article-show__header', false)
            ->assertSee('Tutte le notizie', false)
            ->assertSee('Pubblicato da Maria Editor', false)
            ->assertSee('article-carousels/show.jpg', false);
    }

    public function test_article_form_allows_empty_carousel_row_on_save(): void
    {
        $article = Article::query()->where('slug->it', 'welcome-safe-house')->firstOrFail();
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        Filament::setCurrentPanel(Filament::getPanel('cms-safehouse'));

        Livewire::actingAs($admin)
            ->test(EditArticle::class, ['record' => $article->getKey()])
            ->fillForm([
                'meta' => [
                    'carousel' => [
                        ['path' => null, 'alt' => ['it' => '', 'ru' => '', 'en' => '']],
                    ],
                ],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $article->refresh();
        $this->assertSame([], $article->meta['carousel'] ?? []);
    }
}
