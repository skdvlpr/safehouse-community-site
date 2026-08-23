<?php

namespace Tests\Unit;

use App\Support\CmsHtml;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CmsHtmlTest extends TestCase
{
    #[Test]
    public function it_passes_through_rich_html(): void
    {
        $html = '<p>Hello <strong>world</strong></p>';

        $this->assertSame($html, CmsHtml::render($html));
    }

    #[Test]
    public function it_escapes_and_breaks_plain_text(): void
    {
        $this->assertSame(
            "Line 1<br />\nLine 2 &amp; more",
            CmsHtml::render("Line 1\nLine 2 & more")
        );
    }

    #[Test]
    public function it_returns_empty_for_blank(): void
    {
        $this->assertSame('', CmsHtml::render(null));
        $this->assertSame('', CmsHtml::render('   '));
    }
}
