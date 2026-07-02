<?php

namespace Tests\Feature;

use App\Models\DonationCampaign;
use App\Services\Payments\StripePaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Mockery;
use Mockery\MockInterface;
use RuntimeException;
use Stripe\Event;
use Stripe\PaymentIntent;
use Tests\TestCase;

class StripeWebhookDonationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('espocrm.base_url', 'https://crm.test');
        config()->set('espocrm.api_key', 'test-espo-key');
        config()->set('espocrm.finanziamento.default_close_date', '2026-12-31');
        config()->set('espocrm.prima_nota.default_beneficiary_name', 'Safe House');
    }

    public function test_payment_intent_succeeded_writes_prima_nota_to_crm(): void
    {
        Carbon::setTestNow('2026-07-02T12:00:00+00:00');

        $campaign = DonationCampaign::factory()->create([
            'espocrm_finanziamento_name' => 'Raccolta Safe House 2026',
        ]);

        $intent = $this->paymentIntent([
            'id' => 'pi_webhook_success',
            'amount' => 5000,
            'amount_received' => 5000,
            'metadata' => [
                'campaign_id' => (string) $campaign->id,
                'donor_name' => 'Luigi Verdi',
                'donor_type' => 'individual',
                'comment' => 'Supporto mensile',
            ],
        ]);

        $this->fakeCrmForSuccessfulIngest('opp-webhook', 'pn-webhook', subjectContactMatches: 0, beneficiaryAccountId: 'acc-safe-house');
        $this->mockStripeWebhook($intent);

        $this->postStripeWebhook('{"id":"evt_test"}', 'sig_test')
            ->assertOk()
            ->assertSee('OK');

        Http::assertSent(function ($request): bool {
            if ($request->method() !== 'POST' || ! str_contains($request->url(), '/api/v1/PrimaNota')) {
                return false;
            }

            $payload = $request->data();

            return ($payload['donationPaymentReference'] ?? '') === '#pi_webhook_success'
                && ($payload['donationDonorCategory'] ?? '') === 'Individual'
                && ($payload['donationComment'] ?? '') === 'Supporto mensile'
                && ! array_key_exists('name', $payload)
                && ! array_key_exists('description', $payload)
                && ($payload['entryType'] ?? '') === 'Income'
                && ($payload['amount'] ?? null) === 50.0
                && ($payload['amountCurrency'] ?? '') === 'EUR'
                && ($payload['internalClassification'] ?? '') === 'Donation'
                && ($payload['subjectName'] ?? '') === 'Luigi Verdi'
                && ($payload['createSubjectContact'] ?? false) === true
                && ($payload['beneficiaryPartyId'] ?? '') === 'acc-safe-house'
                && ($payload['beneficiaryPartyType'] ?? '') === 'Account'
                && ($payload['financingId'] ?? '') === 'opp-webhook'
                && ! array_key_exists('createSubjectAccount', $payload);
        });
    }

    public function test_webhook_uses_campaign_finanziamento_title_override(): void
    {
        $campaign = DonationCampaign::factory()->create([
            'espocrm_finanziamento_name' => 'CRM Custom Finanziamento',
        ]);

        $intent = $this->paymentIntent([
            'id' => 'pi_fin_title',
            'metadata' => [
                'campaign_id' => (string) $campaign->id,
                'donor_name' => 'Donor',
            ],
        ]);

        Http::fake(function ($request) {
            $url = $request->url();
            $method = $request->method();

            if ($method === 'GET' && str_contains($url, '/api/v1/PrimaNota')) {
                return Http::response(['total' => 0, 'list' => []]);
            }

            if ($method === 'GET' && str_contains($url, '/api/v1/Opportunity')) {
                return Http::response([
                    'total' => 1,
                    'list' => [['id' => 'opp-custom', 'name' => 'CRM Custom Finanziamento']],
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

            if ($method === 'POST' && str_contains($url, '/api/v1/PrimaNota')) {
                return Http::response(['id' => 'pn-1']);
            }

            return Http::response(['message' => 'Unexpected'], 500);
        });

        $this->mockStripeWebhook($intent);

        $this->postStripeWebhook('{}', 'sig')
            ->assertOk();

        Http::assertSent(function ($request): bool {
            return $request->method() === 'GET'
                && str_contains($request->url(), '/api/v1/Opportunity')
                && ($request->data()['where'][0]['value'] ?? '') === 'CRM Custom Finanziamento';
        });
    }

    public function test_webhook_returns_400_for_invalid_signature(): void
    {
        Http::fake();

        $mock = Mockery::mock(StripePaymentService::class);
        $mock->shouldReceive('constructWebhookEvent')
            ->once()
            ->andThrow(new RuntimeException('Invalid signature'));
        $this->instance(StripePaymentService::class, $mock);

        $this->postStripeWebhook('{}', 'bad')
            ->assertBadRequest()
            ->assertSee('Invalid signature');

        Http::assertNothingSent();
    }

    public function test_webhook_ignores_unhandled_event_types(): void
    {
        Http::fake();

        $event = Event::constructFrom([
            'id' => 'evt_other',
            'type' => 'charge.refunded',
            'data' => ['object' => ['id' => 'ch_1']],
        ]);

        $mock = Mockery::mock(StripePaymentService::class);
        $mock->shouldReceive('constructWebhookEvent')->andReturn($event);
        $mock->shouldReceive('paymentIntentFromEvent')->with($event)->andReturn(null);
        $this->instance(StripePaymentService::class, $mock);

        $this->postStripeWebhook('{}', 'sig')
            ->assertOk()
            ->assertSee('Ignored');

        Http::assertNothingSent();
    }

    public function test_webhook_returns_502_when_crm_ingest_fails(): void
    {
        Http::fake([
            'https://crm.test/api/v1/PrimaNota*' => Http::response(['message' => 'Forbidden'], 403),
        ]);

        $intent = $this->paymentIntent(['id' => 'pi_crm_fail']);
        $this->mockStripeWebhook($intent);

        $this->postStripeWebhook('{}', 'sig')
            ->assertStatus(502)
            ->assertSee('CRM ingest failed');
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function paymentIntent(array $overrides = []): PaymentIntent
    {
        return PaymentIntent::constructFrom(array_merge([
            'id' => 'pi_default',
            'object' => 'payment_intent',
            'amount' => 1000,
            'amount_received' => 1000,
            'currency' => 'eur',
            'metadata' => [],
        ], $overrides));
    }

    private function fakeCrmForSuccessfulIngest(
        string $financingId,
        string $primaNotaId,
        int $subjectContactMatches = 0,
        ?string $subjectContactId = null,
        string $beneficiaryAccountId = 'acc-safe-house',
    ): void {
        Http::fake(function ($request) use (
            $financingId,
            $primaNotaId,
            $subjectContactMatches,
            $subjectContactId,
            $beneficiaryAccountId,
        ) {
            $url = $request->url();
            $method = $request->method();

            if ($method === 'GET' && str_contains($url, '/api/v1/PrimaNota')) {
                return Http::response(['total' => 0, 'list' => []]);
            }

            if ($method === 'GET' && str_contains($url, '/api/v1/Opportunity')) {
                return Http::response([
                    'total' => 1,
                    'list' => [['id' => $financingId, 'name' => 'Raccolta Safe House 2026']],
                ]);
            }

            if ($method === 'GET' && str_contains($url, '/api/v1/Contact')) {
                if ($subjectContactMatches === 1) {
                    return Http::response([
                        'total' => 1,
                        'list' => [['id' => $subjectContactId, 'name' => 'Luigi Verdi']],
                    ]);
                }

                return Http::response(['total' => 0, 'list' => []]);
            }

            if ($method === 'GET' && str_contains($url, '/api/v1/Account')) {
                return Http::response([
                    'total' => 1,
                    'list' => [['id' => $beneficiaryAccountId, 'name' => 'Safe House']],
                ]);
            }

            if ($method === 'POST' && str_contains($url, '/api/v1/PrimaNota')) {
                return Http::response(['id' => $primaNotaId, 'financingId' => $financingId]);
            }

            return Http::response(['message' => 'Unexpected'], 500);
        });
    }

    private function mockStripeWebhook(PaymentIntent $intent): void
    {
        $event = Event::constructFrom([
            'id' => 'evt_test',
            'type' => 'payment_intent.succeeded',
            'data' => ['object' => $intent->toArray()],
        ]);

        $mock = Mockery::mock(StripePaymentService::class);
        $mock->shouldReceive('constructWebhookEvent')->andReturn($event);
        $mock->shouldReceive('paymentIntentFromEvent')->with($event)->andReturn($intent);
        $this->instance(StripePaymentService::class, $mock);
    }

    private function postStripeWebhook(string $payload, string $signature): \Illuminate\Testing\TestResponse
    {
        return $this->call(
            'POST',
            '/api/webhooks/stripe',
            [],
            [],
            [],
            [
                'HTTP_Stripe-Signature' => $signature,
                'CONTENT_TYPE' => 'application/json',
            ],
            $payload,
        );
    }
}
