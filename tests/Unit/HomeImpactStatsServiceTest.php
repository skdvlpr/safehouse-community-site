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

    public function test_intervention_total_uses_reporting_record_count(): void
    {
        $client = $this->createMock(EspoCrmClient::class);
        $client->method('reportingTotals')
            ->willReturnCallback(function (string $path): array {
                if (str_contains($path, 'association-meal-count')) {
                    return ['portionCount' => 100];
                }

                if (str_contains($path, 'intervention')) {
                    return ['recordCount' => 3456];
                }

                return ['totalMeals' => 200];
            });

        $this->app->instance(EspoCrmClient::class, $client);
        Cache::flush();

        $snapshot = app(HomeImpactStatsService::class)->snapshot();

        $this->assertSame(300, $snapshot->distributedMeals);
        $this->assertSame(3456, $snapshot->interventions);
    }
}
