<?php

namespace App\Models;

use Database\Factories\DonationCampaignFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class DonationCampaign extends Model
{
    /** @use HasFactory<DonationCampaignFactory> */
    use HasFactory, HasTranslations;

    /**
     * @var list<string>
     */
    public array $translatable = [
        'title',
        'description',
        'privacy_notice',
        'form_notice',
        'thank_you_message',
    ];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'slug',
        'title',
        'description',
        'privacy_notice',
        'form_notice',
        'thank_you_message',
        'preset_amounts',
        'allow_custom_amount',
        'allows_recurring',
        'min_amount_cents',
        'currency',
        'fundraising_goal_cents',
        'espocrm_finanziamento_name',
        'is_active',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'preset_amounts' => 'array',
            'allow_custom_amount' => 'boolean',
            'allows_recurring' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function finanziamentoTitle(?string $locale = null): string
    {
        if ($this->espocrm_finanziamento_name) {
            return $this->espocrm_finanziamento_name;
        }

        $locale ??= app()->getLocale();

        return (string) ($this->getTranslation('title', $locale, false)
            ?: $this->getTranslation('title', 'it', false)
            ?: $this->slug);
    }

    public function fundraisingGoalAmount(): ?float
    {
        $cents = $this->fundraising_goal_cents;

        if ($cents === null || (int) $cents <= 0) {
            return null;
        }

        return ((int) $cents) / 100;
    }

    public function allowsRecurring(): bool
    {
        return (bool) $this->allows_recurring;
    }

    public function hasFundraisingGoal(): bool
    {
        return $this->fundraisingGoalAmount() !== null;
    }

    /**
     * @return list<int>
     */
    public function presetAmountCents(): array
    {
        $amounts = $this->preset_amounts ?? [];
        $min = (int) $this->min_amount_cents;

        $cents = array_values(array_unique(array_filter(
            array_map('intval', is_array($amounts) ? $amounts : []),
            static fn (int $value): bool => $value > 0,
        )));

        sort($cents);

        return array_values(array_filter(
            $cents,
            static fn (int $value): bool => $value >= $min,
        ));
    }

    public function formatPresetLabel(int $cents): string
    {
        $amount = $cents / 100;
        $decimals = fmod($amount, 1.0) === 0.0 ? 0 : 2;
        $formatted = number_format($amount, $decimals, ',', '.');
        $currency = strtoupper((string) $this->currency);

        return $currency === 'EUR' ? $formatted.' €' : $formatted.' '.$currency;
    }

    public function thankYouBody(?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        $message = trim((string) ($this->getTranslation('thank_you_message', $locale, false)
            ?: $this->getTranslation('thank_you_message', 'it', false)
            ?: ''));

        if ($message !== '') {
            return $message;
        }

        return (string) __('site.donations.thank_you_body');
    }

    public function thankYouHeading(string $donorName = ''): string
    {
        $firstName = trim(explode(' ', trim($donorName))[0] ?? '');

        if ($firstName !== '') {
            return (string) __('site.donations.thank_you_named', ['name' => $firstName]);
        }

        return (string) __('site.donations.thank_you_generic');
    }

    public static function parseEuroTagToCents(string $value): int
    {
        $normalized = str_replace([' ', '€'], '', trim($value));
        $normalized = str_replace(',', '.', $normalized);

        if ($normalized === '' || ! is_numeric($normalized)) {
            return 0;
        }

        return (int) round((float) $normalized * 100);
    }

    public static function formatEuroTag(int $cents): string
    {
        $amount = $cents / 100;

        if (fmod($amount, 1.0) === 0.0) {
            return (string) (int) $amount;
        }

        return str_replace('.', ',', number_format($amount, 2, '.', ''));
    }
}
