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

    public function test_enrichment_from_payment_intent_reads_method_and_ids(): void
    {
        $intent = PaymentIntent::constructFrom([
            'id' => 'pi_enrich',
            'amount' => 10000,
            'amount_received' => 10000,
            'currency' => 'eur',
            'created' => 1720000000,
            'livemode' => false,
            'customer' => 'cus_123',
            'latest_charge' => [
                'id' => 'ch_enrich',
                'object' => 'charge',
                'livemode' => false,
                'receipt_url' => 'https://pay.stripe.com/receipts/test',
                'receipt_email' => 'donor@example.com',
                'calculated_statement_descriptor' => 'SAFE HOUSE',
                'payment_method_details' => [
                    'type' => 'card',
                    'card' => [
                        'brand' => 'visa',
                        'last4' => '4242',
                    ],
                ],
                'billing_details' => [
                    'email' => 'bill@example.com',
                    'phone' => '+391234',
                ],
                'outcome' => [
                    'risk_level' => 'normal',
                ],
                'balance_transaction' => [
                    'id' => 'txn_enrich',
                    'object' => 'balance_transaction',
                    'fee' => 290,
                    'net' => 9710,
                    'amount' => 10000,
                    'fee_details' => [
                        ['type' => 'stripe_fee', 'amount' => 290, 'currency' => 'eur'],
                    ],
                ],
            ],
        ]);

        $enrichment = (new StripePaymentService(null))->enrichmentFromPaymentIntent($intent);
        $fields = $enrichment->toPrimaNotaFields();

        $this->assertSame('ch_enrich', $fields['stripeChargeId']);
        $this->assertSame('txn_enrich', $fields['stripeBalanceTransactionId']);
        $this->assertSame('card', $fields['stripePaymentMethodType']);
        $this->assertSame('visa', $fields['stripeCardBrand']);
        $this->assertSame('4242', $fields['stripeCardLast4']);
        $this->assertSame('cus_123', $fields['stripeCustomerId']);
        $this->assertSame('normal', $fields['stripeRadarRiskLevel']);
        $this->assertFalse($fields['stripeLivemode']);
        $this->assertStringContainsString('stripe_fee', (string) $fields['stripeFeeDetailsJson']);
    }

    public function test_settlement_from_payment_intent_without_balance_transaction_uses_zero_fee(): void
    {
        $intent = PaymentIntent::constructFrom([
            'id' => 'pi_no_bt',
            'amount' => 10000,
            'amount_received' => 10000,
            'currency' => 'eur',
            'latest_charge' => [
                'id' => 'ch_no_bt',
                'object' => 'charge',
            ],
        ]);

        $settlement = (new StripePaymentService(null))->settlementFromPaymentIntent($intent);

        $this->assertSame(100.0, $settlement->gross);
        $this->assertSame(0.0, $settlement->fee);
        $this->assertSame(100.0, $settlement->net);
    }
}
