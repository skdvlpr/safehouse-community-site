<?php

namespace Tests\Feature;

use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;
    public function test_home_renders_hero_and_stats_shell(): void
    {
        $this->seed(\Database\Seeders\PageSeeder::class);
        $this->seed(\Database\Seeders\DeploySiteContentSeeder::class);

        $this->get('/it')
            ->assertOk()
            ->assertSee('Safe House Community', false)
            ->assertSee('Comunità di accoglienza e solidarietà.', false)
            ->assertSee('Il nostro impatto', false)
            ->assertSee('Volontari accolti', false);
    }

    public function test_home_stats_use_configured_placeholders(): void
    {
        $this->seed(\Database\Seeders\PageSeeder::class);

        config(['home.stats' => [
            ['value' => '42', 'label' => 'site.home.stats.volunteers'],
        ]]);

        Page::query()->where('key', 'home')->delete();

        $this->get('/it')
            ->assertOk()
            ->assertSee('42', false)
            ->assertSee(__('site.home.stats.volunteers', [], 'it'), false);
    }
}
