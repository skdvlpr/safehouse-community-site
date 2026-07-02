<?php

namespace Tests\Unit;

use App\Models\DonationCampaign;
use Tests\TestCase;

class DonationCampaignTest extends TestCase
{
    public function test_format_preset_label_shows_whole_euros_without_decimals(): void
    {
        $campaign = new DonationCampaign(['currency' => 'EUR']);

        $this->assertSame('10 €', $campaign->formatPresetLabel(1000));
    }

    public function test_format_preset_label_shows_cents_when_needed(): void
    {
        $campaign = new DonationCampaign(['currency' => 'EUR']);

        $this->assertSame('12,50 €', $campaign->formatPresetLabel(1250));
    }

    public function test_fundraising_goal_amount_converts_cents_to_euros(): void
    {
        $campaign = DonationCampaign::factory()->create([
            'fundraising_goal_cents' => 70000,
        ]);

        $this->assertSame(700.0, $campaign->fundraisingGoalAmount());
    }

    public function test_preset_amount_cents_deduplicates_and_filters_below_minimum(): void
    {
        $campaign = new DonationCampaign([
            'preset_amounts' => [1000, 1000, 20, 500],
            'min_amount_cents' => 50,
        ]);

        $this->assertSame([500, 1000], $campaign->presetAmountCents());
    }

    public function test_parse_euro_tag_to_cents(): void
    {
        $this->assertSame(1250, DonationCampaign::parseEuroTagToCents('12,50'));
        $this->assertSame(1000, DonationCampaign::parseEuroTagToCents('10'));
    }

    public function test_thank_you_body_uses_campaign_translation_when_present(): void
    {
        $campaign = new DonationCampaign([
            'thank_you_message' => [
                'it' => 'Messaggio personalizzato.',
                'en' => 'Custom message.',
            ],
        ]);

        $this->assertSame('Custom message.', $campaign->thankYouBody('en'));
    }

    public function test_thank_you_body_falls_back_to_site_default(): void
    {
        $campaign = new DonationCampaign([
            'thank_you_message' => [],
        ]);

        app()->setLocale('it');

        $this->assertSame(
            (string) __('site.donations.thank_you_body'),
            $campaign->thankYouBody('it'),
        );
    }

    public function test_thank_you_heading_uses_donor_first_name(): void
    {
        $campaign = new DonationCampaign;

        app()->setLocale('it');

        $this->assertSame(
            (string) __('site.donations.thank_you_named', ['name' => 'Mario']),
            $campaign->thankYouHeading('Mario Rossi'),
        );
    }
}
