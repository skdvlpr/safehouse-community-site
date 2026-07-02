<?php

namespace App\Services\EspoCrm;

use App\DataTransferObjects\HomeMealStatsSnapshot;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class HomeMealStatsService
{
    private const CACHE_KEY = 'home_meal_stats_snapshot_v2';

    public function __construct(
        private readonly EspoCrmClient $client,
    ) {}

    public function snapshot(): HomeMealStatsSnapshot
    {
        /** @var array<string, mixed> $cached */
        $cached = Cache::remember(self::CACHE_KEY, now()->addMinutes(5), function (): array {
            try {
                $mealSummary = $this->client->reportingSummary(
                    (string) config('espocrm.reporting.meal_count_summary_path'),
                );
                $networkSummary = $this->client->reportingSummary(
                    (string) config('espocrm.reporting.association_meal_count_summary_path'),
                );

                return HomeMealStatsSnapshot::fromApiResponses($mealSummary, $networkSummary)->toArray();
            } catch (Throwable $exception) {
                Log::warning('Unable to load home meal stats from EspoCRM.', [
                    'reason' => $exception->getMessage(),
                ]);

                return HomeMealStatsSnapshot::empty()->toArray();
            }
        });

        return HomeMealStatsSnapshot::fromArray($cached);
    }

    public function forgetCache(): void
    {
        Cache::forget(self::CACHE_KEY);
        Cache::forget('home_meal_stats_snapshot');
    }
}
