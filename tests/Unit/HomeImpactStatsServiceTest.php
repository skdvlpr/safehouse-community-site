<?php

namespace Tests\Unit;

use App\Services\EspoCrm\EspoCrmClient;
use App\Services\EspoCrm\HomeImpactStatsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class HomeImpactStatsServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_intervention_total_uses_sum_of_intervention_count_field(): void
    {
        $client = $this->createMock(EspoCrmClient::class);
        $client->method('reportingTotals')
            ->willReturnCallback(function (string $path): array {
                if (str_contains($path, 'association-meal-count')) {
                    return ['portionCount' => 100];
                }

                if (str_contains($path, 'intervention')) {
                    return ['interventionCount' => 26, 'recordCount' => 3];
                }

                return ['totalMeals' => 200];
            });

        $this->app->instance(EspoCrmClient::class, $client);
        Cache::flush();

        $snapshot = app(HomeImpactStatsService::class)->snapshot();

        $this->assertSame(300, $snapshot->distributedMeals);
        $this->assertSame(26, $snapshot->interventions);
    }

    public function test_meal_totals_still_load_when_intervention_reporting_is_unavailable(): void
    {
        $client = $this->createMock(EspoCrmClient::class);
        $client->method('reportingTotals')
            ->willReturnCallback(function (string $path): array {
                if (str_contains($path, 'intervention')) {
                    throw new \RuntimeException('Route not found');
                }

                if (str_contains($path, 'association-meal-count')) {
                    return ['portionCount' => 100];
                }

                return ['totalMeals' => 200];
            });
        $client->method('search')
            ->willReturnCallback(function (string $entity, array $query): array {
                if ($entity !== 'Intervention') {
                    return ['total' => 0, 'list' => []];
                }

                return [
                    'total' => 3,
                    'list' => [
                        ['interventionCount' => 2],
                        ['interventionCount' => 4],
                        ['interventionCount' => 20],
                    ],
                ];
            });

        $this->app->instance(EspoCrmClient::class, $client);
        Cache::flush();

        $snapshot = app(HomeImpactStatsService::class)->snapshot();

        $this->assertSame(300, $snapshot->distributedMeals);
        $this->assertSame(26, $snapshot->interventions);
    }
}
