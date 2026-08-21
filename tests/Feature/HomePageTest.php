<?php

namespace Tests\Feature;

use App\Services\EspoCrm\EspoCrmClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_renders_hero_impact_cards_and_manifesto(): void
    {
        $this->seed(\Database\Seeders\PageSeeder::class);
        $this->seed(\Database\Seeders\DeploySiteContentSeeder::class);

        $this->get('/it')
            ->assertOk()
            ->assertSee('Safe House Community', false)
            ->assertSee('NESSUN ESSERE UMANO È ILLEGALE', false)
            ->assertSee('Matteo Grossi', false)
            ->assertSee('theprojectsafehouse@gmail.com', false)
            ->assertSee('images/logo.png', false)
            ->assertDontSee('Dati aggiornati — collegamento al CRM in arrivo', false)
            ->assertSee('Pasti distribuiti', false)
            ->assertSee('Interventi sul territorio', false)
            ->assertSee('I nostri partner', false)
            ->assertSee(__('site.home.cta_contact', [], 'it'), false)
            ->assertSee('/it/contatti', false)
            ->assertSee('favicon.svg', false)
            ->assertSee('data-display-prefs', false)
            ->assertSee('data-theme-option="light"', false)
            ->assertSee('data-theme-option="dark"', false)
            ->assertSee('data-theme-option="system"', false)
            ->assertSee('safehouse.theme', false)
            ->assertSee('>IT</', false)
            ->assertSee('>RU</', false)
            ->assertSee('>EN</', false);
    }

    public function test_home_impact_stats_use_crm_totals(): void
    {
        $this->seed(\Database\Seeders\PageSeeder::class);

        $client = $this->createMock(EspoCrmClient::class);
        $client->method('reportingTotals')
            ->willReturnCallback(function (string $path): array {
                if (str_contains($path, 'association-meal-count')) {
                    return ['metricList' => ['portionCount'], 'portionCount' => 2194];
                }

                if (str_contains($path, 'intervention')) {
                    return ['metricList' => ['recordCount'], 'recordCount' => 42];
                }

                return ['metricList' => ['totalMeals'], 'totalMeals' => 955];
            });

        $this->app->instance(EspoCrmClient::class, $client);
        Cache::flush();

        $this->get('/it')
            ->assertOk()
            ->assertSee('3.149', false)
            ->assertSee('42', false)
            ->assertSee('—', false);
    }

    public function test_home_impact_stats_fall_back_when_crm_unavailable(): void
    {
        $this->seed(\Database\Seeders\PageSeeder::class);

        $client = $this->createMock(EspoCrmClient::class);
        $client->method('reportingTotals')
            ->willThrowException(new \RuntimeException('CRM unavailable'));
        $client->method('search')
            ->willThrowException(new \RuntimeException('CRM unavailable'));

        $this->app->instance(EspoCrmClient::class, $client);
        Cache::flush();

        $this->get('/it')
            ->assertOk()
            ->assertSee('Pasti distribuiti', false)
            ->assertSee('—', false);
    }
}
