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
}
