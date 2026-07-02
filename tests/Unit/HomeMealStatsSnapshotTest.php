<?php

namespace Tests\Unit;

use App\DataTransferObjects\HomeMealStatsSnapshot;
use PHPUnit\Framework\TestCase;

class HomeMealStatsSnapshotTest extends TestCase
{
    public function test_from_api_responses_maps_crm_period_metrics(): void
    {
        $snapshot = HomeMealStatsSnapshot::fromApiResponses(
            [
                'metricList' => ['adults', 'minors', 'totalMeals', 'foodCost'],
                'year' => [
                    'from' => '2026-01-01',
                    'to' => '2026-12-31',
                    'adults' => 955,
                    'minors' => 0,
                    'totalMeals' => 955,
                    'foodCost' => 1432.5,
                ],
                'month' => [
                    'from' => '2026-07-01',
                    'to' => '2026-07-31',
                    'adults' => 0,
                    'minors' => 0,
                    'totalMeals' => 0,
                    'foodCost' => 0,
                ],
                'today' => [
                    'from' => '2026-07-02',
                    'to' => '2026-07-02',
                    'adults' => 0,
                    'minors' => 0,
                    'totalMeals' => 0,
                    'foodCost' => 0,
                ],
            ],
            [
                'metricList' => ['portionCount'],
                'year' => ['from' => '2026-01-01', 'to' => '2026-12-31', 'portionCount' => 2194],
                'month' => ['from' => '2026-07-01', 'to' => '2026-07-31', 'portionCount' => 0],
                'today' => ['from' => '2026-07-02', 'to' => '2026-07-02', 'portionCount' => 0],
            ],
        );

        $this->assertSame(955, $snapshot->mealCount->year->value('adults'));
        $this->assertSame('955', $snapshot->formatMetric('adults', 955));
        $this->assertSame('€1,432.50', $snapshot->formatMetric('foodCost', 1432.5));
        $this->assertSame('2026-01-01 – 2026-12-31', $snapshot->formatPeriodRange('2026-01-01', '2026-12-31'));
        $this->assertSame('2026-07-02', $snapshot->formatPeriodRange('2026-07-02', '2026-07-02'));
        $this->assertSame(2194, $snapshot->network->year->value('portionCount'));
        $this->assertTrue($snapshot->mealCount->isAvailable());
    }

    public function test_array_round_trip_for_cache(): void
    {
        $snapshot = HomeMealStatsSnapshot::fromApiResponses(
            [
                'metricList' => ['totalMeals'],
                'year' => ['from' => '2026-01-01', 'to' => '2026-12-31', 'totalMeals' => 10],
                'month' => ['from' => '2026-07-01', 'to' => '2026-07-31', 'totalMeals' => 2],
                'today' => ['from' => '2026-07-02', 'to' => '2026-07-02', 'totalMeals' => 1],
            ],
            [
                'metricList' => ['portionCount'],
                'year' => ['from' => '2026-01-01', 'to' => '2026-12-31', 'portionCount' => 20],
                'month' => ['from' => '2026-07-01', 'to' => '2026-07-31', 'portionCount' => 3],
                'today' => ['from' => '2026-07-02', 'to' => '2026-07-02', 'portionCount' => 1],
            ],
        );

        $restored = HomeMealStatsSnapshot::fromArray($snapshot->toArray());

        $this->assertSame(10, $restored->mealCount->year->value('totalMeals'));
        $this->assertSame(20, $restored->network->year->value('portionCount'));
    }
}
