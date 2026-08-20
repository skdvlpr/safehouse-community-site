<?php

namespace Tests\Feature;

use App\Services\SocialLinksSettings;
use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SocialLinksPublicTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_and_footer_render_filled_social_icons(): void
    {
        $this->seed(PageSeeder::class);

        app(SocialLinksSettings::class)->save([
            'instagram' => 'https://www.instagram.com/safehouse.community',
            'email' => 'info@safehouse.community',
        ]);

        $this->get('/it')
            ->assertOk()
            ->assertSee('https://www.instagram.com/safehouse.community', false)
            ->assertSee('mailto:info@safehouse.community', false)
            ->assertSee('social-links__item--instagram', false)
            ->assertSee('social-links__item--email', false)
            ->assertDontSee('social-links__item--tiktok', false);
    }
}
