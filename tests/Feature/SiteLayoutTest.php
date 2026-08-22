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
            ->assertSee(__('site.nav.donations', [], 'it'), false)
            ->assertSee(__('site.nav.contact_us', [], 'it'), false)
            ->assertSee('/it/contact', false);
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
            ->assertSee(__('site.footer.tagline', [], 'it'), false);
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
