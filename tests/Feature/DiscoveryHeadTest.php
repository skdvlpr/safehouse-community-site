<?php

namespace Tests\Feature;

use App\Models\DonationCampaign;
use App\Models\Page;
use App\Services\PageService;
use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiscoveryHeadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PageSeeder::class);
    }

    public function test_editor_override_stays_in_its_locale(): void
    {
        $page = Page::query()->where('key', 'about')->firstOrFail();
        $meta = $page->meta ?? [];
        $meta['seo_description'] = [
            'it' => 'Descrizione italiana solo per Chi siamo.',
            'en' => '',
        ];
        $page->update(['meta' => $meta]);

        $italian = $this->get('/it/about-us');
        $italian->assertOk();
        $italian->assertSee('Descrizione italiana solo per Chi siamo.', false);
        $italian->assertSee('<meta name="description"', false);

        $english = $this->get('/en/about-us');
        $english->assertOk();
        $english->assertDontSee('Descrizione italiana solo per Chi siamo.', false);
    }

    public function test_home_and_about_do_not_share_a_description(): void
    {
        $home = $this->get('/it')->assertOk()->getContent();
        $about = $this->get('/it/about-us')->assertOk()->getContent();

        preg_match('/<meta name="description" content="([^"]*)"/', $home, $homeMatch);
        preg_match('/<meta name="description" content="([^"]*)"/', $about, $aboutMatch);

        $this->assertNotSame('', $homeMatch[1] ?? '');
        $this->assertNotSame($homeMatch[1] ?? '', $aboutMatch[1] ?? '');
    }

    public function test_locale_alternates_and_share_tags_skip_russian(): void
    {
        $html = $this->get('/it/about-us')->assertOk()->getContent();

        $this->assertStringContainsString('rel="canonical"', $html);
        $this->assertStringContainsString('hreflang="it"', $html);
        $this->assertStringContainsString('hreflang="en"', $html);
        $this->assertStringNotContainsString('hreflang="ru"', $html);
        $this->assertStringContainsString('property="og:title"', $html);
        $this->assertStringContainsString('property="og:description"', $html);
        $this->assertStringContainsString('property="og:locale" content="it_IT"', $html);
    }

    public function test_preview_and_thank_you_are_noindex(): void
    {
        $page = Page::query()->where('key', 'about')->firstOrFail();
        $url = app(PageService::class)->previewUrl($page, 'it');

        $this->get($url)
            ->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false);

        DonationCampaign::factory()->create(['slug' => 'grazie-test', 'is_active' => true]);

        $this->get('/it/donations/grazie-test/thank-you?payment_intent=pi_test_discovery')
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false);
    }
}
