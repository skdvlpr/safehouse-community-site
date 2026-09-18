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
        // Thank-you path MUST be one-shot: record lookup + "usable BT" predicate, never the settled/retry alias.
        $mock->shouldReceive('retrievePaymentIntentRecord')
            ->once()
            ->with('pi_local_thank_you')
            ->andReturn($intent);
        $mock->shouldReceive('hasUsableBalanceTransaction')
            ->once()
            ->with($intent)
            ->andReturn(true);
        $mock->shouldNotReceive('retrieveSettledPaymentIntent');
        $mock->shouldNotReceive('retrievePaymentIntent');
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
            ->twice()
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

        $this->get('/it/donations/safe-house/thank-you?payment_intent=pi_local_thank_you&donor_name=Sem+Test')
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

        $campaign = DonationCampaign::factory()->create([
            'slug' => 'prod-fallback',
            'is_active' => true,
            'espocrm_finanziamento_name' => 'Test',
        ]);

        $intent = PaymentIntent::constructFrom([
            'id' => 'pi_prod_fallback',
            'object' => 'payment_intent',
            'status' => 'succeeded',
            'amount' => 500,
            'amount_received' => 500,
            'currency' => 'eur',
            'metadata' => [
                // campaign_id marks this as a site donation so the thank-you sync still ingests once BT is ready.
                'campaign_id' => (string) $campaign->id,
                'campaign_title' => 'Test',
                'donor_name' => 'Donor',
            ],
        ], null);

        $mock = Mockery::mock(StripePaymentService::class);
        $mock->shouldReceive('mockModeEnabled')->andReturn(false);
        $mock->shouldReceive('retrievePaymentIntentRecord')
            ->once()
            ->with('pi_prod_fallback')
            ->andReturn($intent);
        $mock->shouldReceive('hasUsableBalanceTransaction')
            ->once()
            ->with($intent)
            ->andReturn(true);
        $mock->shouldNotReceive('retrieveSettledPaymentIntent');
        $mock->shouldNotReceive('retrievePaymentIntent');
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
            ->twice()
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

    /**
     * US2: the thank-you page must never block on the Stripe fee. When the one-shot record lookup
     * returns a succeeded PaymentIntent whose BalanceTransaction is not published yet, the page
     * renders 200 and NO CRM write happens (the webhook pipeline will ingest on charge.updated).
     */
    public function test_thank_you_page_renders_without_crm_when_settlement_not_ready(): void
    {
        DonationCampaign::factory()->create([
            'slug' => 'safe-house-unsettled',
            'is_active' => true,
            'espocrm_finanziamento_name' => 'Donate to Safe House',
        ]);

        // Succeeded PI, but latest_charge.balance_transaction is still null (asynchronous capture).
        $intent = PaymentIntent::constructFrom([
            'id' => 'pi_unsettled',
            'object' => 'payment_intent',
            'status' => 'succeeded',
            'amount' => 100,
            'amount_received' => 100,
            'currency' => 'eur',
            'latest_charge' => [
                'id' => 'ch_unsettled',
                'object' => 'charge',
                'balance_transaction' => null,
            ],
            'metadata' => [
                'campaign_id' => '1',
                'campaign_title' => 'Donate to Safe House',
                'donor_name' => 'Sem Test',
            ],
        ], null);

        $mock = Mockery::mock(StripePaymentService::class);
        $mock->shouldReceive('mockModeEnabled')->andReturn(false);
        $mock->shouldReceive('retrievePaymentIntentRecord')
            ->once()
            ->with('pi_unsettled')
            ->andReturn($intent);
        $mock->shouldReceive('hasUsableBalanceTransaction')
            ->once()
            ->with($intent)
            ->andReturn(false);
        $mock->shouldNotReceive('retrieveSettledPaymentIntent');
        $mock->shouldNotReceive('retrievePaymentIntent');
        // No settlementFromPaymentIntent/ingest expectations on purpose: "no CRM write" is asserted via Http below
        // (a never() on a readonly-DTO return type would make Mockery fatal instead of failing cleanly).
        $this->instance(StripePaymentService::class, $mock);

        Http::fake();

        $this->get('/it/donations/safe-house-unsettled/thank-you?payment_intent=pi_unsettled&donor_name=Sem+Test')
            ->assertOk()
            ->assertSee('pi_unsettled');

        Http::assertNothingSent();
    }

    /**
     * US2: a succeeded PaymentIntent that is not a site donation (no campaign_id / stripe_subscription_id
     * metadata) must not be pushed to CRM from the thank-you page, but the page still renders.
     */
    public function test_thank_you_page_renders_without_crm_when_intent_has_no_site_donation_metadata(): void
    {
        DonationCampaign::factory()->create([
            'slug' => 'safe-house-foreign',
            'is_active' => true,
            'espocrm_finanziamento_name' => 'Donate to Safe House',
        ]);

        $intent = PaymentIntent::constructFrom([
            'id' => 'pi_no_site_metadata',
            'object' => 'payment_intent',
            'status' => 'succeeded',
            'amount' => 100,
            'amount_received' => 100,
            'currency' => 'eur',
            'latest_charge' => [
                'id' => 'ch_settled',
                'object' => 'charge',
                'balance_transaction' => [
                    'id' => 'txn_settled',
                    'object' => 'balance_transaction',
                    'amount' => 100,
                    'fee' => 3,
                    'net' => 97,
                    'currency' => 'eur',
                ],
            ],
            'metadata' => [],
        ], null);

        $mock = Mockery::mock(StripePaymentService::class);
        $mock->shouldReceive('mockModeEnabled')->andReturn(false);
        $mock->shouldReceive('retrievePaymentIntentRecord')
            ->once()
            ->with('pi_no_site_metadata')
            ->andReturn($intent);
        // BT is usable, but the metadata rule must stop the ingest before any CRM call.
        $mock->shouldReceive('hasUsableBalanceTransaction')
            ->zeroOrMoreTimes()
            ->with($intent)
            ->andReturn(true);
        $mock->shouldReceive('donationMetadataFromPaymentIntent')
            ->zeroOrMoreTimes()
            ->with($intent)
            ->andReturn([]);
        $mock->shouldNotReceive('retrieveSettledPaymentIntent');
        $mock->shouldNotReceive('retrievePaymentIntent');
        $this->instance(StripePaymentService::class, $mock);

        Http::fake();

        $this->get('/it/donations/safe-house-foreign/thank-you?payment_intent=pi_no_site_metadata')
            ->assertOk()
            ->assertSee('pi_no_site_metadata');

        Http::assertNothingSent();
    }
}
