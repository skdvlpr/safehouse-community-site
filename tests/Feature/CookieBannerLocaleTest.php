<?php

namespace Tests\Feature;

use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CookieBannerLocaleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PageSeeder::class);
    }

    public function test_banner_language_control_does_not_navigate(): void
    {
        $html = $this->get('/it')->assertOk()->getContent();

        $this->assertSame(1, preg_match('/<nav class="cookie-consent__langs".*?<\/nav>/s', $html, $banner));
        $this->assertStringNotContainsString('href=', $banner[0]);
        $this->assertStringContainsString('data-banner-lang="en"', $banner[0]);
        $this->assertStringContainsString('href="'.url('/en').'"', $html);
    }
}
