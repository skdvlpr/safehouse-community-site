<?php

namespace Tests\Feature;

use App\Models\GdprConsent;
use App\Services\ContactSubmissionService;
use App\Services\SiteSettingsService;
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
            ->assertSee('data-cookie-dismiss', false)
            ->assertSee('data-cookie-reopen', false)
            ->assertSee(__('site.cookie.accept_all'), false)
            ->assertSee('data-cookie-analytics', false);
    }

    public function test_cookie_policy_page_has_preferences_reopen_button(): void
    {
        $this->get('/it/cookie-policy')
            ->assertOk()
            ->assertSee('data-cookie-page-reopen', false)
            ->assertSee(__('site.cookie.reopen_page'), false);

        $this->get('/it/privacy-policy')
            ->assertOk()
            ->assertDontSee('data-cookie-page-reopen', false);
    }

    public function test_bootable_banner_copy_is_plain_language_not_vendor_names(): void
    {
        app(SiteSettingsService::class)->updateMany([
            'measurement.enabled' => '1',
            'measurement.container_id' => 'GTM-TEST1',
        ]);

        $this->get('/it')
            ->assertOk()
            ->assertSee(__('site.cookie.message_bootable'), false)
            ->assertSee('Ti invitiamo ad accettare', false)
            ->assertSee('non ti danneggiano', false)
            ->assertDontSee('Google Analytics 4', false)
            ->assertDontSee('Google Tag Manager', false);
    }

    public function test_cookie_consent_endpoint_stores_audit_record(): void
    {
        $response = $this->withHeaders(['User-Agent' => 'Safehouse Consent Agent'])->postJson('/it/cookie-consent', [
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
        $this->assertSame(ContactSubmissionService::hashIp('127.0.0.1'), $consent->ip_hash);
        $this->assertSame(
            ContactSubmissionService::hashUserAgent('Safehouse Consent Agent'),
            $consent->user_agent_hash,
        );
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
