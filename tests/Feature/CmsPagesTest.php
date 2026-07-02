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
            ->assertSee('data-page-template="about"', false)
            ->assertSee('page-hero__title', false)
            ->assertSee('Comunità di accoglienza e solidarietà sul territorio', false)
            ->assertSee('page-section-band', false)
            ->assertSee('data-page-carousel', false)
            ->assertSee(__('site.pages.about_values_heading', [], 'it'), false)
            ->assertSee('disobbedienza civile', false)
            ->assertSee('casa sicura che si muove', false);
    }

    public function test_services_page_renders_numbered_cards(): void
    {
        $this->get('/it/servizi')
            ->assertOk()
            ->assertSee('data-page-template="services"', false)
            ->assertSee('01', false)
            ->assertSee('Aiuti umanitari e unità di strada', false);
    }

    public function test_contact_privacy_and_cookie_pages_are_reachable(): void
    {
        $this->get('/it/contatti')
            ->assertOk()
            ->assertSee('data-page-template="contact"', false)
            ->assertSee('id="contact-name"', false)
            ->assertSee('info@safehouse.community', false);
        $this->get('/it/privacy')->assertOk()->assertSee('data-page-template="legal"', false);
        $this->get('/it/cookie')->assertOk();
    }

    public function test_demo_templates_are_distinct(): void
    {
        $this->get('/it/esempio-landing')->assertOk()->assertSee('data-page-template="landing"', false);
        $this->get('/it/esempio-articolo')->assertOk()->assertSee('data-page-template="article"', false);
    }

    public function test_news_hub_page_is_removed(): void
    {
        $this->get('/it/hub-notizie')->assertNotFound();
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

    public function test_page_without_carousel_omits_gallery_markup(): void
    {
        $this->get('/it/privacy')
            ->assertOk()
            ->assertDontSee('data-page-carousel', false);
    }
}
