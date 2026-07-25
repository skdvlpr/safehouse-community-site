<?php

namespace Tests\Feature;

use App\Models\DonationCampaign;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Mockery;
use Tests\TestCase;

class MockDonationCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('stripe.secret', '');
        config()->set('stripe.mock', true);
        config()->set('espocrm.base_url', 'https://espocrm.test');
        config()->set('espocrm.api_key', 'test-key');
        config()->set('espocrm.assigned_user_id', 'user-1');
        config()->set('espocrm.prima_nota.entity', 'PrimaNota');
        config()->set('espocrm.finanziamento.entity', 'Finanziamento');
    }

    public function test_mock_intent_and_complete_flow(): void
    {
        $campaign = DonationCampaign::factory()->create([
            'slug' => 'mock-campaign',
            'title' => ['it' => 'Raccolta mock'],
            'allow_custom_amount' => true,
            'min_amount_cents' => 100,
        ]);

        Http::fake([
            'espocrm.test/api/v1/PrimaNota*' => Http::sequence()
                ->push(['total' => 0, 'list' => []])
                ->push(['id' => 'pn-mock-1']),
            'espocrm.test/api/v1/Finanziamento*' => Http::response([
                'total' => 1,
                'list' => [['id' => 'fin-1', 'name' => $campaign->finanziamentoTitle()]],
            ]),
            'espocrm.test/api/v1/Contact*' => Http::response(['total' => 0, 'list' => []]),
            'espocrm.test/api/v1/Account*' => Http::response([
                'total' => 1,
                'list' => [['id' => 'acc-safehouse', 'name' => 'Safe House']],
            ]),
        ]);

        $intentResponse = $this->postJson('/api/donations/intents/mock-campaign', [
            'amount_cents' => 5000,
            'donor_name' => 'Mock Donor',
            'donor_type' => 'individual',
            'donor_email' => 'mock.donor@example.com',
            'comment' => 'Local mock payment',
        ])->assertOk();

        $intentResponse->assertJsonPath('mock', true);
        $paymentIntentId = $intentResponse->json('payment_intent_id');
        $this->assertStringStartsWith('pi_mock_', $paymentIntentId);

        $completeUrl = $intentResponse->json('complete_url');
        $this->assertNotNull($completeUrl);

        $this->postJson(parse_url($completeUrl, PHP_URL_PATH))
            ->assertOk()
            ->assertJsonPath('status', 'created')
            ->assertJsonPath('prima_nota_id', 'pn-mock-1');

        $this->postJson(parse_url($completeUrl, PHP_URL_PATH))
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Mock payment intent already completed.');
    }

    public function test_mock_complete_returns_404_when_mock_disabled(): void
    {
        config()->set('stripe.mock', false);
        config()->set('stripe.secret', 'sk_test_real');

        $this->postJson('/api/donations/mock/pi_mock_test/complete')->assertNotFound();
    }

    public function test_donation_page_shows_mock_banner_in_mock_mode(): void
    {
        config(['app.debug' => true]);

        DonationCampaign::factory()->create([
            'slug' => 'banner-campaign',
            'title' => ['it' => 'Raccolta banner'],
            'is_active' => true,
        ]);

        $this->get(route('donations.show', ['locale' => 'it', 'campaignSlug' => 'banner-campaign']))
            ->assertOk()
            ->assertSee(__('site.donations.dev_simulation', [], 'it'), false);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
