<?php

namespace App\DataTransferObjects;

readonly class HomeMealStatsSnapshot
{
  /** @var list<string> */
    public const MEAL_COUNT_METRICS = ['adults', 'minors', 'totalMeals', 'foodCost'];

  /** @var list<string> */
    public const NETWORK_METRICS = ['portionCount'];

    public function __construct(
        public HomeMealStatsPanel $mealCount,
        public HomeMealStatsPanel $network,
    ) {}

    /**
     * @param  array<string, mixed>  $mealSummary
     * @param  array<string, mixed>  $networkSummary
     */
    public static function fromApiResponses(array $mealSummary, array $networkSummary): self
    {
        return new self(
            mealCount: HomeMealStatsPanel::fromApiSummary($mealSummary, self::MEAL_COUNT_METRICS),
            network: HomeMealStatsPanel::fromApiSummary($networkSummary, self::NETWORK_METRICS),
        );
    }

    public static function empty(): self
    {
        return new self(
            mealCount: HomeMealStatsPanel::empty(self::MEAL_COUNT_METRICS),
            network: HomeMealStatsPanel::empty(self::NETWORK_METRICS),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'mealCount' => $this->mealCount->toArray(),
            'network' => $this->network->toArray(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            mealCount: HomeMealStatsPanel::fromArray(is_array($data['mealCount'] ?? null) ? $data['mealCount'] : []),
            network: HomeMealStatsPanel::fromArray(is_array($data['network'] ?? null) ? $data['network'] : []),
        );
    }

    public function formatPeriodRange(?string $from, ?string $to): string
    {
        if ($from === null || $from === '') {
            return '';
        }

        if ($to === null || $to === '' || $from === $to) {
            return $from;
        }

        return "{$from} – {$to}";
    }

    public function formatMetric(string $metricKey, int|float|null $value): string
    {
        if ($metricKey === 'foodCost') {
            $amount = is_float($value) || is_int($value) ? (float) $value : 0.0;

            return '€'.number_format($amount, 2, '.', ',');
        }

        if (! is_int($value)) {
            return '0';
        }

        return (string) $value;
    }

    public function metricLabel(string $metricKey): string
    {
        $key = "site.home.stats.metrics.{$metricKey}";

        return __($key) !== $key ? __($key) : $metricKey;
    }
}
