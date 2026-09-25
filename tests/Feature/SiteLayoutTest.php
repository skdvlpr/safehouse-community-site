<?php

namespace Tests\Feature;

use App\Models\DonationCampaign;
use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteLayoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PageSeeder::class);
    }

    public function test_home_includes_header_footer_and_locale_switcher(): void
    {
        $this->get('/it')
            ->assertOk()
            ->assertSee('images/logo.png', false)
            ->assertSee('Safe House', false)
            ->assertSee('/en', false)
            ->assertDontSee('/ru', false)
            ->assertSee(__('site.nav.donate', [], 'it'), false)
            ->assertSee(__('site.nav.donate_short', [], 'it'), false)
            ->assertSee('data-header-drawer-open', false)
            ->assertSee('data-header-drawer', false)
            ->assertSee('site-header-drawer', false)
            ->assertSee(__('site.nav.menu', [], 'it'), false)
            ->assertSee(__('site.nav.close_menu', [], 'it'), false)
            ->assertSee(__('site.nav.drawer_title', [], 'it'), false)
            ->assertSee(__('site.nav.contact_us', [], 'it'), false)
            ->assertSee('/it/contact', false)
            ->assertSee('— Safe House ETS', false);
    }

    public function test_mobile_drawer_is_branded_without_a_home_link(): void
    {
        $html = $this->get('/it')->assertOk()->getContent();

        $this->assertTrue((bool) preg_match('/id="site-header-drawer"[\s\S]*?<\/nav>/', $html, $drawer));
        $this->assertStringContainsString('Safe House ETS', $drawer[0]);
        $this->assertStringNotContainsString('>Home</', $drawer[0]);
        $this->assertStringContainsString('aria-label="'.__('site.nav.menu', [], 'it').'"', $html);

        $this->assertTrue((bool) preg_match('/<nav class="hidden items-center[\s\S]*?<\/nav>/', $html, $desktop));
        $this->assertStringNotContainsString('>Home</', $desktop[0]);
    }

    public function test_document_title_suffix_is_ets_and_og_title_stays_bare(): void
    {
        $html = $this->get('/it')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/<title>[^<]*— Safe House ETS<\/title>/', $html);
        $this->assertTrue((bool) preg_match('/property="og:title" content="([^"]*)"/', $html, $og));
        $this->assertStringNotContainsString('— Safe House ETS', $og[1]);

        $this->get('/en/donations')
            ->assertOk()
            ->assertSee('— Safe House ETS', false);
    }

    public function test_english_header_uses_short_donate_label(): void
    {
        $this->get('/en')
            ->assertOk()
            ->assertSee(__('site.nav.donate_short', [], 'en'), false)
            ->assertSee(__('site.nav.donate', [], 'en'), false)
            ->assertSee('data-header-drawer-open', false);
    }

    public function test_donation_pages_use_shared_layout(): void
    {
        DonationCampaign::factory()->create([
            'slug' => 'layout-test',
            'is_active' => true,
        ]);

        $this->get('/it/donations/layout-test')
            ->assertOk()
            ->assertSee('images/logo.png', false)
            ->assertSee('Safe House', false)
            ->assertSee('INCLUDERE', false)
            ->assertDontSee(__('site.footer.tagline', [], 'it'), false);
    }

    public function test_locale_switcher_preserves_path(): void
    {
        DonationCampaign::factory()->create([
            'slug' => 'locale-path',
            'is_active' => true,
        ]);

        $this->get('/it/donations/locale-path')
            ->assertOk()
            ->assertSee('/en/donations/locale-path', false);
    }
}
