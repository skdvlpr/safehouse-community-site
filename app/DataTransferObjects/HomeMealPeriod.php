<?php

namespace App\DataTransferObjects;

readonly class HomeMealPeriod
{
    /**
     * @param  array<string, int|float|null>  $values
     */
    public function __construct(
        public ?string $from,
        public ?string $to,
        public array $values = [],
    ) {}

    public function value(string $key): int|float|null
    {
        $value = $this->values[$key] ?? null;

        return is_int($value) || is_float($value) ? $value : null;
    }

    /**
     * @return array<string, int|float|null>
     */
    public function toArray(): array
    {
        return [
            'from' => $this->from,
            'to' => $this->to,
            'values' => $this->values,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $values = $data['values'] ?? [];

        if (! is_array($values)) {
            $values = [];
        }

        $normalized = [];

        foreach ($values as $key => $value) {
            if (! is_string($key)) {
                continue;
            }

            if (is_int($value) || is_float($value)) {
                $normalized[$key] = $value;
            }
        }

        return new self(
            from: is_string($data['from'] ?? null) ? $data['from'] : null,
            to: is_string($data['to'] ?? null) ? $data['to'] : null,
            values: $normalized,
        );
    }

    /**
     * @param  list<string>  $metricKeys
     */
    public static function fromApiPeriod(mixed $period, array $metricKeys): self
    {
        if (! is_array($period) && ! is_object($period)) {
            return new self(from: null, to: null);
        }

        /** @var array<string, mixed> $data */
        $data = (array) $period;
        $values = [];

        foreach ($metricKeys as $key) {
            if (! array_key_exists($key, $data)) {
                continue;
            }

            $raw = $data[$key];

            if ($key === 'foodCost' && is_numeric($raw)) {
                $values[$key] = (float) $raw;

                continue;
            }

            if (is_numeric($raw)) {
                $values[$key] = (int) $raw;
            }
        }

        return new self(
            from: is_string($data['from'] ?? null) ? $data['from'] : null,
            to: is_string($data['to'] ?? null) ? $data['to'] : null,
            values: $values,
        );
    }
}
