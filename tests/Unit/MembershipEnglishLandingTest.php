<?php

namespace Tests\Unit;

use App\Support\LandingContent;
use App\Support\MembershipEnglishLanding;
use PHPUnit\Framework\TestCase;

class MembershipEnglishLandingTest extends TestCase
{
    public function test_english_paragraph_headings_become_animated_cards(): void
    {
        $html = '<p><strong>Together</strong></p><p>Intro.</p><p><strong>How to become a member</strong></p><p>Steps.</p><p><strong>Our Values</strong></p><p>Long.</p>';

        $split = MembershipEnglishLanding::splitParagraphHeadings($html);
        $layout = LandingContent::fromHtml($split);

        $this->assertNotSame('', $layout->intro);
        $this->assertCount(1, $layout->cards);
        $this->assertSame(['Solidarity', 'Participation', 'Inclusion', 'Transparency'], $layout->values);
    }
}
