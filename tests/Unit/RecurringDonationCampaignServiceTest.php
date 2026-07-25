<?php

namespace Tests\Unit;

use App\Models\DonationCampaign;
use App\Services\RecurringDonationCampaignService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecurringDonationCampaignServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_singleton_without_fundraising_goal(): void
    {
        $service = app(RecurringDonationCampaignService::class);
        $campaign = $service->getOrCreate();

        $this->assertTrue($campaign->allowsRecurring());
        $this->assertFalse($campaign->hasFundraisingGoal());
        $this->assertNull($campaign->fundraising_goal_cents);
        $this->assertSame('donazione-ricorrente', $campaign->slug);
        $this->assertTrue($service->isEnabled());
    }

    public function test_save_from_form_updates_title_description_and_toggle(): void
    {
        $service = app(RecurringDonationCampaignService::class);

        $service->saveFromFormState([
            'enabled' => false,
            'title' => [
                'it' => 'Sostegno mensile',
                'en' => 'Monthly support',
            ],
            'description' => [
                'it' => '<p>Descrizione IT</p>',
                'en' => '<p>EN description</p>',
            ],
        ]);

        $campaign = $service->campaign();

        $this->assertFalse($campaign->is_active);
        $this->assertNull($service->activeCampaign());
        $this->assertSame('Sostegno mensile', $campaign->getTranslation('title', 'it'));
        $this->assertTrue($campaign->allowsRecurring());
        $this->assertNull($campaign->fundraising_goal_cents);

        $this->assertSame(1, DonationCampaign::query()->recurring()->count());
        $this->assertSame(0, DonationCampaign::query()->oneTime()->count());
    }

    public function test_clears_stale_goal_on_existing_row(): void
    {
        DonationCampaign::factory()->create([
            'slug' => 'donazione-ricorrente',
            'allows_recurring' => true,
            'fundraising_goal_cents' => 50_000,
        ]);

        $campaign = app(RecurringDonationCampaignService::class)->getOrCreate();

        $this->assertNull($campaign->fundraising_goal_cents);
        $this->assertFalse($campaign->hasFundraisingGoal());
    }
}
