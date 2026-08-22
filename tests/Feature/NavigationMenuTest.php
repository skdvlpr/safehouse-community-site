<?php

namespace Tests\Feature;

use App\Models\Page;
use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigationMenuTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PageSeeder::class);
    }

    public function test_other_pages_dropdown_lists_non_standard_published_pages(): void
    {
        $this->get('/it')
            ->assertOk()
            ->assertSee(__('site.nav.other_pages', [], 'it'), false)
            ->assertSee('Trasparenza', false)
            ->assertSee('Esempio landing', false);
    }

    public function test_standard_pages_are_not_listed_in_other_pages_dropdown(): void
    {
        $response = $this->get('/it/about-us');

        $response->assertOk();
        $this->assertStringNotContainsString(
            'href="/it/about-us"',
            $this->extractOtherPagesDropdown($this->get('/it')->getContent()),
        );
    }

    public function test_newly_created_page_appears_in_other_pages_dropdown(): void
    {
        Page::query()->create([
            'key' => 'volontari-info',
            'template' => 'default',
            'is_published' => true,
            'title' => [
                'it' => 'Diventa volontario',
                'en' => 'Become a volunteer',
            ],
            'slug' => [
                'it' => 'diventa-volontario',
                'en' => 'become-volunteer',
            ],
            'body' => [
                'it' => '<p>Informazioni per chi vuole unirsi al team.</p>',
            ],
        ]);

        $this->get('/it')
            ->assertOk()
            ->assertSee('Diventa volontario', false)
            ->assertSee('/it/become-a-volunteer', false);
    }

    public function test_other_pages_dropdown_is_hidden_when_no_extra_pages_exist(): void
    {
        Page::query()->whereNotIn('key', ['about', 'services', 'privacy', 'contact', 'cookie'])->delete();
        Page::query()->whereNull('key')->delete();

        $this->get('/it')
            ->assertOk()
            ->assertDontSee(__('site.nav.other_pages', [], 'it'), false);
    }

    private function extractOtherPagesDropdown(string $html): string
    {
        if (! preg_match('/Altre Pagine.*?<\/details>/s', $html, $matches)) {
            return '';
        }

        return $matches[0];
    }
}
