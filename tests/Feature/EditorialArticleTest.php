<?php

namespace Tests\Feature;

use App\Enums\ArticleSection;
use App\Filament\Resources\EditorialArticleCategoryResource\Pages\CreateEditorialArticleCategory;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EditorialArticleTest extends TestCase
{
    use RefreshDatabase;

    public function test_articoli_page_lists_published_editorial_articles(): void
    {
        $category = ArticleCategory::factory()->create([
            'section' => ArticleSection::Editorial,
            'name' => ['it' => 'Rassegna'],
            'slug' => ['it' => 'rassegna'],
        ]);

        Article::factory()->editorial()->published()->create([
            'article_category_id' => $category->id,
            'title' => ['it' => 'Storia editoriale di prova'],
            'slug' => ['it' => 'storia-editoriale-prova'],
            'body' => ['it' => '<p>Contenuto.</p>'],
        ]);

        Article::factory()->published()->create([
            'title' => ['it' => 'Notizia separata'],
            'slug' => ['it' => 'notizia-separata'],
            'body' => ['it' => '<p>News.</p>'],
        ]);

        $this->get('/it/articles')
            ->assertOk()
            ->assertSee('Storia editoriale di prova', false)
            ->assertDontSee('Notizia separata', false)
            ->assertSee('Rassegna', false);
    }

    public function test_articoli_filters_by_category(): void
    {
        $included = ArticleCategory::factory()->create([
            'section' => ArticleSection::Editorial,
            'slug' => ['it' => 'inclusa'],
        ]);
        $excluded = ArticleCategory::factory()->create([
            'section' => ArticleSection::Editorial,
            'slug' => ['it' => 'esclusa'],
        ]);

        Article::factory()->editorial()->published()->create([
            'article_category_id' => $included->id,
            'title' => ['it' => 'Articolo incluso'],
            'slug' => ['it' => 'articolo-incluso'],
            'body' => ['it' => '<p>Ok</p>'],
        ]);

        Article::factory()->editorial()->published()->create([
            'article_category_id' => $excluded->id,
            'title' => ['it' => 'Articolo escluso'],
            'slug' => ['it' => 'articolo-escluso'],
            'body' => ['it' => '<p>No</p>'],
        ]);

        $this->get('/it/articles?categories[]=inclusa')
            ->assertOk()
            ->assertSee('Articolo incluso', false)
            ->assertDontSee('Articolo escluso', false);
    }

    public function test_journalist_can_access_editorial_cms_but_not_news_cms(): void
    {
        $this->seed(RoleSeeder::class);

        $journalist = User::factory()->create();
        $journalist->assignRole('journalist');

        $this->actingAs($journalist)
            ->get('/cms-safehouse/editorial-articles')
            ->assertOk();

        $this->actingAs($journalist)
            ->get('/cms-safehouse/articles')
            ->assertForbidden();
    }

    public function test_journalist_can_create_and_list_shared_editorial_categories(): void
    {
        $this->seed(RoleSeeder::class);

        $shared = ArticleCategory::factory()->create([
            'section' => ArticleSection::Editorial,
            'name' => ['it' => 'Categoria condivisa'],
            'slug' => ['it' => 'categoria-condivisa'],
        ]);

        $journalist = User::factory()->create();
        $journalist->assignRole('journalist');

        $this->actingAs($journalist)
            ->get('/cms-safehouse/editorial-article-categories')
            ->assertOk()
            ->assertSee('Categoria condivisa', false);

        $this->actingAs($journalist)
            ->get('/cms-safehouse/editorial-article-categories/create')
            ->assertOk();

        Filament::setCurrentPanel(Filament::getPanel('cms-safehouse'));

        Livewire::actingAs($journalist)
            ->test(CreateEditorialArticleCategory::class)
            ->fillForm([
                'name' => ['it' => 'Nuova del giornalista', 'ru' => '', 'en' => ''],
                'description' => ['it' => '', 'ru' => '', 'en' => ''],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertTrue(
            ArticleCategory::query()
                ->where('section', ArticleSection::Editorial)
                ->get()
                ->contains(fn (ArticleCategory $c): bool => ($c->getTranslation('slug', 'it') ?? '') === 'nuova-del-giornalista')
        );

        $this->actingAs($journalist)
            ->get('/cms-safehouse/editorial-article-categories/'.$shared->id.'/edit')
            ->assertForbidden();

        $this->actingAs($journalist)
            ->get('/cms-safehouse/article-categories')
            ->assertForbidden();
    }

    public function test_journalist_can_assign_category_created_by_someone_else(): void
    {
        $this->seed(RoleSeeder::class);

        $category = ArticleCategory::factory()->create([
            'section' => ArticleSection::Editorial,
            'name' => ['it' => 'Di un altro'],
            'slug' => ['it' => 'di-un-altro'],
        ]);

        $journalist = User::factory()->create();
        $journalist->assignRole('journalist');

        $article = Article::factory()->editorial()->create([
            'author_id' => $journalist->id,
            'article_category_id' => null,
            'title' => ['it' => 'Mio articolo'],
            'slug' => ['it' => 'mio-articolo'],
            'body' => ['it' => '<p>Test</p>'],
        ]);

        $article->update(['article_category_id' => $category->id]);

        $this->assertSame($category->id, $article->fresh()->article_category_id);

        $this->actingAs($journalist)
            ->get('/cms-safehouse/editorial-articles/'.$article->id.'/edit')
            ->assertOk()
            ->assertSee('Di un altro', false);
    }

    public function test_journalist_cannot_edit_another_authors_editorial_article(): void
    {
        $this->seed(RoleSeeder::class);

        $owner = User::factory()->create();
        $owner->assignRole('journalist');

        $other = User::factory()->create();
        $other->assignRole('journalist');

        $article = Article::factory()->editorial()->create([
            'author_id' => $owner->id,
            'title' => ['it' => 'Di qualcun altro'],
            'slug' => ['it' => 'di-qualcun-altro'],
            'body' => ['it' => '<p>Test</p>'],
        ]);

        $this->actingAs($other)
            ->get('/cms-safehouse/editorial-articles/'.$article->id.'/edit')
            ->assertNotFound();
    }

    public function test_admin_can_manage_users(): void
    {
        $this->seed(RoleSeeder::class);

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->get('/cms-safehouse/users')
            ->assertOk();
    }
}
