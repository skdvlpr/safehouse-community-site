<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\ArticleCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Article>
 */
class ArticleFactory extends Factory
{
    protected $model = Article::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'article_category_id' => ArticleCategory::factory(),
            'title' => [
                'it' => fake()->sentence(4),
                'en' => fake()->sentence(4),
            ],
            'slug' => [
                'it' => fake()->unique()->slug(4),
                'en' => fake()->unique()->slug(4),
            ],
            'excerpt' => [
                'it' => fake()->paragraph(),
                'en' => fake()->paragraph(),
            ],
            'body' => [
                'it' => fake()->paragraphs(3, true),
                'en' => fake()->paragraphs(3, true),
            ],
            'is_published' => false,
            'published_at' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => true,
            'published_at' => now(),
        ]);
    }
}
