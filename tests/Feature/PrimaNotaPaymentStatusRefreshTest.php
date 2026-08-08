<?php

namespace Tests\Feature;

use App\Services\Donations\PrimaNotaPaymentStatusService;
use App\Services\Payments\StripePaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Mockery;
use Tests\TestCase;

class PrimaNotaPaymentStatusRefreshTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('espocrm.base_url', 'https://crm.test');
        config()->set('espocrm.api_key', 'test-espo-key');
        config()->set('espocrm.prima_nota.entity', 'PrimaNota');
    }

    public function test_refresh_marks_inviato_when_manual_payout_is_paid(): void
    {
        Http::fake([
            'https://crm.test/api/v1/PrimaNota/pn-manual*' => Http::sequence()
                ->push([
                    'id' => 'pn-manual',
                    'paymentStatus' => 'Planned',
                    'donationPaymentProvider' => 'Stripe',
                    'donationPaymentReference' => '#pi_manual_1',
                    'stripeChargeId' => 'ch_manual_1',
                    'stripeBalanceTransactionId' => 'txn_manual_1',
                    'stripePayoutId' => '',
                    'stripeSubscriptionId' => '',
                ], 200)
                ->push([
                    'id' => 'pn-manual',
                    'paymentStatus' => 'Planned',
                    'donationPaymentProvider' => 'Stripe',
                    'donationPaymentReference' => '#pi_manual_1',
                    'stripeChargeId' => 'ch_manual_1',
                    'stripeBalanceTransactionId' => 'txn_manual_1',
                    'stripePayoutId' => '',
                    'stripeSubscriptionId' => '',
                ], 200)
                ->push([
                    'id' => 'pn-manual',
                    'paymentStatus' => 'Inviato',
                ], 200),
            'https://crm.test/api/v1/PrimaNota*' => function ($request) {
                if ($request->method() === 'PUT') {
                    $data = $request->data();

                    return Http::response([
                        'id' => 'pn-manual',
                        'paymentStatus' => $data['paymentStatus'] ?? null,
                        'stripePayoutId' => $data['stripePayoutId'] ?? null,
                    ], 200);
                }

                if ($request->method() === 'GET') {
                    return Http::response([
                        'total' => 1,
                        'list' => [[
                            'id' => 'pn-manual',
                            'paymentStatus' => 'Planned',
                        ]],
                    ], 200);
                }

                return Http::response(['message' => 'unexpected '.$request->method()], 500);
            },
        ]);

        $payout = (object) [
            'id' => 'po_manual_paid',
            'object' => 'payout',
            'automatic' => false,
            'status' => 'paid',
            'arrival_date' => 1722470400,
            'created' => 1722470400,
        ];

        $charge = (object) [
            'id' => 'ch_manual_1',
            'amount' => 1000,
            'amount_refunded' => 0,
            'refunded' => false,
            'dispute' => null,
            'balance_transaction' => (object) [
                'id' => 'txn_manual_1',
                'payout' => 'po_manual_paid',
            ],
        ];

        $stripe = Mockery::mock(StripePaymentService::class);
        $stripe->shouldReceive('retrieveCharge')->andReturn($charge);
        $stripe->shouldReceive('retrieveBalanceTransaction')->andReturn((object) [
            'id' => 'txn_manual_1',
            'payout' => 'po_manual_paid',
        ]);
        $stripe->shouldReceive('retrievePayout')->andReturn($payout);
        $stripe->shouldReceive('findPaidAutomaticPayoutForPayment')->andReturn(null);
        $this->app->instance(StripePaymentService::class, $stripe);

        $result = app(PrimaNotaPaymentStatusService::class)->refreshFromPrimaNotaId('pn-manual');

        $this->assertTrue($result['updated']);
        $this->assertSame('Inviato', $result['paymentStatus']);
        $this->assertSame('manual_payout_paid', $result['reason']);
    }
}
