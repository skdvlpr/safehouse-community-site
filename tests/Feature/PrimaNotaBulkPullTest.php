<?php

namespace Tests\Feature;

use App\Models\DonationCampaign;
use App\Services\Donations\DonationIngestPayloadMapper;
use App\Services\Payments\MockStripePaymentService;
use App\Services\Payments\StripePaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PrimaNotaBulkPullTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('espocrm.base_url', 'https://crm.test');
        config()->set('espocrm.api_key', 'test-espo-key');
        config()->set('espocrm.assigned_user_id', 'api-user-id');
        config()->set('espocrm.finanziamento.default_close_date', '2026-12-31');
        config()->set('espocrm.prima_nota.default_subject_name', 'Donatore');
        config()->set('espocrm.prima_nota.default_beneficiary_name', 'Safe House');
        config()->set('stripe.mock', true);
        config()->set('crm.sync_token', 'test-sync-token');

        $this->app->instance(StripePaymentService::class, MockStripePaymentService::make());
    }

    public function test_bulk_pull_requires_sync_token(): void
    {
        $response = $this->postJson('/api/internal/prima-nota/bulk-pull', [
            'providers' => ['Stripe'],
            'mode' => 'all',
        ]);

        $response->assertUnauthorized();
    }

    public function test_bulk_pull_requires_providers(): void
    {
        $response = $this->withHeader('X-Safehouse-Sync-Token', 'test-sync-token')
            ->postJson('/api/internal/prima-nota/bulk-pull', [
                'providers' => [],
                'mode' => 'all',
            ]);

        $response->assertStatus(422);
    }

    public function test_bulk_pull_from_date_requires_date(): void
    {
        $response = $this->withHeader('X-Safehouse-Sync-Token', 'test-sync-token')
            ->postJson('/api/internal/prima-nota/bulk-pull', [
                'providers' => ['Stripe'],
                'mode' => 'from_date',
            ]);

        $response->assertStatus(422);
    }

    public function test_bulk_pull_ingests_succeeded_mock_intents(): void
    {
        $mock = MockStripePaymentService::make();
        $this->app->instance(StripePaymentService::class, $mock);

        $campaign = DonationCampaign::factory()->create([
            'espocrm_finanziamento_name' => 'Raccolta fondi per Safe House',
        ]);

        $created = $mock->createDonationIntent(
            $campaign,
            2500,
            'Bulk Donor',
            'individual',
            'Bulk test',
            'bulk@example.com'
        );

        $mapper = app(DonationIngestPayloadMapper::class);
        $mock->completeIntent($created['payment_intent_id'], $mapper);

        Http::fake(function ($request) {
            $method = $request->method();
            $url = $request->url();

            if ($method === 'GET' && str_contains($url, '/api/v1/PrimaNota') && str_contains($url, 'where')) {
                return Http::response(['total' => 0, 'list' => []], 200);
            }

            if ($method === 'GET' && str_contains($url, '/api/v1/Opportunity')) {
                return Http::response([
                    'total' => 1,
                    'list' => [['id' => 'opp-bulk', 'name' => 'Raccolta fondi per Safe House']],
                ], 200);
            }

            if ($method === 'GET' && (str_contains($url, '/api/v1/Contact') || str_contains($url, '/api/v1/Account'))) {
                return Http::response(['total' => 0, 'list' => []], 200);
            }

            if ($method === 'POST' && str_contains($url, '/api/v1/Contact')) {
                return Http::response(['id' => 'c-bulk'], 200);
            }

            if ($method === 'POST' && str_contains($url, '/api/v1/Account')) {
                return Http::response(['id' => 'a-bulk'], 200);
            }

            if ($method === 'GET' && str_contains($url, '/api/v1/User/')) {
                $id = trim((string) basename(parse_url($url, PHP_URL_PATH) ?: ''));

                return Http::response(['id' => $id !== '' ? $id : 'api-user-id', 'userName' => 'api']);
            }

            if ($method === 'GET' && str_contains($url, '/api/v1/App/user')) {
                return Http::response(['user' => ['id' => 'api-user-id', 'userName' => 'api']]);
            }
            if ($method === 'POST' && str_contains($url, '/api/v1/PrimaNota') && ! str_contains($url, '/action/')) {
                return Http::response(['id' => 'pn-bulk', 'financingId' => 'opp-bulk'], 200);
            }

            if ($method === 'PUT' && str_contains($url, '/api/v1/PrimaNota/')) {
                return Http::response(['id' => 'pn-bulk'], 200);
            }

            return Http::response(['message' => 'Unexpected '.$method.' '.$url], 500);
        });

        $response = $this->withHeader('X-Safehouse-Sync-Token', 'test-sync-token')
            ->postJson('/api/internal/prima-nota/bulk-pull', [
                'providers' => ['Stripe'],
                'mode' => 'all',
                'maxItems' => 50,
            ]);

        $response->assertOk()
            ->assertJsonPath('created', 1)
            ->assertJsonPath('failed', 0)
            ->assertJsonPath('providers.0', 'Stripe')
            ->assertJsonStructure(['log']);

        $this->assertNotEmpty($response->json('log'));
        $this->assertTrue(
            collect($response->json('log'))->contains(fn ($line) => str_contains((string) $line, 'DONE')),
            'Bulk pull log should include DONE step'
        );
    }

    public function test_bulk_pull_marks_reserved_providers_unsupported(): void
    {
        $response = $this->withHeader('X-Safehouse-Sync-Token', 'test-sync-token')
            ->postJson('/api/internal/prima-nota/bulk-pull', [
                'providers' => ['Stripe', 'Satispay'],
                'mode' => 'all',
                'maxItems' => 10,
            ]);

        $response->assertOk()
            ->assertJsonPath('unsupportedProviders.0', 'Satispay');
    }

    public function test_bulk_pull_skips_unsupported_currency_without_failing(): void
    {
        $mock = MockStripePaymentService::make();
        $this->app->instance(StripePaymentService::class, $mock);

        $campaign = DonationCampaign::factory()->create([
            'espocrm_finanziamento_name' => 'USD campaign',
            'currency' => 'USD',
        ]);

        $created = $mock->createDonationIntent(
            $campaign,
            1000,
            'Usd Donor',
            'individual',
            'usd test',
            'usd@example.com'
        );

        $mapper = app(DonationIngestPayloadMapper::class);
        $mock->completeIntent($created['payment_intent_id'], $mapper);

        Http::fake(function ($request) {
            return Http::response(['message' => 'CRM should not be called for USD'], 500);
        });

        $response = $this->withHeader('X-Safehouse-Sync-Token', 'test-sync-token')
            ->postJson('/api/internal/prima-nota/bulk-pull', [
                'providers' => ['Stripe'],
                'currencies' => ['EUR', 'USD'],
                'mode' => 'all',
                'maxItems' => 50,
            ]);

        $response->assertOk()
            ->assertJsonPath('created', 0)
            ->assertJsonPath('failed', 0)
            ->assertJsonPath('skipped', 1)
            ->assertJsonPath('skippedCurrencies.0', 'USD');
    }

    public function test_bulk_pull_skips_unselected_currency_before_crm(): void
    {
        $mock = MockStripePaymentService::make();
        $this->app->instance(StripePaymentService::class, $mock);

        $campaign = DonationCampaign::factory()->create([
            'espocrm_finanziamento_name' => 'USD campaign filter',
            'currency' => 'USD',
        ]);

        $created = $mock->createDonationIntent(
            $campaign,
            1000,
            'Usd Donor',
            'individual',
            'usd filter',
            'usd-filter@example.com'
        );

        $mapper = app(DonationIngestPayloadMapper::class);
        $mock->completeIntent($created['payment_intent_id'], $mapper);

        Http::fake(function ($request) {
            $method = $request->method();
            $url = $request->url();

            // Status sync after pull always loads Planned Stripe rows (even if ingest skipped all).
            if ($method === 'GET' && str_contains($url, '/api/v1/PrimaNota')) {
                return Http::response(['total' => 0, 'list' => []], 200);
            }

            return Http::response(['message' => 'CRM should not be called for unselected USD ingest'], 500);
        });

        $response = $this->withHeader('X-Safehouse-Sync-Token', 'test-sync-token')
            ->postJson('/api/internal/prima-nota/bulk-pull', [
                'providers' => ['Stripe'],
                'currencies' => ['EUR'],
                'mode' => 'all',
                'maxItems' => 50,
            ]);

        $response->assertOk()
            ->assertJsonPath('created', 0)
            ->assertJsonPath('failed', 0)
            ->assertJsonPath('skipped', 1)
            ->assertJsonPath('skippedCurrencies.0', 'USD');

        Http::assertSent(function ($request) {
            return $request->method() === 'GET' && str_contains($request->url(), '/api/v1/PrimaNota');
        });

        Http::assertNotSent(function ($request) {
            return in_array($request->method(), ['POST', 'PUT'], true);
        });
    }
}
