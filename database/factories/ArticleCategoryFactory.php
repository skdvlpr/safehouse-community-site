<?php

namespace Database\Factories;

use App\Models\ArticleCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ArticleCategory>
 */
class ArticleCategoryFactory extends Factory
{
    protected $model = ArticleCategory::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => [
                'it' => fake()->words(2, true),
                'en' => fake()->words(2, true),
            ],
            'slug' => [
                'it' => fake()->unique()->slug(2),
                'en' => fake()->unique()->slug(2),
            ],
            'description' => [
                'it' => fake()->sentence(),
                'en' => fake()->sentence(),
            ],
        ];
    }
}
