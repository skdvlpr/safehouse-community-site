<?php

namespace Tests\Feature;

use App\DataTransferObjects\FundraisingProgress;
use App\Models\DonationCampaign;
use App\Services\Donations\CampaignFundraisingProgressService;
use App\Services\Donations\StripeDonationThankYouSync;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DonationCampaignRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_donations_index_lists_active_campaigns(): void
    {
        DonationCampaign::factory()->create([
            'slug' => 'active-one',
            'title' => ['it' => 'Raccolta attiva'],
            'is_active' => true,
        ]);
        DonationCampaign::factory()->create([
            'slug' => 'hidden-one',
            'title' => ['it' => 'Raccolta nascosta'],
            'is_active' => false,
        ]);
        DonationCampaign::factory()->recurring()->create([
            'slug' => 'recurring-donation',
            'title' => ['it' => 'Donazione ricorrente'],
            'is_active' => true,
        ]);

        $this->get('/it/donations')
            ->assertOk()
            ->assertSee('Raccolta attiva')
            ->assertDontSee('Raccolta nascosta')
            ->assertSee('Donazione ricorrente')
            ->assertSee(__('site.donations.recurring_cta'));
    }

    public function test_donations_index_hides_disabled_recurring_campaign(): void
    {
        DonationCampaign::factory()->recurring()->create([
            'slug' => 'recurring-donation',
            'title' => ['it' => 'Donazione ricorrente nascosta'],
            'is_active' => false,
        ]);

        $this->get('/it/donations')
            ->assertOk()
            ->assertDontSee('Donazione ricorrente nascosta');
    }

    public function test_donations_index_shows_fundraising_progress_bar(): void
    {
        DonationCampaign::factory()->create([
            'slug' => 'with-progress',
            'title' => ['it' => 'Raccolta con progresso'],
            'is_active' => true,
        ]);

        $this->mock(CampaignFundraisingProgressService::class, function ($mock): void {
            $mock->shouldReceive('forCampaigns')
                ->once()
                ->andReturn([
                    'with-progress' => new FundraisingProgress(575, 700, 82, 'EUR'),
                ]);
        });

        $this->get('/it/donations')
            ->assertOk()
            ->assertSee('575 €')
            ->assertSee('700 €')
            ->assertSee('82%');
    }

    public function test_thank_you_page_uses_campaign_message(): void
    {
        $campaign = DonationCampaign::factory()->create([
            'slug' => 'custom-thanks',
            'title' => ['it' => 'Raccolta test'],
            'thank_you_message' => [
                'it' => 'Grazie di cuore per aver sostenuto questa raccolta.',
            ],
            'is_active' => true,
        ]);

        $this->mock(StripeDonationThankYouSync::class, function ($mock): void {
            $mock->shouldReceive('ingestSucceededPaymentIntent')
                ->once()
                ->with('pi_test_123');
        });

        $this->get('/it/donations/'.$campaign->slug.'/thank-you?payment_intent=pi_test_123&donor_name=Mario%20Rossi')
            ->assertOk()
            ->assertSee('Grazie di cuore per aver sostenuto questa raccolta.')
            ->assertSee('Grazie, Mario!');
    }

    public function test_campaign_resolves_by_slug(): void
    {
        $campaign = DonationCampaign::factory()->create([
            'slug' => 'slug-resolve',
            'is_active' => true,
        ]);

        $found = DonationCampaign::query()->where('slug', 'slug-resolve')->first();

        $this->assertNotNull($found);
        $this->assertTrue($found->is($campaign));
    }
}
