<?php

namespace Tests\Feature;

use Tests\TestCase;

class HomePageTest extends TestCase
{
    public function test_home_renders_hero_and_stats_shell(): void
    {
        $this->get('/it')
            ->assertOk()
            ->assertSee(__('site.home.title', [], 'it'), false)
            ->assertSee(__('site.home.stats.heading', [], 'it'), false)
            ->assertSee(__('site.home.stats.volunteers', [], 'it'), false);
    }

    public function test_home_stats_use_configured_placeholders(): void
    {
        config(['home.stats' => [
            ['value' => '42', 'label' => 'site.home.stats.volunteers'],
        ]]);

        $this->get('/it')
            ->assertOk()
            ->assertSee('42', false)
            ->assertSee(__('site.home.stats.volunteers', [], 'it'), false);
    }
}
