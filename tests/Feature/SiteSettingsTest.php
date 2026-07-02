<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Models\User;
use App\Services\SiteSettingsService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SiteSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_database_value_overrides_env_fallback(): void
    {
        config(['stripe.key' => 'pk_test_from_env']);

        SiteSetting::query()->create([
            'key' => 'stripe.key',
            'value' => 'pk_live_from_db',
            'is_encrypted' => false,
        ]);

        Cache::forget('site_setting:stripe.key');

        $this->assertSame('pk_live_from_db', app(SiteSettingsService::class)->get('stripe.key'));
    }

    public function test_encrypted_secret_round_trips(): void
    {
        app(SiteSettingsService::class)->updateMany([
            'stripe.secret' => 'sk_live_test_secret',
        ]);

        $this->assertSame('sk_live_test_secret', app(SiteSettingsService::class)->get('stripe.secret'));
    }

    public function test_empty_encrypted_update_keeps_previous_value(): void
    {
        app(SiteSettingsService::class)->updateMany([
            'stripe.secret' => 'sk_live_keep_me',
        ]);

        app(SiteSettingsService::class)->updateMany([
            'stripe.secret' => '',
        ]);

        $this->assertSame('sk_live_keep_me', app(SiteSettingsService::class)->get('stripe.secret'));
    }

    public function test_form_state_is_flattened_before_persisting(): void
    {
        app(SiteSettingsService::class)->updateFromFormState([
            'stripe' => [
                'key' => 'pk_test_form',
                'secret' => 'sk_test_form',
                'currency' => 'EUR',
            ],
            'espocrm' => [
                'base_url' => 'https://crm.test',
                'prima_nota' => [
                    'default_beneficiary_name' => 'Safe House',
                ],
            ],
        ]);

        $this->assertDatabaseHas('site_settings', [
            'key' => 'stripe.key',
            'value' => 'pk_test_form',
        ]);

        $this->assertDatabaseHas('site_settings', [
            'key' => 'espocrm.prima_nota.default_beneficiary_name',
            'value' => 'Safe House',
        ]);

        $this->assertSame('sk_test_form', app(SiteSettingsService::class)->get('stripe.secret'));
    }

    public function test_nested_form_values_round_trip(): void
    {
        app(SiteSettingsService::class)->updateMany([
            'stripe.key' => 'pk_test_nested',
            'stripe.currency' => 'EUR',
        ]);

        Cache::forget('site_setting:stripe.key');
        Cache::forget('site_setting:stripe.currency');

        $nested = app(SiteSettingsService::class)->nestedFormValues();

        $this->assertSame('pk_test_nested', $nested['stripe']['key'] ?? null);
        $this->assertSame('EUR', $nested['stripe']['currency'] ?? null);
    }

    public function test_integrations_page_is_super_admin_only(): void
    {
        $editor = User::factory()->create();
        $editor->assignRole('editor');

        $this->actingAs($editor)
            ->get('/cms-safehouse/manage-integrations')
            ->assertForbidden();
    }
}
