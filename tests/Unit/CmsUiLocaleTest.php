<?php

namespace Tests\Unit;

use App\Services\CmsUiLocale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CmsUiLocaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_defaults_to_config_locale(): void
    {
        config(['cms.locale' => 'it', 'cms.available_locales' => ['it', 'en']]);

        $this->assertSame('it', app(CmsUiLocale::class)->current());
    }

    public function test_session_overrides_default(): void
    {
        config(['cms.locale' => 'it', 'cms.available_locales' => ['it', 'en']]);

        $locale = app(CmsUiLocale::class);
        $this->assertTrue($locale->set('en'));
        $this->assertSame('en', $locale->current());
        $this->assertFalse($locale->set('ru'));
        $this->assertSame('en', $locale->current());
    }

    public function test_public_site_url_uses_current_locale(): void
    {
        config(['cms.available_locales' => ['it', 'en']]);

        $locale = app(CmsUiLocale::class);
        $locale->set('en');

        $this->assertSame(url('/en'), $locale->publicSiteUrl());
    }
}
