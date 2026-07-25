<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Models\User;
use App\Services\ApplicationCacheClearer;
use App\Services\SiteSettingsService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class DeveloperToolsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_super_admin_can_open_developer_tools_page(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $this->actingAs($user)
            ->get('/cms-safehouse/manage-developer-tools')
            ->assertOk();
    }

    public function test_editor_cannot_open_developer_tools_page(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->actingAs($user)
            ->get('/cms-safehouse/manage-developer-tools')
            ->assertForbidden();
    }

    public function test_no_cache_setting_adds_cache_control_headers(): void
    {
        config(['developer.no_cache' => false]);

        SiteSetting::query()->create([
            'key' => 'developer.no_cache',
            'value' => '1',
            'is_encrypted' => false,
        ]);

        Cache::forget('site_setting:developer.no_cache');

        $response = $this->get('/it')->assertOk();
        $cacheControl = strtolower((string) $response->headers->get('Cache-Control'));

        $this->assertStringContainsString('no-store', $cacheControl);
        $this->assertStringContainsString('no-cache', $cacheControl);
    }

    public function test_clear_cache_forgets_site_settings_cache(): void
    {
        SiteSetting::query()->create([
            'key' => 'stripe.key',
            'value' => 'pk_test_cached',
            'is_encrypted' => false,
        ]);

        $settings = app(SiteSettingsService::class);
        $this->assertSame('pk_test_cached', $settings->get('stripe.key'));

        SiteSetting::query()->where('key', 'stripe.key')->update(['value' => 'pk_test_fresh']);

        app(ApplicationCacheClearer::class)->clearAll();

        $this->assertSame('pk_test_fresh', app(SiteSettingsService::class)->get('stripe.key'));
    }
}
