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
        $author = User::factory()->create([
            'first_name' => 'Maria',
            'last_name' => 'Editor',
        ]);
        $article->update([
            'author_id' => $author->id,
            'show_author' => true,
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

    public function test_article_show_renders_author_job_title_in_brackets(): void
    {
        $article = Article::query()->where('slug->it', 'welcome-safe-house')->firstOrFail();
        $author = User::factory()->create([
            'first_name' => 'Matteo',
            'last_name' => 'Grossi',
            'job_title' => 'Presidente Safe House',
        ]);
        $article->update(['author_id' => $author->id, 'show_author' => true]);

        $this->get('/it/news/welcome-safe-house')
            ->assertOk()
            ->assertSee('Pubblicato da Matteo Grossi [Presidente Safe House]', false);
    }

    public function test_article_show_hides_author_when_show_author_is_false(): void
    {
        $article = Article::query()->where('slug->it', 'welcome-safe-house')->firstOrFail();
        $author = User::factory()->create([
            'first_name' => 'Maria',
            'last_name' => 'Editor',
        ]);
        $article->update([
            'author_id' => $author->id,
            'show_author' => false,
        ]);

        $this->get('/it/news/welcome-safe-house')
            ->assertOk()
            ->assertDontSee('Pubblicato da Maria Editor', false);
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
