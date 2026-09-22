<?php

namespace Tests\Unit;

use Database\Seeders\Data\LegalPagesContent;
use PHPUnit\Framework\TestCase;

class LegalPagesContentTest extends TestCase
{
    public function test_cookie_copy_does_not_point_to_footer_preferences(): void
    {
        $pages = LegalPagesContent::pages();

        $this->assertArrayHasKey('cookie', $pages);

        $it = $pages['cookie']['body']['it'];
        $en = $pages['cookie']['body']['en'];

        $this->assertStringNotContainsString('nel footer', $it);
        $this->assertStringNotContainsString('link nel footer', $it);
        $this->assertStringNotContainsString('«Preferenze cookie»', $it);
        $this->assertStringContainsString('pulsante in questa pagina', $it);

        $this->assertStringNotContainsString('footer link', $en);
        $this->assertStringNotContainsString('link in the footer', $en);
        $this->assertStringNotContainsString('«Cookie preferences»', $en);
        $this->assertStringContainsString('button on this page', $en);
    }
}
