<?php

namespace Tests\Feature;

use App\Services\Donations\PrimaNotaPaymentStatusService;
use App\Services\Payments\StripePaymentService;
use App\Support\IntegrationConfig;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Group;
use Stripe\Event;
use Stripe\StripeClient;
use Tests\TestCase;

/**
 * Live Stripe Test-mode (sandbox) lifecycle — no Stripe SDK mocks.
 *
 * Requires a sk_test_ secret from Integrations / .env / local-integrations.php.
 *
 * Run:
 *   php artisan test tests/Feature/StripeSandboxPayoutLifecycleTest.php
 *   php artisan test --group=stripe-sandbox
 */
#[Group('stripe-sandbox')]
class StripeSandboxPayoutLifecycleTest extends TestCase
{
    private StripeClient $stripe;

    protected function setUp(): void
    {
        parent::setUp();

        $secret = $this->resolveStripeTestSecret();

        if ($secret === '' || ! str_starts_with($secret, 'sk_test_')) {
            $this->markTestSkipped('Stripe sandbox tests require STRIPE_SECRET=sk_test_…');
        }

        config()->set('stripe.secret', $secret);
        config()->set('stripe.mock', false);
        config()->set('espocrm.base_url', 'https://crm.test');
        config()->set('espocrm.api_key', 'test-key');

        $this->stripe = new StripeClient($secret);
        $this->app->instance(StripePaymentService::class, new StripePaymentService($this->stripe));
    }

    public function test_sandbox_payment_intent_succeeds_and_balance_transaction_appears(): void
    {
        $intent = $this->stripe->paymentIntents->create([
            'amount' => 550,
            'currency' => 'eur',
            'confirm' => true,
            'payment_method' => 'pm_card_visa',
            'automatic_payment_methods' => [
                'enabled' => true,
                'allow_redirects' => 'never',
            ],
            'metadata' => [
                'safehouse_sandbox_test' => '1',
                'purpose' => 'prima_nota_status_lifecycle',
            ],
        ]);

        $this->assertSame('succeeded', $intent->status);
        $this->assertNotEmpty($intent->latest_charge);

        $chargeId = is_string($intent->latest_charge)
            ? $intent->latest_charge
            : (string) ($intent->latest_charge->id ?? '');

        $this->assertStringStartsWith('ch_', $chargeId);

        // On this account charge.balance_transaction can be null; BT appears in
        // balance_transactions shortly after (often still pending/available_on future).
        $btId = '';
        for ($attempt = 0; $attempt < 15; $attempt++) {
            $btId = $this->findBalanceTransactionIdForCharge($chargeId);
            if ($btId !== '') {
                break;
            }
            usleep(400000);
        }

        $this->assertStringStartsWith('txn_', $btId);
    }

    public function test_sandbox_payout_paid_marks_planned_prima_nota_inviato(): void
    {
        $payout = $this->findPaidPayoutWithChargeLeg();
        if ($payout === null) {
            $this->markTestSkipped('No paid Stripe payout with charge legs in this test account.');
        }

        $stripeService = new StripePaymentService($this->stripe);
        $bts = $stripeService->listBalanceTransactionsForPayout((string) $payout->id);

        $matchedBtId = '';
        $matchedChargeId = '';
        foreach ($bts as $bt) {
            $type = (string) ($bt->type ?? '');
            if (! in_array($type, ['charge', 'payment'], true)) {
                continue;
            }
            $matchedBtId = (string) ($bt->id ?? '');
            $source = $bt->source ?? null;
            $matchedChargeId = is_string($source) && str_starts_with($source, 'ch_')
                ? $source
                : (is_object($source) && isset($source->id) ? (string) $source->id : '');
            if ($matchedBtId !== '') {
                break;
            }
        }

        $this->assertNotSame('', $matchedBtId, 'Expected charge BT inside payout');

        Http::fake(function ($request) use ($payout) {
            $url = $request->url();
            $method = $request->method();

            if ($method === 'GET' && str_contains($url, '/api/v1/PrimaNota')) {
                return Http::response([
                    'total' => 1,
                    'list' => [[
                        'id' => 'pn-sandbox-1',
                        'paymentStatus' => 'Planned',
                    ]],
                ], 200);
            }

            if ($method === 'PUT' && str_contains($url, '/api/v1/PrimaNota/pn-sandbox-1')) {
                $data = $request->data();
                if (($data['paymentStatus'] ?? null) !== 'Inviato') {
                    return Http::response(['message' => 'unexpected status'], 500);
                }
                if (($data['stripePayoutId'] ?? null) !== $payout->id) {
                    return Http::response(['message' => 'unexpected payout'], 500);
                }

                return Http::response([
                    'id' => 'pn-sandbox-1',
                    'paymentStatus' => 'Inviato',
                ], 200);
            }

            return Http::response(['message' => 'unexpected'], 500);
        });

        $event = Event::constructFrom([
            'id' => 'evt_sandbox_payout_'.uniqid(),
            'type' => 'payout.paid',
            'data' => ['object' => $payout->toArray()],
        ]);

        $result = app(PrimaNotaPaymentStatusService::class)->applyFromStripeEvent($event);

        $this->assertTrue($result['handled']);
        $this->assertSame('Inviato', $result['status']);
        $this->assertGreaterThan(0, $result['updated']);

        Http::assertSent(function ($request) use ($payout) {
            $data = $request->data();

            return $request->method() === 'PUT'
                && str_contains($request->url(), '/api/v1/PrimaNota/pn-sandbox-1')
                && ($data['paymentStatus'] ?? null) === 'Inviato'
                && ($data['stripePayoutId'] ?? null) === $payout->id
                && ! empty($data['stripePayoutPaidAt'] ?? null);
        });

        $this->assertTrue($matchedChargeId === '' || str_starts_with($matchedChargeId, 'ch_'));
    }

    private function findBalanceTransactionIdForCharge(string $chargeId): string
    {
        $listed = $this->stripe->balanceTransactions->all([
            'type' => 'charge',
            'limit' => 50,
        ]);

        foreach ($listed->data as $bt) {
            $source = $bt->source ?? null;
            $sourceId = is_string($source)
                ? $source
                : (is_object($source) && isset($source->id) ? (string) $source->id : '');
            if ($sourceId === $chargeId) {
                return (string) ($bt->id ?? '');
            }
        }

        return '';
    }

    private function findPaidPayoutWithChargeLeg(): ?object
    {
        $payouts = $this->stripe->payouts->all(['limit' => 10, 'status' => 'paid']);

        foreach ($payouts->data as $payout) {
            $bts = $this->stripe->balanceTransactions->all([
                'payout' => $payout->id,
                'limit' => 20,
            ]);
            foreach ($bts->data as $bt) {
                if (in_array((string) ($bt->type ?? ''), ['charge', 'payment'], true)) {
                    return $payout;
                }
            }
        }

        return null;
    }

    private function resolveStripeTestSecret(): string
    {
        $candidates = [
            IntegrationConfig::string('stripe.secret'),
            (string) env('STRIPE_SECRET', ''),
            (string) (getenv('STRIPE_SECRET') ?: ''),
        ];

        $localIntegrations = database_path('seeders/data/local-integrations.php');
        if (is_readable($localIntegrations)) {
            /** @var mixed $data */
            $data = require $localIntegrations;
            if (is_array($data) && isset($data['stripe.secret'])) {
                $candidates[] = (string) $data['stripe.secret'];
            }
        }

        $envPath = base_path('.env');
        if (is_readable($envPath)) {
            foreach (file($envPath, FILE_IGNORE_NEW_LINES) ?: [] as $line) {
                if (preg_match('/^\s*STRIPE_SECRET\s*=\s*(.+)\s*$/', $line, $matches) !== 1) {
                    continue;
                }
                $candidates[] = trim($matches[1], " \t\"'");
            }
        }

        foreach ($candidates as $candidate) {
            if (str_starts_with($candidate, 'sk_test_')) {
                return $candidate;
            }
        }

        return '';
    }
}
