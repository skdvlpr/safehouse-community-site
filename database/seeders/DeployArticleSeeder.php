<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\ArticleCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DeployArticleSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/deploy-articles.php');

        if (! is_file($path)) {
            $this->call(ArticleSeeder::class);

            return;
        }

        /** @var array{categories?: list<array<string, mixed>>, articles?: list<array<string, mixed>>} $data */
        $data = require $path;

        $categoryIds = [];

        foreach ($data['categories'] ?? [] as $categoryData) {
            $slugIt = (string) ($categoryData['slug_it'] ?? $categoryData['slug']['it'] ?? '');

            if ($slugIt === '') {
                continue;
            }

            $category = ArticleCategory::query()->updateOrCreate(
                ['slug->it' => $slugIt],
                [
                    'name' => $categoryData['name'] ?? [],
                    'slug' => $categoryData['slug'] ?? [],
                    'description' => $categoryData['description'] ?? [],
                ],
            );

            $categoryIds[$slugIt] = $category->id;
        }

        foreach ($data['articles'] ?? [] as $articleData) {
            $slugIt = (string) ($articleData['slug']['it'] ?? '');

            if ($slugIt === '') {
                continue;
            }

            $categorySlugIt = (string) ($articleData['category_slug_it'] ?? '');
            $categoryId = $categoryIds[$categorySlugIt] ?? null;

            Article::query()->updateOrCreate(
                ['slug->it' => $slugIt],
                [
                    'article_category_id' => $categoryId,
                    'title' => $articleData['title'] ?? [],
                    'slug' => $articleData['slug'] ?? [],
                    'excerpt' => $articleData['excerpt'] ?? [],
                    'body' => $articleData['body'] ?? [],
                    'is_published' => (bool) ($articleData['is_published'] ?? true),
                    'published_at' => isset($articleData['published_at'])
                        ? Carbon::parse((string) $articleData['published_at'])
                        : now(),
                ],
            );
        }
    }
}
