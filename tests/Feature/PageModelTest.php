<?php

namespace Tests\Feature;

use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PageModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_pages_migration_runs(): void
    {
        $this->assertTrue(
            Schema::hasTable('pages'),
            'The pages table should exist after migrations.',
        );
    }

    public function test_page_factory_saves_it_and_en_translations(): void
    {
        $page = Page::factory()->create([
            'title' => [
                'it' => 'Chi siamo',
                'en' => 'About us',
            ],
            'slug' => [
                'it' => 'chi-siamo',
                'en' => 'about-us',
            ],
            'body' => [
                'it' => 'Contenuto in italiano.',
                'en' => 'Content in English.',
            ],
        ]);

        $page->refresh();

        $this->assertSame('Chi siamo', $page->getTranslation('title', 'it'));
        $this->assertSame('About us', $page->getTranslation('title', 'en'));
        $this->assertSame('chi-siamo', $page->getTranslation('slug', 'it'));
        $this->assertSame('about-us', $page->getTranslation('slug', 'en'));
        $this->assertSame('Contenuto in italiano.', $page->getTranslation('body', 'it'));
        $this->assertSame('Content in English.', $page->getTranslation('body', 'en'));
    }
}
