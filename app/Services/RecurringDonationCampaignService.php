<?php

namespace App\Services;

use App\Models\DonationCampaign;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

/**
 * Singleton recurring donation campaign (CMS "Donazioni ricorrenti").
 *
 * Reuses DonationCampaign storage with allows_recurring=true; never has a fundraising goal.
 * Goal / one-time campaigns stay in DonationCampaignResource and are filtered via scopeOneTime().
 */
class RecurringDonationCampaignService
{
    public function slug(): string
    {
        return (string) config('donations.recurring_campaign_slug', 'recurring-donation');
    }

    public function getOrCreate(): DonationCampaign
    {
        /** @var DonationCampaign $campaign */
        $campaign = DonationCampaign::query()->firstOrCreate(
            ['slug' => $this->slug()],
            $this->defaultAttributes(),
        );

        if (! $campaign->allows_recurring || $campaign->fundraising_goal_cents !== null) {
            $campaign->forceFill([
                'allows_recurring' => true,
                'fundraising_goal_cents' => null,
            ])->save();
        }

        return $campaign->refresh();
    }

    public function campaign(): DonationCampaign
    {
        return $this->getOrCreate();
    }

    public function isEnabled(): bool
    {
        return $this->campaign()->is_active;
    }

    /**
     * Active recurring campaign for the public site, or null when disabled.
     */
    public function activeCampaign(): ?DonationCampaign
    {
        $campaign = $this->campaign();

        return $campaign->is_active ? $campaign : null;
    }

    /**
     * @return array{enabled: bool, title: array<string, string>, description: array<string, string>}
     */
    public function formValues(): array
    {
        $campaign = $this->campaign();
        $locales = config('locales.available', ['it', 'ru', 'en']);

        $title = [];
        $description = [];

        foreach ($locales as $locale) {
            $title[$locale] = (string) ($campaign->getTranslation('title', $locale, false) ?: '');
            $description[$locale] = (string) ($campaign->getTranslation('description', $locale, false) ?: '');
        }

        return [
            'enabled' => (bool) $campaign->is_active,
            'title' => $title,
            'description' => $description,
        ];
    }

    /**
     * @param  array<string, mixed>  $state
     */
    public function saveFromFormState(array $state): DonationCampaign
    {
        $campaign = $this->campaign();
        $locales = config('locales.available', ['it', 'ru', 'en']);

        $title = [];
        $description = [];

        foreach ($locales as $locale) {
            $titleValue = trim((string) data_get($state, "title.{$locale}", ''));
            if ($titleValue !== '') {
                $title[$locale] = $titleValue;
            }

            $descriptionValue = (string) data_get($state, "description.{$locale}", '');
            $sanitized = $this->sanitizer()->sanitize($descriptionValue);
            if (trim(strip_tags($sanitized)) !== '') {
                $description[$locale] = $sanitized;
            }
        }

        if ($title === []) {
            $title = ['it' => 'Donazione ricorrente'];
        }

        $campaign->fill([
            'title' => $title,
            'description' => $description,
            'is_active' => (bool) ($state['enabled'] ?? false),
            'allows_recurring' => true,
            'fundraising_goal_cents' => null,
        ]);
        $campaign->save();

        return $campaign->refresh();
    }

    /**
     * @return array<string, mixed>
     */
    private function defaultAttributes(): array
    {
        return [
            'title' => [
                'it' => 'Donazione ricorrente',
                'en' => 'Recurring donation',
                'ru' => 'Регулярное пожертвование',
            ],
            'description' => [
                'it' => '<p>Sostieni Safe House ogni mese con un contributo ricorrente. Puoi interrompere in qualsiasi momento tramite il portale Stripe dedicato ai donatori.</p>',
                'en' => '<p>Support Safe House every month with a recurring gift. You can cancel anytime via the Stripe donor portal.</p>',
                'ru' => '<p>Поддерживайте Safe House ежемесячно. Отменить можно в любой момент через портал Stripe для доноров.</p>',
            ],
            'form_notice' => [
                'it' => 'Questa campagna accetta donazioni ricorrenti. Prima di confermare ti spiegheremo come disdire.',
                'en' => 'This campaign accepts recurring donations. Before confirming we explain how to cancel.',
                'ru' => 'Эта кампания принимает регулярные пожертвования. Перед подтверждением мы объясним, как отписаться.',
            ],
            'privacy_notice' => [
                'it' => '<p>Trattiamo i dati solo per gestire la donazione ricorrente e gli obblighi di legge. I pagamenti sono gestiti da Stripe.</p>',
                'en' => '<p>We process data only to manage the recurring donation and legal obligations. Payments are handled by Stripe.</p>',
                'ru' => '<p>Мы обрабатываем данные только для регулярного пожертвования и юридических обязательств. Платежи обрабатывает Stripe.</p>',
            ],
            'thank_you_message' => [
                'it' => 'Grazie per il tuo sostegno ricorrente. Puoi gestire o annullare l\'abbonamento in qualsiasi momento dal link del portale donatori che ti invieremo.',
                'en' => 'Thank you for your recurring support. You can manage or cancel anytime from the donor portal link we will send you.',
                'ru' => 'Спасибо за регулярную поддержку. Управлять или отменить подписку можно в любой момент по ссылке портала донора.',
            ],
            'preset_amounts' => [1000, 2000, 5000, 10000],
            'allow_custom_amount' => true,
            'allows_recurring' => true,
            'min_amount_cents' => 500,
            'currency' => 'EUR',
            'fundraising_goal_cents' => null,
            'espocrm_finanziamento_name' => 'Donazione ricorrente',
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    private function sanitizer(): HtmlSanitizer
    {
        $config = (new HtmlSanitizerConfig)
            ->allowSafeElements()
            ->allowRelativeLinks()
            ->allowRelativeMedias();

        return new HtmlSanitizer($config);
    }
}
