<?php

namespace Database\Factories;

use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Page>
 */
class PageFactory extends Factory
{
    protected $model = Page::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => fake()->unique()->slug(2),
            'template' => 'default',
            'is_published' => true,
            'title' => [
                'it' => fake()->sentence(3),
                'en' => fake()->sentence(3),
            ],
            'slug' => [
                'it' => fake()->unique()->slug(3),
                'en' => fake()->unique()->slug(3),
            ],
            'body' => [
                'it' => fake()->paragraphs(2, true),
                'en' => fake()->paragraphs(2, true),
            ],
        ];
    }
}
