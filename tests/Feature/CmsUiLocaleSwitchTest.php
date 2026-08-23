<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\CmsUiLocale;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CmsUiLocaleSwitchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_authenticated_user_can_switch_cms_locale(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $this->actingAs($user)
            ->from('/cms-safehouse')
            ->post('/cms-safehouse/locale/en')
            ->assertRedirect('/cms-safehouse');

        $this->assertSame('en', session(CmsUiLocale::SESSION_KEY));
        $this->assertSame('en', app(CmsUiLocale::class)->current());
    }

    public function test_cms_uses_session_locale_on_dashboard(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $this->actingAs($user)
            ->withSession([CmsUiLocale::SESSION_KEY => 'en'])
            ->get('/cms-safehouse')
            ->assertOk()
            ->assertSee('Back to site', false)
            ->assertSee('Pages', false);
    }

    public function test_invalid_cms_locale_is_rejected(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $this->actingAs($user)
            ->post('/cms-safehouse/locale/ru')
            ->assertNotFound();
    }
}
