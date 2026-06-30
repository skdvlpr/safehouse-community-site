<?php

namespace Tests\Feature;

use Tests\TestCase;

class DesignTokensTest extends TestCase
{
    public function test_app_css_declares_safehouse_design_tokens(): void
    {
        $css = file_get_contents(base_path('resources/css/app.css'));

        $this->assertIsString($css);
        $this->assertStringContainsString('--color-safehouse-page: #050505', $css);
        $this->assertStringContainsString('--color-safehouse-primary: #dc2626', $css);
        $this->assertStringContainsString('--safehouse-glass-bg:', $css);
        $this->assertStringContainsString("font-family: 'JetBrains Sans'", $css);
        $this->assertStringContainsString("url('/images/bg.svg')", $css);
    }

    public function test_polygon_background_asset_is_available(): void
    {
        $this->assertFileExists(public_path('images/bg.svg'));
    }

    public function test_jetbrains_sans_font_is_available(): void
    {
        $this->assertFileExists(public_path('fonts/JetBrainsSans[wght].woff2'));
    }
}
