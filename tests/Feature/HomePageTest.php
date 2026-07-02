<?php

namespace Tests\Feature;

use App\Services\EspoCrm\EspoCrmClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_renders_hero_and_meal_stats_shell(): void
    {
        $this->seed(\Database\Seeders\PageSeeder::class);
        $this->seed(\Database\Seeders\DeploySiteContentSeeder::class);

        $this->get('/it')
            ->assertOk()
            ->assertSee('Safe House Community', false)
            ->assertSee('Comunità di accoglienza e solidarietà.', false)
            ->assertSee('Il nostro impatto', false)
            ->assertSee('Conteggio pasti', false)
            ->assertSee('Conteggio pasti per Rete', false)
            ->assertSee('Totale Pasti', false)
            ->assertSee('Anno', false)
            ->assertDontSee('Adulti', false)
            ->assertDontSee('Costo Totale Cibo', false)
            ->assertSee('favicon.svg', false)
            ->assertSee('apple-touch-icon.png', false);
    }

    public function test_home_meal_stats_use_crm_summary(): void
    {
        $this->seed(\Database\Seeders\PageSeeder::class);

        $client = $this->createMock(EspoCrmClient::class);
        $client->method('reportingSummary')
            ->willReturnCallback(function (string $path): array {
                if (str_contains($path, 'association-meal-count')) {
                    return [
                        'metricList' => ['portionCount'],
                        'year' => ['from' => '2026-01-01', 'to' => '2026-12-31', 'portionCount' => 2194],
                        'month' => ['from' => '2026-07-01', 'to' => '2026-07-31', 'portionCount' => 0],
                        'today' => ['from' => '2026-07-02', 'to' => '2026-07-02', 'portionCount' => 0],
                    ];
                }

                return [
                    'metricList' => ['adults', 'minors', 'totalMeals', 'foodCost'],
                    'year' => [
                        'from' => '2026-01-01',
                        'to' => '2026-12-31',
                        'adults' => 955,
                        'minors' => 0,
                        'totalMeals' => 955,
                        'foodCost' => 1432.5,
                    ],
                    'month' => ['from' => '2026-07-01', 'to' => '2026-07-31', 'adults' => 0, 'minors' => 0, 'totalMeals' => 0, 'foodCost' => 0],
                    'today' => ['from' => '2026-07-02', 'to' => '2026-07-02', 'adults' => 0, 'minors' => 0, 'totalMeals' => 0, 'foodCost' => 0],
                ];
            });

        $this->app->instance(EspoCrmClient::class, $client);
        Cache::flush();

        $this->get('/it')
            ->assertOk()
            ->assertSee('955', false)
            ->assertSee('2.194', false)
            ->assertSee('Totale Pasti', false)
            ->assertSee('N° pasti', false)
            ->assertSee('2026-01-01 – 2026-12-31', false)
            ->assertDontSee('Adulti', false)
            ->assertDontSee('€1,432.50', false);
    }

    public function test_home_meal_stats_fall_back_when_crm_unavailable(): void
    {
        $this->seed(\Database\Seeders\PageSeeder::class);

        $client = $this->createMock(EspoCrmClient::class);
        $client->method('reportingSummary')
            ->willThrowException(new \RuntimeException('CRM unavailable'));

        $this->app->instance(EspoCrmClient::class, $client);
        Cache::flush();

        $this->get('/it')
            ->assertOk()
            ->assertSee('Conteggio pasti', false)
            ->assertSee('Totale Pasti', false)
            ->assertSee('0', false);
    }
}
