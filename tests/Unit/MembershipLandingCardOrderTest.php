<?php

namespace Tests\Unit;

use App\Support\LandingContent;
use App\Support\MembershipLandingCardOrder;
use PHPUnit\Framework\TestCase;

class MembershipLandingCardOrderTest extends TestCase
{
    public function test_swap_html_puts_what_it_means_before_who_can(): void
    {
        $html = implode('<hr>', [
            '<h2>Intro</h2><p>Hero.</p>',
            '<h3>Protagonista</h3><p>1</p>',
            '<h3>Perché</h3><p>2</p>',
            '<h3>Come</h3><p>3</p>',
            '<h2>Chi può diventare socio?</h2><p>4</p>',
            '<h1>La quota associativa</h1><p>5</p>',
            '<h1>Cosa significa essere socio?</h1><p>6</p>',
        ]);

        $swapped = MembershipLandingCardOrder::swapHtml($html);
        $layout = LandingContent::fromHtml($swapped);

        $this->assertSame('Cosa significa essere socio?', LandingContent::headingText($layout->cards[3]));
        $this->assertSame('La quota associativa', LandingContent::headingText($layout->cards[4]));
        $this->assertSame('Chi può diventare socio?', LandingContent::headingText($layout->cards[5]));
        $this->assertSame($swapped, MembershipLandingCardOrder::swapHtml($swapped));
    }

    public function test_swap_meta_cards_by_italian_title(): void
    {
        $cards = [
            ['title' => ['it' => 'Uno']],
            ['title' => ['it' => 'Due']],
            ['title' => ['it' => 'Tre']],
            ['title' => ['it' => 'Chi può diventare socio?']],
            ['title' => ['it' => 'La quota associativa']],
            ['title' => ['it' => 'Cosa significa essere socio?']],
        ];

        $swapped = MembershipLandingCardOrder::swapMetaCards($cards);

        $this->assertSame('Cosa significa essere socio?', $swapped[3]['title']['it']);
        $this->assertSame('Chi può diventare socio?', $swapped[5]['title']['it']);
        $this->assertSame($swapped, MembershipLandingCardOrder::swapMetaCards($swapped));
    }
}
