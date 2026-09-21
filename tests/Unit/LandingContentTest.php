<?php

namespace Tests\Unit;

use App\Support\LandingContent;
use PHPUnit\Framework\TestCase;

class LandingContentTest extends TestCase
{
    public function test_from_html_keeps_intro_six_cards_values_and_drops_contact_email_block(): void
    {
        $html = implode('<hr>', [
            '<h2>Insieme</h2><p>Intro.</p>',
            '<h3>Protagonista</h3><p>Uno.</p>',
            '<h3>Perché</h3><p>Due.</p>',
            '<h3>Come</h3><p>Tre.</p>',
            '<h3>Chi può</h3><p>Quattro.</p>',
            '<h3>Quota</h3><p>Cinque.</p>',
            '<h3>Essere socio</h3><p>Sei.</p>',
            '<h1>I nostri valori</h1><ul><li>Solidarietà</li><li>Crediamo nel valore lungo</li><li>Partecipazione</li><li>Inclusione</li><li>Trasparenza</li></ul>',
            '<h1>Unisciti a noi</h1><p>Ogni adesione.</p>',
            '<h2>Contattaci</h2><p>info@safehouse.community</p>',
        ]);

        $layout = LandingContent::fromHtml($html);

        $this->assertStringContainsString('Intro.', $layout->intro);
        $this->assertCount(6, $layout->cards);
        $this->assertSame(['Solidarietà', 'Partecipazione', 'Inclusione', 'Trasparenza'], $layout->values);
        $this->assertSame('Contattaci', $layout->contactHeading);
        $this->assertStringNotContainsString('info@safehouse.community', implode('', $layout->cards));
        $this->assertStringNotContainsString('Unisciti a noi', implode('', $layout->cards));
    }

    public function test_structured_meta_wins_over_html_and_caps_cards_at_six(): void
    {
        $meta = [
            'landing_values' => [
                ['label' => ['it' => 'Solidarietà']],
            ],
            'landing_cards' => [
                ['title' => ['it' => 'A'], 'body' => ['it' => '<p>A</p>']],
                ['title' => ['it' => 'B'], 'body' => ['it' => '<p>B</p>']],
                ['title' => ['it' => 'C'], 'body' => ['it' => '<p>C</p>']],
                ['title' => ['it' => 'D'], 'body' => ['it' => '<p>D</p>']],
                ['title' => ['it' => 'E'], 'body' => ['it' => '<p>E</p>']],
                ['title' => ['it' => 'F'], 'body' => ['it' => '<p>F</p>']],
                ['title' => ['it' => 'G'], 'body' => ['it' => '<p>G</p>']],
            ],
            'landing_contact_heading' => ['it' => 'Contattaci'],
        ];

        $layout = LandingContent::fromMeta($meta, 'it', '<p>Hero</p>');

        $this->assertNotNull($layout);
        $this->assertSame('Hero', trim(strip_tags($layout->intro)));
        $this->assertSame(['Solidarietà'], $layout->values);
        $this->assertCount(6, $layout->cards);
        $this->assertSame('Contattaci', $layout->contactHeading);
    }

    public function test_hydrate_form_splits_html_into_cms_repeaters(): void
    {
        $data = LandingContent::hydrateForm([
            'template' => 'landing',
            'body' => [
                'it' => implode('<hr>', [
                    '<p>Intro.</p>',
                    '<h3>Uno</h3><p>A</p>',
                    '<h1>I nostri valori</h1><ul><li>Solidarietà</li><li>Crediamo nel valore lungo</li></ul>',
                    '<h2>Contattaci</h2><p>info@safehouse.community</p>',
                ]),
            ],
            'meta' => [],
        ]);

        $this->assertStringContainsString('Intro.', $data['body']['it']);
        $this->assertStringNotContainsString('Uno', $data['body']['it']);
        $this->assertSame('Solidarietà', $data['meta']['landing_values'][0]['label']['it']);
        $this->assertCount(1, $data['meta']['landing_values']);
        $this->assertCount(1, $data['meta']['landing_cards']);
        $this->assertSame('Uno', $data['meta']['landing_cards'][0]['title']['it']);
        $this->assertSame('Contattaci', $data['meta']['landing_contact_heading']['it']);
    }
}
