<?php

namespace Tests\Unit;

use App\Support\LandingBody;
use PHPUnit\Framework\TestCase;

class LandingBodyTest extends TestCase
{
    public function test_sections_split_on_hr_and_drop_empty_parts(): void
    {
        $html = "<p>Intro</p><hr><h3>Due</h3><hr />\n<p>Tre</p><hr><p></p>";

        $this->assertSame(
            ['<p>Intro</p>', '<h3>Due</h3>', '<p>Tre</p>', '<p></p>'],
            LandingBody::sections($html),
        );
    }

    public function test_empty_html_returns_no_sections(): void
    {
        $this->assertSame([], LandingBody::sections(null));
        $this->assertSame([], LandingBody::sections('   '));
    }
}
