<?php

namespace Tests\Unit;

use App\Support\PageCarousel;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PageCarouselTest extends TestCase
{
    public function test_slides_resolve_public_and_storage_paths(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('page-carousels/test.jpg', 'fake');

        $meta = [
            'carousel' => [
                ['path' => 'page-carousels/test.jpg', 'alt' => ['it' => 'Da storage']],
                ['path' => 'images/carousel-demo/slide-1.jpg', 'alt' => ['it' => 'Da public']],
            ],
        ];

        $slides = PageCarousel::slides($meta, 'it');

        $this->assertCount(2, $slides);
        $this->assertStringContainsString('page-carousels/test.jpg', $slides[0]['url']);
        $this->assertSame('Da storage', $slides[0]['alt']);
        $this->assertStringContainsString('carousel-demo/slide-1.jpg', $slides[1]['url']);
    }

    public function test_normalize_meta_strips_empty_slides_and_alt(): void
    {
        $normalized = PageCarousel::normalizeMeta([
            'carousel' => [
                ['path' => '', 'alt' => ['it' => 'Ignorato']],
                ['path' => 'page-carousels/hero.jpg', 'alt' => ['it' => 'Hero', 'en' => '']],
            ],
            'values' => ['it' => 'keep'],
        ]);

        $this->assertSame('keep', $normalized['values']['it']);
        $this->assertCount(1, $normalized['carousel']);
        $this->assertSame('page-carousels/hero.jpg', $normalized['carousel'][0]['path']);
        $this->assertSame(['it' => 'Hero'], $normalized['carousel'][0]['alt']);
    }
}
