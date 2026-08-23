<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Services\SiteAppearanceSettings;
use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SiteAppearanceBackgroundTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (! is_file(public_path('images/bg-photo.jpg'))) {
            if (! is_dir(public_path('images'))) {
                mkdir(public_path('images'), 0775, true);
            }
            file_put_contents(public_path('images/bg-photo.jpg'), 'fake-stock');
        }
    }

    public function test_home_uses_stock_photo_by_default(): void
    {
        $html = $this->get('/it')->assertOk()->getContent();

        $this->assertStringContainsString('--safehouse-bg-image: url(', $html);
        $this->assertStringContainsString('bg-photo.jpg', $html);
    }

    public function test_home_uses_aurora_when_restored(): void
    {
        app(SiteAppearanceSettings::class)->clearBackground();

        $html = $this->get('/it')->assertOk()->getContent();

        $this->assertStringNotContainsString('bg-photo.jpg', $html);
        $this->assertStringNotContainsString('site-appearance/', $html);
    }

    public function test_home_injects_custom_background_css_when_uploaded(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('site-appearance/custom.jpg', 'fake');

        app(SiteAppearanceSettings::class)->saveFromFormState([
            'appearance' => ['background' => 'site-appearance/custom.jpg'],
        ]);

        $html = $this->get('/it')->assertOk()->getContent();

        $this->assertStringContainsString('--safehouse-bg-image: url(', $html);
        $this->assertStringContainsString('site-appearance/custom.jpg', $html);
    }

    public function test_cms_page_can_override_background(): void
    {
        $this->seed(PageSeeder::class);
        Storage::fake('public');
        Storage::disk('public')->put('site-appearance/page-only.gif', 'gif');

        $page = Page::query()->where('key', 'services')->firstOrFail();
        $page->meta = array_merge($page->meta ?? [], [
            'background' => [
                'enabled' => true,
                'path' => 'site-appearance/page-only.gif',
            ],
        ]);
        $page->save();

        $html = $this->get('/it/services')->assertOk()->getContent();

        $this->assertStringContainsString('site-appearance/page-only.gif', $html);
    }
}
