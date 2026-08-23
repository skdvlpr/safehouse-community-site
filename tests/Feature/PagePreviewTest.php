<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Services\PageService;
use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PagePreviewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PageSeeder::class);
    }

    public function test_signed_preview_renders_unpublished_page(): void
    {
        $page = Page::query()->where('key', 'about')->firstOrFail();
        $page->update(['is_published' => false]);

        $url = app(PageService::class)->previewUrl($page, 'it');

        $this->assertNotNull($url);

        $this->get($url)
            ->assertOk()
            ->assertSee(__('site.pages.preview_banner', [], 'it'), false)
            ->assertSee('data-page-template="about"', false)
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    }

    public function test_preview_requires_valid_signature(): void
    {
        $page = Page::query()->where('key', 'about')->firstOrFail();

        $this->get('/it/_preview/pages/'.$page->id)
            ->assertForbidden();
    }

    public function test_preview_url_requires_slug_for_locale(): void
    {
        $page = Page::factory()->create([
            'slug' => ['it' => 'solo-it'],
            'title' => ['it' => 'Solo IT'],
            'body' => ['it' => '<p>Test</p>'],
        ]);

        $this->assertNotNull(app(PageService::class)->previewUrl($page, 'it'));
        $this->assertNull(app(PageService::class)->previewUrl($page, 'en'));
    }

    public function test_preview_shows_locale_body_without_fallback(): void
    {
        $page = Page::factory()->create([
            'slug' => ['it' => 'corpo-it', 'en' => 'body-en'],
            'title' => ['it' => 'Titolo IT', 'en' => 'Title EN'],
            'body' => ['it' => '<p>Testo italiano unico</p>', 'en' => '<p>English only body</p>'],
            'is_published' => false,
            'template' => 'default',
        ]);

        $url = app(PageService::class)->previewUrl($page, 'it');
        $this->assertNotNull($url);

        $this->get($url)
            ->assertOk()
            ->assertSee('Testo italiano unico', false)
            ->assertDontSee('English only body', false);
    }
}
