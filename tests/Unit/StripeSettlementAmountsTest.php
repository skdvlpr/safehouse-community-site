<?php

namespace Tests\Unit;

use App\DataTransferObjects\StripeSettlementAmounts;
use App\Services\Payments\StripePaymentService;
use Stripe\PaymentIntent;
use Tests\TestCase;

class StripeSettlementAmountsTest extends TestCase
{
    public function test_from_cents_computes_percent_from_stripe_fee(): void
    {
        $settlement = StripeSettlementAmounts::fromCents([
            'gross_cents' => 10000,
            'fee_cents' => 290,
            'net_cents' => 9710,
            'currency' => 'eur',
        ]);

        $this->assertSame(100.0, $settlement->gross);
        $this->assertSame(2.9, $settlement->fee);
        $this->assertSame(97.1, $settlement->net);
        $this->assertSame(2.9, $settlement->feePercent);
        $this->assertSame('EUR', $settlement->currency);
    }

    public function test_settlement_from_payment_intent_uses_balance_transaction(): void
    {
        $intent = PaymentIntent::constructFrom([
            'id' => 'pi_fee',
            'amount' => 10000,
            'amount_received' => 10000,
            'currency' => 'eur',
            'latest_charge' => [
                'id' => 'ch_1',
                'object' => 'charge',
                'balance_transaction' => [
                    'id' => 'txn_1',
                    'object' => 'balance_transaction',
                    'fee' => 145,
                    'net' => 9855,
                    'amount' => 10000,
                ],
            ],
        ]);

        $settlement = (new StripePaymentService(null))->settlementFromPaymentIntent($intent);

        $this->assertSame(100.0, $settlement->gross);
        $this->assertSame(1.45, $settlement->fee);
        $this->assertSame(98.55, $settlement->net);
        $this->assertSame(1.45, $settlement->feePercent);
    }

    public function test_mock_store_fee_is_source_of_truth(): void
    {
        $settlement = (new StripePaymentService(null))->settlementFromMockStoredIntent([
            'amount_cents' => 5000,
            'fee_cents' => 200,
            'net_cents' => 4800,
            'currency' => 'eur',
        ]);

        $this->assertSame(50.0, $settlement->gross);
        $this->assertSame(2.0, $settlement->fee);
        $this->assertSame(48.0, $settlement->net);
        $this->assertSame(4.0, $settlement->feePercent);
    }
}
