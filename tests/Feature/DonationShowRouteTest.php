<?php

namespace Tests\Feature;

use App\Models\DonationCampaign;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DonationShowRouteTest extends TestCase
{
    use RefreshDatabase;

    public function test_show_active_campaign(): void
    {
        DonationCampaign::factory()->create([
            'slug' => 'show-me',
            'is_active' => true,
        ]);

        $this->get('/it/donazioni/show-me')->assertOk();
    }

    public function test_thank_you_page_shows_personalized_message_and_reference(): void
    {
        DonationCampaign::factory()->create([
            'slug' => 'grazie-test',
            'is_active' => true,
        ]);

        $this->get('/it/donazioni/grazie-test/grazie?payment_intent=pi_test_123&donor_name=Mario%20Rossi')
            ->assertOk()
            ->assertSee('Grazie, Mario!', false)
            ->assertSee('pi_test_123', false)
            ->assertDontSee('EspoCRM', false)
            ->assertDontSee('Prima nota', false);
    }
}
