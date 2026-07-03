<?php

namespace Tests\Unit;

use App\Services\SiteSettingsService;
use App\Services\TurnstileVerifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TurnstileVerifierTest extends TestCase
{
    use RefreshDatabase;

    public function test_captcha_is_disabled_by_default_until_enabled_in_cms(): void
    {
        config()->set('turnstile.enabled', true);
        config()->set('turnstile.site_key', 'site-key');
        config()->set('turnstile.secret_key', 'secret-key');

        $verifier = app(TurnstileVerifier::class);

        $this->assertFalse($verifier->enabled());
        $this->assertTrue($verifier->verify(null));
    }

    public function test_cms_can_enable_captcha_when_keys_are_present(): void
    {
        app(SiteSettingsService::class)->updateMany([
            'turnstile.enabled' => '1',
            'turnstile.site_key' => 'site-key',
            'turnstile.secret_key' => 'secret-key',
        ]);

        $verifier = app(TurnstileVerifier::class);

        $this->assertTrue($verifier->enabled());
    }

    public function test_cms_can_explicitly_disable_captcha_after_being_enabled(): void
    {
        app(SiteSettingsService::class)->updateMany([
            'turnstile.enabled' => '1',
            'turnstile.site_key' => 'site-key',
            'turnstile.secret_key' => 'secret-key',
        ]);

        $this->assertTrue(app(TurnstileVerifier::class)->enabled());

        app(SiteSettingsService::class)->updateMany([
            'turnstile.enabled' => '0',
        ]);

        $this->assertFalse(app(TurnstileVerifier::class)->enabled());
    }
}
