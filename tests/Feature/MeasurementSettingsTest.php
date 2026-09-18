<?php

namespace Tests\Feature;

use App\Filament\Pages\ManageIntegrations;
use App\Models\User;
use App\Services\MeasurementBootService;
use App\Services\SiteSettingsService;
use Database\Seeders\PageSeeder;
use Database\Seeders\RoleSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MeasurementSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->seed(PageSeeder::class);
    }

    public function test_cms_save_enables_public_boot_node(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        Filament::setCurrentPanel(Filament::getPanel('cms-safehouse'));

        Livewire::actingAs($admin)
            ->test(ManageIntegrations::class)
            ->fillForm([
                'measurement.enabled' => true,
                'measurement.container_id' => 'GTM-TEST1',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertTrue(app(SiteSettingsService::class)->isTruthy('measurement.enabled'));
        $this->assertSame('GTM-TEST1', app(SiteSettingsService::class)->getRaw('measurement.container_id'));

        $this->get('/it')
            ->assertOk()
            ->assertSee('data-measurement-enabled="true"', false)
            ->assertSee('data-measurement-container="GTM-TEST1"', false)
            ->assertDontSee('googletagmanager.com', false);
    }

    public function test_cms_off_overrides_env_on(): void
    {
        config([
            'measurement.enabled' => true,
            'measurement.container_id' => 'GTM-TEST1',
        ]);

        app(SiteSettingsService::class)->updateMany([
            'measurement.enabled' => '0',
            'measurement.container_id' => 'GTM-TEST1',
        ]);

        $this->get('/it')
            ->assertOk()
            ->assertSee('data-measurement-enabled="false"', false)
            ->assertDontSee('GTM-TEST1', false);
    }

    public function test_invalid_cms_container_id_is_not_bootable(): void
    {
        app(SiteSettingsService::class)->updateMany([
            'measurement.enabled' => '1',
            'measurement.container_id' => 'not-a-container',
        ]);

        $this->assertFalse(app(MeasurementBootService::class)->isBootable());

        $this->get('/it')
            ->assertOk()
            ->assertSee('data-measurement-enabled="false"', false)
            ->assertDontSee('googletagmanager.com', false)
            ->assertDontSee('GTM-', false);
    }
}
