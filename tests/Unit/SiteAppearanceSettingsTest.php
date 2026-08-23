<?php

namespace Tests\Unit;

use App\Models\Page;
use App\Services\SiteAppearanceSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SiteAppearanceSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        // Stock file for ensureStockInLibrary / stock URL checks via public_path
        if (! is_dir(public_path('images'))) {
            mkdir(public_path('images'), 0775, true);
        }
        if (! is_file(public_path('images/bg-photo.jpg'))) {
            file_put_contents(public_path('images/bg-photo.jpg'), 'fake-stock');
        }
        app(SiteAppearanceSettings::class)->forgetCache();
    }

    public function test_defaults_to_stock_photo_background(): void
    {
        $appearance = app(SiteAppearanceSettings::class);

        $this->assertSame(SiteAppearanceSettings::MODE_STOCK, $appearance->mode());
        $this->assertNull($appearance->backgroundPath());
        $this->assertStringContainsString('bg-photo.jpg', (string) $appearance->activeBackgroundUrl());
    }

    public function test_form_background_path_shows_stock_library_file(): void
    {
        $appearance = app(SiteAppearanceSettings::class);
        $path = $appearance->formBackgroundPath();

        $this->assertSame($appearance->stockLibraryPath(), $path);
        $this->assertTrue(Storage::disk('public')->exists($path));
    }

    public function test_saves_and_resolves_uploaded_background(): void
    {
        Storage::disk('public')->put('site-appearance/hero.gif', 'fake');

        $appearance = app(SiteAppearanceSettings::class);
        $appearance->saveFromFormState([
            'appearance' => ['background' => 'site-appearance/hero.gif'],
        ]);

        $this->assertSame(SiteAppearanceSettings::MODE_CUSTOM, $appearance->mode());
        $this->assertSame('site-appearance/hero.gif', $appearance->backgroundPath());
        $this->assertStringContainsString('site-appearance/hero.gif', (string) $appearance->activeBackgroundUrl());
    }

    public function test_saving_stock_library_path_keeps_stock_mode(): void
    {
        $appearance = app(SiteAppearanceSettings::class);
        $stock = $appearance->formBackgroundPath();

        $appearance->saveFromFormState([
            'appearance' => ['background' => $stock],
        ]);

        $this->assertSame(SiteAppearanceSettings::MODE_STOCK, $appearance->mode());
        $this->assertTrue(Storage::disk('public')->exists($appearance->stockLibraryPath()));
    }

    public function test_restore_aurora_disables_photo_background(): void
    {
        Storage::disk('public')->put('site-appearance/hero.jpg', 'fake');

        $appearance = app(SiteAppearanceSettings::class);
        $appearance->saveFromFormState([
            'appearance' => ['background' => 'site-appearance/hero.jpg'],
        ]);
        $appearance->clearBackground();

        $this->assertSame(SiteAppearanceSettings::MODE_AURORA, $appearance->mode());
        $this->assertNull($appearance->activeBackgroundUrl());
        $this->assertFalse(Storage::disk('public')->exists('site-appearance/hero.jpg'));
        $this->assertTrue(Storage::disk('public')->exists($appearance->stockLibraryPath()));
    }

    public function test_page_override_wins_over_global(): void
    {
        Storage::disk('public')->put('site-appearance/page.svg', '<svg></svg>');

        $page = Page::query()->create([
            'key' => 'about',
            'template' => 'about',
            'is_published' => true,
            'title' => ['it' => 'About'],
            'slug' => ['it' => 'about-us'],
            'body' => ['it' => '<p>x</p>'],
            'meta' => [
                'background' => [
                    'enabled' => true,
                    'path' => 'site-appearance/page.svg',
                ],
            ],
        ]);

        $url = app(SiteAppearanceSettings::class)->backgroundUrlForPage($page);

        $this->assertStringContainsString('site-appearance/page.svg', (string) $url);
    }

    public function test_normalize_page_background_meta_from_upload(): void
    {
        Storage::disk('public')->put('site-appearance/upload.gif', 'gif');

        $meta = app(SiteAppearanceSettings::class)->normalizePageBackgroundMeta([
            'background' => [
                'enabled' => true,
                'path' => 'site-appearance/old.jpg',
                'upload' => 'site-appearance/upload.gif',
            ],
        ]);

        $this->assertSame([
            'enabled' => true,
            'path' => 'site-appearance/upload.gif',
        ], $meta['background']);
    }
}
