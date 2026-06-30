<?php

namespace Tests\Unit;

use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_production_seed_does_not_reference_local_demo_carousel_images(): void
    {
        config(['app.env' => 'production']);
        putenv('SEED_DEMO_CAROUSEL=false');

        $this->seed(PageSeeder::class);

        $about = \App\Models\Page::query()->where('key', 'about')->first();

        $this->assertNotNull($about);
        $carousel = $about->meta['carousel'] ?? null;
        $this->assertTrue($carousel === null || $carousel === []);
    }

    public function test_local_seed_includes_demo_carousel_slides(): void
    {
        putenv('SEED_DEMO_CAROUSEL');
        config(['app.env' => 'local']);

        $this->seed(PageSeeder::class);

        $about = \App\Models\Page::query()->where('key', 'about')->first();

        $this->assertCount(2, $about->meta['carousel'] ?? []);
        $this->assertSame('images/carousel-demo/slide-1.jpg', $about->meta['carousel'][0]['path']);
    }
}
