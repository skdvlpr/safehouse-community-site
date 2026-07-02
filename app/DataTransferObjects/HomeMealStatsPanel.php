<?php

namespace App\DataTransferObjects;

readonly class HomeMealStatsPanel
{
    /**
     * @param  list<string>  $metricList
     */
    public function __construct(
        public array $metricList,
        public HomeMealPeriod $year,
        public HomeMealPeriod $month,
        public HomeMealPeriod $today,
    ) {}

    public function isAvailable(): bool
    {
        return $this->metricList !== [];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'metricList' => $this->metricList,
            'year' => $this->year->toArray(),
            'month' => $this->month->toArray(),
            'today' => $this->today->toArray(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $metricList = $data['metricList'] ?? [];

        if (! is_array($metricList)) {
            $metricList = [];
        }

        return new self(
            metricList: array_values(array_filter(array_map(
                static fn (mixed $item): string => is_string($item) ? $item : '',
                $metricList,
            ), static fn (string $item): bool => $item !== '')),
            year: HomeMealPeriod::fromArray(is_array($data['year'] ?? null) ? $data['year'] : []),
            month: HomeMealPeriod::fromArray(is_array($data['month'] ?? null) ? $data['month'] : []),
            today: HomeMealPeriod::fromArray(is_array($data['today'] ?? null) ? $data['today'] : []),
        );
    }

    /**
     * @param  array<string, mixed>  $summary
     * @param  list<string>  $defaultMetrics
     */
    public static function fromApiSummary(array $summary, array $defaultMetrics): self
    {
        $metricList = self::stringList($summary['metricList'] ?? []);

        if ($metricList === []) {
            $metricList = $defaultMetrics;
        }

        return new self(
            metricList: $metricList,
            year: HomeMealPeriod::fromApiPeriod($summary['year'] ?? null, $metricList),
            month: HomeMealPeriod::fromApiPeriod($summary['month'] ?? null, $metricList),
            today: HomeMealPeriod::fromApiPeriod($summary['today'] ?? null, $metricList),
        );
    }

    /**
     * @param  list<string>  $metrics
     */
    public static function empty(array $metrics): self
    {
        return new self(
            metricList: $metrics,
            year: new HomeMealPeriod(from: null, to: null),
            month: new HomeMealPeriod(from: null, to: null),
            today: new HomeMealPeriod(from: null, to: null),
        );
    }

    /**
     * @return list<string>
     */
    private static function stringList(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return array_values(array_filter(array_map(
            static fn (mixed $item): string => is_string($item) ? $item : '',
            $value,
        ), static fn (string $item): bool => $item !== ''));
    }
}
