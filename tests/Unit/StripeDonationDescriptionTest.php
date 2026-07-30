<?php

namespace Tests\Unit;

use App\Models\DonationCampaign;
use App\Services\Payments\StripePaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StripeDonationDescriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_donation_description_includes_campaign_and_donor(): void
    {
        $campaign = DonationCampaign::factory()->create([
            'espocrm_finanziamento_name' => 'Raccolta Safe House 2026',
        ]);

        $service = new StripePaymentService(null);
        $description = $service->donationDescription($campaign, 'Mario Rossi', 'OneTime');

        $this->assertStringContainsString('una tantum', $description);
        $this->assertStringContainsString('Raccolta Safe House 2026', $description);
        $this->assertStringContainsString('Mario Rossi', $description);
    }

    public function test_prima_nota_crm_url_uses_configured_base(): void
    {
        config()->set('espocrm.base_url', 'https://crm.safehouse.community');

        $this->assertSame(
            'https://crm.safehouse.community/#PrimaNota/view/abc123',
            StripePaymentService::primaNotaCrmUrl('abc123'),
        );
    }

    public function test_prima_nota_crm_url_works_for_ddev_base(): void
    {
        config()->set('espocrm.base_url', 'https://nonprofit-espocrm.ddev.site/');

        $this->assertSame(
            'https://nonprofit-espocrm.ddev.site/#PrimaNota/view/pn-1',
            StripePaymentService::primaNotaCrmUrl('pn-1'),
        );
    }
}
