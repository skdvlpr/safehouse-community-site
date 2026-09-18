<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Services\PageService;
use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MeasurementGateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PageSeeder::class);
    }

    public function test_default_home_html_has_no_gtm_hosts_or_container_id(): void
    {
        $this->get('/it')
            ->assertOk()
            ->assertSee('id="measurement-boot"', false)
            ->assertSee('data-measurement-enabled="false"', false)
            ->assertDontSee('googletagmanager.com', false)
            ->assertDontSee('GTM-', false)
            ->assertDontSee('www.googletagmanager.com/ns.html', false);
    }

    public function test_bootable_home_exposes_container_on_boot_node_without_blade_snippet(): void
    {
        config([
            'measurement.enabled' => true,
            'measurement.container_id' => 'GTM-TEST1',
        ]);

        $this->get('/it')
            ->assertOk()
            ->assertSee('data-measurement-enabled="true"', false)
            ->assertSee('data-measurement-container="GTM-TEST1"', false)
            ->assertDontSee('googletagmanager.com', false)
            ->assertDontSee('gtm.js', false)
            ->assertDontSee('www.googletagmanager.com/ns.html', false);
    }

    public function test_invalid_container_id_is_treated_as_off(): void
    {
        config([
            'measurement.enabled' => true,
            'measurement.container_id' => 'not-a-container',
        ]);

        $this->get('/it')
            ->assertOk()
            ->assertSee('data-measurement-enabled="false"', false)
            ->assertDontSee('googletagmanager.com', false)
            ->assertDontSee('GTM-', false)
            ->assertDontSee('not-a-container', false);
    }

    public function test_preview_route_is_never_bootable(): void
    {
        config([
            'measurement.enabled' => true,
            'measurement.container_id' => 'GTM-TEST1',
        ]);

        $page = Page::query()->where('key', 'about')->firstOrFail();
        $page->update(['is_published' => false]);

        $url = app(PageService::class)->previewUrl($page, 'it');

        $this->assertNotNull($url);

        $this->get($url)
            ->assertOk()
            ->assertSee('data-measurement-enabled="false"', false)
            ->assertDontSee('GTM-TEST1', false)
            ->assertDontSee('googletagmanager.com', false);
    }

    public function test_cms_login_does_not_use_public_measurement_boot(): void
    {
        config([
            'measurement.enabled' => true,
            'measurement.container_id' => 'GTM-TEST1',
        ]);

        $this->get('/cms-safehouse/login')
            ->assertOk()
            ->assertDontSee('id="measurement-boot"', false)
            ->assertDontSee('GTM-TEST1', false)
            ->assertDontSee('googletagmanager.com', false);
    }
}
