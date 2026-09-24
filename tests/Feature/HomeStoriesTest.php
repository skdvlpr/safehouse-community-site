<?php

namespace Tests\Feature;

use App\Enums\ArticleSection;
use App\Models\Article;
use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HomeStoriesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PageSeeder::class);
    }

    public function test_home_shows_four_newest_stories_and_membership_first(): void
    {
        Article::factory()->create([
            'section' => ArticleSection::News,
            'is_published' => true,
            'published_at' => now()->subDays(3),
            'title' => ['it' => 'Notizia vecchia', 'en' => 'Old news'],
            'slug' => ['it' => 'notizia-vecchia', 'en' => 'old-news'],
        ]);
        Article::factory()->create([
            'section' => ArticleSection::Editorial,
            'is_published' => true,
            'published_at' => now()->subDay(),
            'title' => ['it' => 'Articolo nuovo', 'en' => ''],
            'slug' => ['it' => 'articolo-nuovo', 'en' => ''],
        ]);
        Article::factory()->create([
            'section' => ArticleSection::News,
            'is_published' => true,
            'published_at' => now(),
            'title' => ['it' => 'Notizia nuova', 'en' => 'New news'],
            'slug' => ['it' => 'notizia-nuova', 'en' => 'new-news'],
        ]);

        $italian = $this->get('/it')->assertOk();
        $italian->assertSee('Notizia nuova', false);
        $italian->assertSee('Articolo nuovo', false);
        $italian->assertSee('Notizia vecchia', false);
        $italian->assertSee(__('site.home.cta_member', [], 'it'), false);
        $italian->assertSee(route('articles.index', ['locale' => 'it']), false);
        $italian->assertSee(route('editorial-articles.index', ['locale' => 'it']), false);

        $this->get('/en')
            ->assertOk()
            ->assertDontSee('Articolo nuovo', false)
            ->assertSee('New news', false);
    }

    public function test_home_hides_the_story_strip_when_nothing_is_published(): void
    {
        $this->get('/it')
            ->assertOk()
            ->assertDontSee('story-window', false);
    }

    public function test_story_card_keeps_a_thumb_slot_when_a_photo_exists(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('article-carousels/cover.jpg', 'img');

        Article::factory()->create([
            'section' => ArticleSection::News,
            'is_published' => true,
            'published_at' => now(),
            'title' => ['it' => 'Con foto', 'en' => 'With photo'],
            'slug' => ['it' => 'con-foto', 'en' => 'with-photo'],
            'meta' => [
                'carousel' => [
                    ['path' => 'article-carousels/cover.jpg', 'alt' => ['it' => 'Copertina', 'en' => 'Cover']],
                ],
            ],
        ]);

        $this->get('/it')
            ->assertOk()
            ->assertSee('story-card__thumb', false)
            ->assertSee('Con foto', false);
    }
}
