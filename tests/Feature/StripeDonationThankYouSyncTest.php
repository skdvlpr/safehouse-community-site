<?php

namespace Tests\Feature;

use App\DataTransferObjects\StripeEnrichmentFields;
use App\DataTransferObjects\StripeSettlementAmounts;
use App\Models\DonationCampaign;
use App\Services\Donations\StripeDonationThankYouSync;
use App\Services\Payments\StripePaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Mockery;
use Stripe\PaymentIntent;
use Tests\TestCase;

class StripeDonationThankYouSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('espocrm.base_url', 'https://crm.test');
        config()->set('espocrm.api_key', 'test-espo-key');
        config()->set('espocrm.finanziamento.default_close_date', '2026-12-31');
        config()->set('espocrm.prima_nota.default_beneficiary_name', 'Safe House');
        config()->set('stripe.secret', 'sk_test_local');
        config()->set('stripe.mock', false);
    }

    public function test_thank_you_page_syncs_succeeded_payment_intent_to_crm(): void
    {
        $campaign = DonationCampaign::factory()->create([
            'slug' => 'safe-house',
            'is_active' => true,
            'espocrm_finanziamento_name' => 'Donate to Safe House',
        ]);

        $intent = PaymentIntent::constructFrom([
            'id' => 'pi_local_thank_you',
            'object' => 'payment_intent',
            'status' => 'succeeded',
            'amount' => 100,
            'amount_received' => 100,
            'currency' => 'eur',
            'metadata' => [
                'campaign_id' => (string) $campaign->id,
                'campaign_title' => 'Donate to Safe House',
                'donor_name' => 'Sem Test',
                'donor_type' => 'individual',
                'donor_phone' => '+393331112222',
            ],
        ], null);

        $mock = Mockery::mock(StripePaymentService::class);
        $mock->shouldReceive('mockModeEnabled')->andReturn(false);
        $mock->shouldReceive('retrievePaymentIntent')
            ->once()
            ->with('pi_local_thank_you')
            ->andReturn($intent);
        $mock->shouldReceive('settlementFromPaymentIntent')
            ->once()
            ->with($intent)
            ->andReturn(StripeSettlementAmounts::fromCents([
                'gross_cents' => 100,
                'fee_cents' => 0,
                'net_cents' => 100,
                'currency' => 'eur',
            ]));
        $mock->shouldReceive('donationMetadataFromPaymentIntent')
            ->once()
            ->with($intent)
            ->andReturn($intent->metadata->toArray());
        $mock->shouldReceive('enrichmentFromPaymentIntent')
            ->once()
            ->with($intent)
            ->andReturn(StripeEnrichmentFields::fromMockStoredIntent([
                'created' => time(),
                'payment_method_type' => 'card',
                'card_brand' => 'visa',
                'card_last4' => '4242',
            ]));
        $this->instance(StripePaymentService::class, $mock);

        Http::fake(function ($request) {
            $url = $request->url();
            $method = $request->method();

            if ($method === 'GET' && str_contains($url, '/api/v1/PrimaNota')) {
                return Http::response(['total' => 0, 'list' => []]);
            }

            if ($method === 'GET' && str_contains($url, '/api/v1/Opportunity')) {
                return Http::response([
                    'total' => 1,
                    'list' => [['id' => 'opp-local', 'name' => 'Donate to Safe House']],
                ]);
            }

            if ($method === 'GET' && str_contains($url, '/api/v1/Contact')) {
                return Http::response(['total' => 0, 'list' => []]);
            }

            if ($method === 'GET' && str_contains($url, '/api/v1/Account')) {
                return Http::response([
                    'total' => 1,
                    'list' => [['id' => 'acc-safe-house', 'name' => 'Safe House']],
                ]);
            }

            if ($method === 'GET' && str_contains($url, '/api/v1/User/')) {
                $id = trim((string) basename(parse_url($url, PHP_URL_PATH) ?: ''));

                return Http::response(['id' => $id !== '' ? $id : 'api-user-id', 'userName' => 'api']);
            }

            if ($method === 'GET' && str_contains($url, '/api/v1/App/user')) {
                return Http::response(['user' => ['id' => 'api-user-id', 'userName' => 'api']]);
            }
            if ($method === 'POST' && str_contains($url, '/api/v1/PrimaNota') && ! str_contains($url, '/action/')) {
                return Http::response(['id' => 'pn-local-thank-you', 'financingId' => 'opp-local']);
            }

            return Http::response(['message' => 'Unexpected'], 500);
        });

        $this->get('/it/donazioni/safe-house/grazie?payment_intent=pi_local_thank_you&donor_name=Sem+Test')
            ->assertOk()
            ->assertSee('pi_local_thank_you');

        Http::assertSent(function ($request): bool {
            return $request->method() === 'POST'
                && str_contains($request->url(), '/api/v1/PrimaNota')
                && ! str_contains($request->url(), '/action/')
                && ($request->data()['donationPaymentReference'] ?? '') === '#pi_local_thank_you';
        });
    }

    public function test_sync_service_runs_in_production_environment(): void
    {
        app()->detectEnvironment(fn (): string => 'production');

        $intent = PaymentIntent::constructFrom([
            'id' => 'pi_prod_fallback',
            'object' => 'payment_intent',
            'status' => 'succeeded',
            'amount' => 500,
            'amount_received' => 500,
            'currency' => 'eur',
            'metadata' => [
                'campaign_title' => 'Test',
                'donor_name' => 'Donor',
            ],
        ], null);

        $mock = Mockery::mock(StripePaymentService::class);
        $mock->shouldReceive('mockModeEnabled')->andReturn(false);
        $mock->shouldReceive('retrievePaymentIntent')
            ->once()
            ->with('pi_prod_fallback')
            ->andReturn($intent);
        $mock->shouldReceive('settlementFromPaymentIntent')
            ->once()
            ->with($intent)
            ->andReturn(StripeSettlementAmounts::fromCents([
                'gross_cents' => 500,
                'fee_cents' => 0,
                'net_cents' => 500,
                'currency' => 'eur',
            ]));
        $mock->shouldReceive('donationMetadataFromPaymentIntent')
            ->once()
            ->with($intent)
            ->andReturn($intent->metadata->toArray());
        $mock->shouldReceive('enrichmentFromPaymentIntent')
            ->once()
            ->with($intent)
            ->andReturn(StripeEnrichmentFields::fromMockStoredIntent([
                'created' => time(),
                'payment_method_type' => 'card',
            ]));
        $this->instance(StripePaymentService::class, $mock);

        Http::fake(function ($request) {
            if ($request->method() === 'GET' && str_contains($request->url(), '/api/v1/PrimaNota')) {
                return Http::response(['total' => 0, 'list' => []]);
            }

            if ($request->method() === 'GET' && str_contains($request->url(), '/api/v1/Opportunity')) {
                return Http::response(['total' => 0, 'list' => []]);
            }

            if ($request->method() === 'POST' && str_contains($request->url(), '/api/v1/Opportunity')) {
                return Http::response(['id' => 'opp-new']);
            }

            if ($request->method() === 'GET' && str_contains($request->url(), '/api/v1/Contact')) {
                return Http::response(['total' => 0, 'list' => []]);
            }

            if ($request->method() === 'GET' && str_contains($request->url(), '/api/v1/Account')) {
                return Http::response(['total' => 0, 'list' => []]);
            }

            if ($request->method() === 'GET' && str_contains($request->url(), '/api/v1/User/')) {
                $id = trim((string) basename(parse_url($request->url(), PHP_URL_PATH) ?: ''));

                return Http::response(['id' => $id !== '' ? $id : 'api-user-id', 'userName' => 'api']);
            }

            if ($request->method() === 'GET' && str_contains($request->url(), '/api/v1/App/user')) {
                return Http::response(['user' => ['id' => 'api-user-id', 'userName' => 'api']]);
            }

            if ($request->method() === 'POST' && str_contains($request->url(), '/api/v1/PrimaNota') && ! str_contains($request->url(), '/action/')) {
                return Http::response(['id' => 'pn-prod']);
            }

            return Http::response(['message' => 'Unexpected'], 500);
        });

        app(StripeDonationThankYouSync::class)->ingestSucceededPaymentIntent('pi_prod_fallback');

        Http::assertSent(fn ($request): bool => $request->method() === 'POST'
            && str_contains($request->url(), '/api/v1/PrimaNota')
                && ! str_contains($request->url(), '/action/'));
    }
}
