<?php

namespace Tests\Unit;

use App\Services\Payments\StripePaymentService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StripeSubscriptionInvoiceCredentialsTest extends TestCase
{
    #[Test]
    public function it_reads_legacy_invoice_payment_intent_id(): void
    {
        $invoice = (object) [
            'payment_intent' => 'pi_legacy_1',
        ];

        $this->assertSame(
            'pi_legacy_1',
            StripePaymentService::paymentIntentIdFromInvoiceObject($invoice),
        );
    }

    #[Test]
    public function it_reads_basil_invoice_payments_payment_intent_id(): void
    {
        $invoice = (object) [
            'payments' => (object) [
                'data' => [
                    (object) [
                        'payment' => (object) [
                            'type' => 'payment_intent',
                            'payment_intent' => 'pi_basil_1',
                        ],
                    ],
                ],
            ],
        ];

        $this->assertSame(
            'pi_basil_1',
            StripePaymentService::paymentIntentIdFromInvoiceObject($invoice),
        );
    }

    #[Test]
    public function it_parses_payment_intent_id_from_confirmation_secret(): void
    {
        $invoice = (object) [
            'confirmation_secret' => (object) [
                'client_secret' => 'pi_3TyA0U1XvMWH96Ks1hH7qXlo_secret_abc',
            ],
        ];

        $this->assertSame(
            'pi_3TyA0U1XvMWH96Ks1hH7qXlo',
            StripePaymentService::paymentIntentIdFromInvoiceObject($invoice),
        );
    }
}
