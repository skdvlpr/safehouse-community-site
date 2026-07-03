<?php

namespace App\DataTransferObjects;

readonly class HomeImpactStatsSnapshot
{
    public function __construct(
        public ?int $distributedMeals,
        public ?int $interventions,
        public string $partnersDisplay = '—',
    ) {}

    public static function empty(): self
    {
        return new self(null, null);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'distributedMeals' => $this->distributedMeals,
            'interventions' => $this->interventions,
            'partnersDisplay' => $this->partnersDisplay,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            isset($data['distributedMeals']) ? (int) $data['distributedMeals'] : null,
            isset($data['interventions']) ? (int) $data['interventions'] : null,
            (string) ($data['partnersDisplay'] ?? '—'),
        );
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public function cards(string $locale): array
    {
        return [
            [
                'value' => $this->formatCount($this->distributedMeals),
                'label' => (string) __('site.home.stats.distributed_meals', [], $locale),
            ],
            [
                'value' => $this->formatCount($this->interventions),
                'label' => (string) __('site.home.stats.interventions', [], $locale),
            ],
            [
                'value' => $this->partnersDisplay,
                'label' => (string) __('site.home.stats.partners', [], $locale),
            ],
        ];
    }

    private function formatCount(?int $value): string
    {
        if ($value === null) {
            return '—';
        }

        return number_format($value, 0, ',', '.');
    }
}
