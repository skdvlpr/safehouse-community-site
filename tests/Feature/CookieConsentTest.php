<?php

namespace Tests\Feature;

use App\Models\GdprConsent;
use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class CookieConsentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PageSeeder::class);
    }

    public function test_home_page_includes_cookie_banner_markup(): void
    {
        $this->get('/it')
            ->assertOk()
            ->assertSee('id="cookie-consent-banner"', false)
            ->assertSee(__('site.cookie.accept_all'), false);
    }

    public function test_cookie_consent_endpoint_stores_audit_record(): void
    {
        $response = $this->postJson('/it/cookie-consent', [
            'level' => 'all',
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'status' => 'ok',
                'level' => 'all',
            ]);

        $this->assertDatabaseHas('gdpr_consents', [
            'consent_type' => 'cookie_banner_analytics',
            'granted' => true,
        ]);

        $consent = GdprConsent::query()->first();

        $this->assertNotNull($consent);
        $this->assertNotContains('ip', array_keys($consent->getAttributes()));
    }

    public function test_cookie_consent_essential_level_uses_separate_consent_type(): void
    {
        $this->postJson('/it/cookie-consent', [
            'level' => 'essential',
        ])->assertOk();

        $this->assertDatabaseHas('gdpr_consents', [
            'consent_type' => 'cookie_banner_essential',
            'granted' => true,
        ]);
    }

    public function test_cookie_consent_does_not_store_when_level_missing(): void
    {
        $this->postJson('/it/cookie-consent', []);

        $this->assertSame(0, GdprConsent::query()->count());
    }

    public function test_cookie_consent_is_rate_limited(): void
    {
        RateLimiter::clear('gdpr');

        for ($i = 0; $i < 20; $i++) {
            $this->postJson('/it/cookie-consent', ['level' => 'essential'])->assertOk();
        }

        $this->postJson('/it/cookie-consent', ['level' => 'essential'])
            ->assertStatus(429);
    }
}
