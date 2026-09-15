<?php

namespace Tests\Feature;

use App\Filament\Pages\ManageCaptchaSettings;
use App\Models\User;
use App\Services\SiteSettingsService;
use App\Services\TurnstileVerifier;
use Database\Seeders\RoleSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CaptchaSettingsPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_blank_secret_save_does_not_wipe_stored_secret(): void
    {
        app(SiteSettingsService::class)->updateMany([
            'turnstile.enabled' => '1',
            'turnstile.site_key' => 'site-key',
            'turnstile.secret_key' => 'keep-this-secret',
        ]);

        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        Filament::setCurrentPanel(Filament::getPanel('cms-safehouse'));

        Livewire::actingAs($admin)
            ->test(ManageCaptchaSettings::class)
            ->fillForm([
                'turnstile.enabled' => true,
                'turnstile.site_key' => 'site-key',
                'turnstile.secret_key' => '',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('keep-this-secret', app(SiteSettingsService::class)->getRaw('turnstile.secret_key'));
        $this->assertTrue(app(TurnstileVerifier::class)->enabled());
    }

    public function test_enabled_without_site_key_disables_verifier(): void
    {
        app(SiteSettingsService::class)->updateMany([
            'turnstile.enabled' => '1',
            'turnstile.site_key' => 'site-key',
            'turnstile.secret_key' => 'secret-key',
        ]);

        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        Filament::setCurrentPanel(Filament::getPanel('cms-safehouse'));

        Livewire::actingAs($admin)
            ->test(ManageCaptchaSettings::class)
            ->fillForm([
                'turnstile.enabled' => true,
                'turnstile.site_key' => '',
                'turnstile.secret_key' => '',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertFalse(app(TurnstileVerifier::class)->enabled());
        $this->assertSame('secret-key', app(SiteSettingsService::class)->getRaw('turnstile.secret_key'));
    }
}
