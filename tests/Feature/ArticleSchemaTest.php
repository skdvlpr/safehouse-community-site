<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\ArticleCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ArticleSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_article_schema_migrations_run(): void
    {
        $this->assertTrue(Schema::hasTable('article_categories'));
        $this->assertTrue(Schema::hasTable('articles'));
    }

    public function test_article_category_factory_saves_translations(): void
    {
        $category = ArticleCategory::factory()->create([
            'name' => [
                'it' => 'Notizie',
                'en' => 'News',
            ],
            'slug' => [
                'it' => 'notizie',
                'en' => 'news',
            ],
        ]);

        $category->refresh();

        $this->assertSame('Notizie', $category->getTranslation('name', 'it'));
        $this->assertSame('News', $category->getTranslation('name', 'en'));
    }

    public function test_article_factory_saves_translations_and_category_relation(): void
    {
        $category = ArticleCategory::factory()->create();

        $article = Article::factory()->create([
            'article_category_id' => $category->id,
            'title' => [
                'it' => 'Primo articolo',
                'en' => 'First article',
            ],
            'slug' => [
                'it' => 'primo-articolo',
                'en' => 'first-article',
            ],
            'body' => [
                'it' => 'Testo IT',
                'en' => 'Text EN',
            ],
        ]);

        $article->refresh();

        $this->assertSame('Primo articolo', $article->getTranslation('title', 'it'));
        $this->assertSame('First article', $article->getTranslation('title', 'en'));
        $this->assertTrue($article->category->is($category));
    }
}
