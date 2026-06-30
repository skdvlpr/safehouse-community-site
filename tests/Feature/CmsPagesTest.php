<?php

namespace Tests\Feature;

use App\Models\Page;
use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CmsPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PageSeeder::class);
    }

    public function test_about_page_uses_about_template(): void
    {
        $this->get('/it/chi-siamo')
            ->assertOk()
            ->assertSee('Chi siamo', false)
            ->assertSee(__('site.pages.about_values_heading', [], 'it'), false)
            ->assertSee('disobbedienza civile', false);
    }

    public function test_services_page_renders_service_cards(): void
    {
        $this->get('/it/servizi')
            ->assertOk()
            ->assertSee('Aiuti umanitari e unità di strada', false)
            ->assertSee('Sportello digitale', false)
            ->assertSee('1.000+ pasti caldi', false);
    }

    public function test_contact_privacy_and_cookie_pages_are_reachable(): void
    {
        $this->get('/it/contatti')->assertOk()->assertSee('info@safehouse.community', false);
        $this->get('/it/privacy')->assertOk();
        $this->get('/it/cookie')->assertOk();
    }

    public function test_localized_about_slugs(): void
    {
        $this->get('/en/about-us')->assertOk()->assertSee('About us', false);
        $this->get('/ru/o-nas')->assertOk()->assertSee('О нас', false);
    }

    public function test_news_index_route_works(): void
    {
        $this->get('/it/notizie')
            ->assertOk()
            ->assertSee(__('site.pages.news_title', [], 'it'), false)
            ->assertSee(__('site.pages.news_empty', [], 'it'), false);
    }

    public function test_unpublished_cms_page_returns_not_found(): void
    {
        Page::query()->where('key', 'about')->update(['is_published' => false]);

        $this->get('/it/chi-siamo')->assertNotFound();
    }

    public function test_donations_route_is_not_handled_by_cms_catch_all(): void
    {
        $this->get('/it/donazioni')->assertOk();
    }
}
