<?php

namespace App\DataTransferObjects;

readonly class FundraisingProgress
{
    public function __construct(
        public float $collected,
        public float $target,
        public int $percent,
        public string $currency,
    ) {}

    /**
     * @return array{collected: float, target: float, percent: int, currency: string}
     */
    public function toArray(): array
    {
        return [
            'collected' => $this->collected,
            'target' => $this->target,
            'percent' => $this->percent,
            'currency' => $this->currency,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            collected: (float) ($data['collected'] ?? 0),
            target: (float) ($data['target'] ?? 0),
            percent: (int) ($data['percent'] ?? 0),
            currency: strtoupper((string) ($data['currency'] ?? 'EUR')),
        );
    }

    public function hasTarget(): bool
    {
        return $this->target > 0;
    }

    public function formatMoney(float $amount): string
    {
        $decimals = fmod($amount, 1.0) === 0.0 ? 0 : 2;
        $formatted = number_format($amount, $decimals, ',', '.');
        $currency = strtoupper($this->currency);

        return $currency === 'EUR' ? $formatted.' €' : $formatted.' '.$currency;
    }
}
