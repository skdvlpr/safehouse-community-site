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
        'preset_amounts',
        'allow_custom_amount',
        'min_amount_cents',
        'currency',
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

    /**
     * @return list<int>
     */
    public function presetAmountCents(): array
    {
        $amounts = $this->preset_amounts ?? [];

        return array_values(array_filter(array_map('intval', $amounts)));
    }
}
