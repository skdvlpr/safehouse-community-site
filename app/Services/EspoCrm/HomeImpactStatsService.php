<?php

namespace App\Services\EspoCrm;

use App\DataTransferObjects\HomeImpactStatsSnapshot;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class HomeImpactStatsService
{
    private const CACHE_KEY = 'home_impact_stats_snapshot_v4';

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
        return new HomeImpactStatsSnapshot(
            distributedMeals: $this->loadDistributedMeals(),
            interventions: $this->loadInterventionCount(),
        );
    }

    private function loadDistributedMeals(): ?int
    {
        try {
            $mealTotals = $this->client->reportingTotals(
                (string) config('espocrm.reporting.meal_count_totals_path'),
            );
            $networkTotals = $this->client->reportingTotals(
                (string) config('espocrm.reporting.association_meal_count_totals_path'),
            );

            $mealCount = (int) ($mealTotals['totalMeals'] ?? 0);
            $networkCount = (int) ($networkTotals['portionCount'] ?? 0);

            return $mealCount + $networkCount;
        } catch (Throwable $exception) {
            Log::warning('Unable to load home meal totals from EspoCRM.', [
                'reason' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    private function loadInterventionCount(): ?int
    {
        try {
            $interventionTotals = $this->client->reportingTotals(
                (string) config('espocrm.reporting.intervention_totals_path'),
            );

            return self::toCount(
                $interventionTotals['interventionCount']
                    ?? $interventionTotals['recordCount']
                    ?? null,
            );
        } catch (Throwable $exception) {
            Log::warning('Intervention reporting totals unavailable; falling back to CRM search.', [
                'reason' => $exception->getMessage(),
            ]);
        }

        try {
            return $this->sumInterventionCountsFromSearch();
        } catch (Throwable $exception) {
            Log::warning('Unable to load home intervention count from EspoCRM.', [
                'reason' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    private function sumInterventionCountsFromSearch(): int
    {
        $total = 0;
        $offset = 0;
        $pageSize = 200;
        $recordTotal = 0;

        do {
            $interventionResponse = $this->client->search('Intervention', [
                'maxSize' => $pageSize,
                'offset' => $offset,
                'select' => 'interventionCount',
            ]);

            $recordTotal = (int) ($interventionResponse['total'] ?? 0);

            foreach ($interventionResponse['list'] ?? [] as $row) {
                if (! is_array($row)) {
                    continue;
                }

                $count = $row['interventionCount'] ?? 1;
                $total += max(0, (int) $count);
            }

            $offset += $pageSize;
        } while ($offset < $recordTotal);

        return $total;
    }

    private static function toCount(mixed $value): int
    {
        if (is_int($value)) {
            return max(0, $value);
        }

        if (is_float($value)) {
            return max(0, (int) round($value));
        }

        if (! is_string($value)) {
            return 0;
        }

        $normalized = preg_replace('/[^\d-]/', '', $value) ?? '';

        if ($normalized === '' || $normalized === '-') {
            return 0;
        }

        return max(0, (int) $normalized);
    }
}
