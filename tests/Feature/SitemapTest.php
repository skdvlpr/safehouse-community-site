<?php

namespace Tests\Feature;

use App\Models\DonationCampaign;
use App\Models\Page;
use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitemapTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PageSeeder::class);
    }

    public function test_sitemap_lists_public_hubs_and_hides_private_urls(): void
    {
        $campaign = DonationCampaign::factory()->create([
            'slug' => 'cucina-solidale',
            'is_active' => true,
            'title' => ['it' => 'Cucina solidale', 'en' => 'Community kitchen'],
        ]);

        $xml = $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->getContent();

        $this->assertStringContainsString('/it/donations', $xml);
        $this->assertStringContainsString('/it/volunteers', $xml);
        $this->assertStringContainsString('/it/donations/5-per-thousand', $xml);
        $this->assertStringContainsString('/it/about-us', $xml);
        $this->assertStringContainsString('/it/contact', $xml);
        $this->assertStringContainsString('/it/donations/cucina-solidale', $xml);
        $this->assertStringContainsString('/it/donations/cucina-solidale/privacy', $xml);
        $this->assertStringNotContainsString('thank-you', $xml);
        $this->assertStringNotContainsString('/_preview/', $xml);

        $campaign->update(['is_active' => false]);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertDontSee('cucina-solidale', false);

        Page::query()->where('key', 'about')->update(['is_published' => false]);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertDontSee('/it/about-us', false);
    }

    public function test_robots_names_the_sitemap(): void
    {
        $this->get('/robots.txt')
            ->assertOk()
            ->assertSee('Sitemap: '.url('/sitemap.xml'), false);
    }
}
