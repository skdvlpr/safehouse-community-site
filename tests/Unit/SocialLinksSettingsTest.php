<?php

namespace Tests\Unit;

use App\Services\SocialLinksSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SocialLinksSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_filled_returns_only_valid_links(): void
    {
        app(SocialLinksSettings::class)->save([
            'instagram' => 'https://www.instagram.com/safehouse',
            'facebook' => '',
            'whatsapp' => 'wa.me/393331112222',
            'email' => 'info@safehouse.community',
            'tiktok' => 'not-a-url',
        ]);

        $filled = app(SocialLinksSettings::class)->filled();
        $keys = array_column($filled, 'key');

        $this->assertSame(['instagram', 'whatsapp', 'email'], $keys);
        $this->assertSame('https://www.instagram.com/safehouse', $filled[0]['href']);
        $this->assertSame('https://wa.me/393331112222', $filled[1]['href']);
        $this->assertSame('mailto:info@safehouse.community', $filled[2]['href']);
    }
}
