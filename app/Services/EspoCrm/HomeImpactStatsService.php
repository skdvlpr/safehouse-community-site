<?php

namespace App\Services\EspoCrm;

use App\DataTransferObjects\HomeImpactStatsSnapshot;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class HomeImpactStatsService
{
    private const CACHE_KEY = 'home_impact_stats_snapshot_v1';

    public function __construct(
        private readonly EspoCrmClient $client,
    ) {}

    public function snapshot(): HomeImpactStatsSnapshot
    {
        /** @var array<string, mixed> $cached */
        $cached = Cache::remember(self::CACHE_KEY, now()->addMinutes(5), function (): array {
            try {
                return $this->loadFromCrm()->toArray();
            } catch (Throwable $exception) {
                Log::warning('Unable to load home impact stats from EspoCRM.', [
                    'reason' => $exception->getMessage(),
                ]);

                return HomeImpactStatsSnapshot::empty()->toArray();
            }
        });

        return HomeImpactStatsSnapshot::fromArray($cached);
    }

    public function forgetCache(): void
    {
        Cache::forget(self::CACHE_KEY);
        app(HomeMealStatsService::class)->forgetCache();
    }

    private function loadFromCrm(): HomeImpactStatsSnapshot
    {
        $mealTotals = $this->client->reportingTotals(
            (string) config('espocrm.reporting.meal_count_totals_path'),
        );
        $networkTotals = $this->client->reportingTotals(
            (string) config('espocrm.reporting.association_meal_count_totals_path'),
        );

        $mealCount = (int) ($mealTotals['totalMeals'] ?? 0);
        $networkCount = (int) ($networkTotals['portionCount'] ?? 0);

        $interventionResponse = $this->client->search('Intervention', [
            'maxSize' => 0,
            'select' => 'id',
        ]);

        $interventions = (int) ($interventionResponse['total'] ?? 0);

        return new HomeImpactStatsSnapshot(
            distributedMeals: $mealCount + $networkCount,
            interventions: $interventions,
        );
    }
}
