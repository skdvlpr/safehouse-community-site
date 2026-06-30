<?php

namespace Tests\Feature;

use App\Models\DonationCampaign;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteLayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_includes_header_footer_and_locale_switcher(): void
    {
        $this->get('/it')
            ->assertOk()
            ->assertSee('images/logo-horizontal.svg', false)
            ->assertSee('/en', false)
            ->assertSee('/ru', false)
            ->assertSee(__('site.nav.donations', [], 'it'), false);
    }

    public function test_donation_pages_use_shared_layout(): void
    {
        DonationCampaign::factory()->create([
            'slug' => 'layout-test',
            'is_active' => true,
        ]);

        $this->get('/it/donazioni/layout-test')
            ->assertOk()
            ->assertSee('images/logo-horizontal.svg', false)
            ->assertSee(__('site.footer.tagline', [], 'it'), false);
    }

    public function test_locale_switcher_preserves_path(): void
    {
        DonationCampaign::factory()->create([
            'slug' => 'locale-path',
            'is_active' => true,
        ]);

        $this->get('/it/donazioni/locale-path')
            ->assertOk()
            ->assertSee('/en/donazioni/locale-path', false);
    }
}
