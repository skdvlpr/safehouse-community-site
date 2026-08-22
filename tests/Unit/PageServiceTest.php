<?php

namespace Tests\Unit;

use App\Models\Page;
use App\Services\PageService;
use Tests\TestCase;

class PageServiceTest extends TestCase
{
    public function test_template_view_falls_back_to_default(): void
    {
        $service = new PageService;
        $page = new Page(['template' => 'unknown-template']);

        $this->assertSame('pages.templates.default', $service->templateView($page));
    }

    public function test_localized_service_cards_picks_locale_with_italian_fallback(): void
    {
        $service = new PageService;
        $meta = [
            'services' => [
                [
                    'title' => ['it' => 'Titolo IT', 'en' => 'Title EN'],
                    'body' => ['it' => 'Corpo IT'],
                ],
            ],
        ];

        $cards = $service->localizedServiceCards($meta, 'en');

        $this->assertCount(1, $cards);
        $this->assertSame('Title EN', $cards[0]['title']);
        $this->assertSame('Corpo IT', $cards[0]['body']);
    }
}
