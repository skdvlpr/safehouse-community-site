<?php

namespace Tests\Feature;

use App\Models\DonationCampaign;
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

        $this->get('/it/donazioni')
            ->assertOk()
            ->assertSee('Raccolta attiva')
            ->assertDontSee('Raccolta nascosta');
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
