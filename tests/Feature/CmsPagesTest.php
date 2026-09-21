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
        $this->get('/it/about-us')
            ->assertOk()
            ->assertSee('data-page-template="about"', false)
            ->assertSee('page-hero__title', false)
            ->assertSee('Comunità di accoglienza e solidarietà sul territorio', false)
            ->assertSee('data-page-carousel', false)
            ->assertSee(__('site.pages.about_values_heading', [], 'it'), false)
            ->assertSee('disobbedienza civile', false)
            ->assertSee('casa sicura che si muove', false);
    }

    public function test_services_page_renders_numbered_cards(): void
    {
        $this->get('/it/services')
            ->assertOk()
            ->assertSee('data-page-template="services"', false)
            ->assertSee('template-services-grid', false)
            ->assertSee('template-service-card--span-full', false)
            ->assertSee('01', false)
            ->assertSee('Aiuti umanitari e unità di strada', false);
    }

    public function test_contact_privacy_and_cookie_pages_are_reachable(): void
    {
        $this->get('/it/contact')
            ->assertOk()
            ->assertSee('data-page-template="contact"', false)
            ->assertSee('id="contact-name"', false)
            ->assertSee('info@safehouse.community', false)
            ->assertSee('Via Delleani 26, 00042 Anzio (RM)', false)
            ->assertSee(__('site.pages.contact_faq', [], 'it'), false)
            ->assertSee('safehouse-btn-primary', false)
            ->assertDontSee('https://safehouse.community/it/domande-frequenti-faq', false);
        $this->get('/it/privacy-policy')->assertOk()->assertSee('data-page-template="legal"', false);
        $this->get('/it/cookie-policy')->assertOk();
    }

    public function test_demo_landing_is_not_public(): void
    {
        $this->get('/it/landing-example')->assertNotFound();
    }

    public function test_article_example_template_is_distinct(): void
    {
        $this->get('/it/article-example')->assertOk()->assertSee('data-page-template="article"', false);
    }

    public function test_published_landing_template_renders(): void
    {
        Page::query()->where('key', 'demo-landing')->update(['is_published' => true]);

        $this->get('/it/landing-example')->assertOk()->assertSee('data-page-template="landing"', false);
    }

    public function test_news_hub_page_is_removed(): void
    {
        $this->get('/it/hub-notizie')->assertNotFound();
    }

    public function test_localized_about_slug_is_shared_across_locales(): void
    {
        $this->get('/en/about-us')->assertOk()->assertSee('About us', false);
        $this->get('/it/about-us')->assertOk()->assertSee('Chi siamo', false);
    }

    public function test_news_index_route_works(): void
    {
        $this->get('/it/news')
            ->assertOk()
            ->assertSee(__('site.pages.news_title', [], 'it'), false)
            ->assertSee(__('site.pages.news_empty', [], 'it'), false);
    }

    public function test_unpublished_cms_page_returns_not_found(): void
    {
        Page::query()->where('key', 'about')->update(['is_published' => false]);

        $this->get('/it/about-us')->assertNotFound();
    }

    public function test_donations_route_is_not_handled_by_cms_catch_all(): void
    {
        $this->get('/it/donations')->assertOk();
    }

    public function test_page_without_carousel_omits_gallery_markup(): void
    {
        $this->get('/it/privacy-policy')
            ->assertOk()
            ->assertDontSee('data-page-carousel', false);
    }

    public function test_privacy_policy_covers_controller_and_google_apis(): void
    {
        $this->get('/en/privacy-policy')
            ->assertOk()
            ->assertSee('Safe House ETS', false)
            ->assertSee('96629270586', false)
            ->assertSee('Via Delleani 26, 00042 Anzio (RM)', false)
            ->assertSee('156768', false)
            ->assertSee('id="google-api-services"', false)
            ->assertSee('drive.file', false)
            ->assertSee('Google Calendar', false)
            ->assertSee('staff area', false)
            ->assertSee('Aruba Cloud', false)
            ->assertSee('Google Workspace', false)
            ->assertSee('Turnstile', false)
            ->assertSee('Card data does not pass through our servers', false)
            ->assertDontSee('crm.safehouse.community', false)
            ->assertDontSee('EspoCRM', false)
            ->assertDontSee('contact_success', false)
            ->assertDontSee('Aruba mail', false)
            ->assertDontSee('email Aruba', false)
            ->assertDontSee('Data Processing Agreement', false)
            ->assertDontSee('da approvare', false);

        $this->get('/it/privacy-policy')
            ->assertOk()
            ->assertSee('Aruba Cloud', false)
            ->assertSee('Google Workspace', false)
            ->assertSee('Via Delleani 26, 00042 Anzio (RM)', false)
            ->assertSee('Turnstile', false)
            ->assertSee('Non comunichiamo a terzi', false)
            ->assertDontSee('servizi email Aruba', false)
            ->assertDontSee('in questa versione', false)
            ->assertDontSee('contact_success', false)
            ->assertDontSee('EspoCRM', false);

        $this->get('/it/cookie-policy')
            ->assertOk()
            ->assertSee('sh_cookie_consent', false)
            ->assertSee('safe-house-community-session', false)
            ->assertSee('Non in uso', false)
            ->assertSee('Turnstile', false)
            ->assertSee('Via Delleani 26, 00042 Anzio (RM)', false)
            ->assertSee('proposti già selezionati', false)
            ->assertSee(__('site.measurement.status_off', [], 'it'), false)
            ->assertDontSee('Laravel', false)
            ->assertDontSee('crm.safehouse.community', false)
            ->assertDontSee('contact_success', false);
    }

    public function test_cookie_and_privacy_copy_name_ga4_when_measurement_is_bootable(): void
    {
        config([
            'measurement.enabled' => true,
            'measurement.container_id' => 'GTM-TEST1',
        ]);

        $this->get('/it/cookie-policy')
            ->assertOk()
            ->assertSee('Google Analytics 4', false)
            ->assertSee('Google Tag Manager', false)
            ->assertSee(__('site.measurement.status_on', [], 'it'), false)
            ->assertDontSee(__('site.measurement.status_off', [], 'it'), false)
            ->assertDontSee('non sono attivi cookie analitici', false)
            ->assertDontSee('EspoCRM', false)
            ->assertDontSee('crm.safehouse.community', false)
            ->assertDontSee('Data Processing Agreement', false)
            ->assertDontSee('Data Processing Addendum', false);

        $this->get('/en/privacy-policy')
            ->assertOk()
            ->assertSee('Google Analytics 4', false)
            ->assertSee('Google Tag Manager', false)
            ->assertSee('Google Workspace', false)
            ->assertSee(__('site.measurement.status_on', [], 'en'), false)
            ->assertDontSee('not currently active', false)
            ->assertDontSee('EspoCRM', false)
            ->assertDontSee('crm.safehouse.community', false)
            ->assertDontSee('Data Processing Agreement', false)
            ->assertDontSee('contact_success', false)
            ->assertSee('Card data does not pass through our servers', false);
    }
}
