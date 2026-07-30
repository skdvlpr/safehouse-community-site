<?php

namespace App\DataTransferObjects;

use Illuminate\Support\Carbon;

/**
 * Stripe P0/P1/P2 enrichment mapped to PrimaNota field names.
 */
readonly class StripeEnrichmentFields
{
    /**
     * @param  array<int, array<string, mixed>>|null  $feeDetails
     */
    public function __construct(
        public ?string $stripePaymentCreatedAt = null,
        public ?string $stripeChargeId = null,
        public ?string $stripeBalanceTransactionId = null,
        public ?string $stripePaymentMethodType = null,
        public ?string $stripeCardBrand = null,
        public ?string $stripeCardLast4 = null,
        public ?string $stripeReceiptUrl = null,
        public ?string $stripeReceiptEmail = null,
        public ?string $stripeBillingEmail = null,
        public ?string $stripeBillingPhone = null,
        public ?string $stripeFeeDetailsJson = null,
        public ?bool $stripeLivemode = null,
        public ?string $stripeRadarRiskLevel = null,
        public ?string $stripeStatementDescriptor = null,
        public ?string $stripeCustomerId = null,
        public ?string $stripeSubscriptionId = null,
        public ?string $stripeInvoiceId = null,
        public ?string $stripeInvoiceNumber = null,
    ) {}

    /**
     * @param  array<string, mixed>  $stored
     */
    public static function fromMockStoredIntent(array $stored): self
    {
        $created = null;
        if (isset($stored['created']) && is_numeric($stored['created'])) {
            $created = Carbon::createFromTimestamp((int) $stored['created'], 'UTC')->format('Y-m-d H:i:s');
        }

        return new self(
            stripePaymentCreatedAt: $created,
            stripeChargeId: isset($stored['charge_id']) ? (string) $stored['charge_id'] : null,
            stripeBalanceTransactionId: isset($stored['balance_transaction_id']) ? (string) $stored['balance_transaction_id'] : null,
            stripePaymentMethodType: (string) ($stored['payment_method_type'] ?? 'card'),
            stripeCardBrand: isset($stored['card_brand']) ? (string) $stored['card_brand'] : null,
            stripeCardLast4: isset($stored['card_last4']) ? (string) $stored['card_last4'] : null,
            stripeReceiptUrl: isset($stored['receipt_url']) ? (string) $stored['receipt_url'] : null,
            stripeReceiptEmail: isset($stored['receipt_email']) ? (string) $stored['receipt_email'] : null,
            stripeBillingEmail: isset($stored['billing_email']) ? (string) $stored['billing_email'] : null,
            stripeBillingPhone: isset($stored['billing_phone']) ? (string) $stored['billing_phone'] : null,
            stripeFeeDetailsJson: null,
            stripeLivemode: false,
            stripeRadarRiskLevel: null,
            stripeStatementDescriptor: isset($stored['statement_descriptor']) ? (string) $stored['statement_descriptor'] : null,
            stripeCustomerId: isset($stored['customer_id']) ? (string) $stored['customer_id'] : null,
            stripeSubscriptionId: isset($stored['subscription_id'])
                ? (string) $stored['subscription_id']
                : (isset($stored['metadata']['stripe_subscription_id'])
                    ? (string) $stored['metadata']['stripe_subscription_id']
                    : null),
            stripeInvoiceId: isset($stored['invoice_id']) ? (string) $stored['invoice_id'] : null,
            stripeInvoiceNumber: isset($stored['invoice_number']) ? (string) $stored['invoice_number'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toPrimaNotaFields(): array
    {
        $fields = [
            'stripePaymentCreatedAt' => $this->stripePaymentCreatedAt,
            'stripeChargeId' => $this->stripeChargeId,
            'stripeBalanceTransactionId' => $this->stripeBalanceTransactionId,
            'stripePaymentMethodType' => $this->stripePaymentMethodType,
            'stripeCardBrand' => $this->stripeCardBrand,
            'stripeCardLast4' => $this->stripeCardLast4,
            'stripeReceiptUrl' => $this->stripeReceiptUrl,
            'stripeReceiptEmail' => $this->stripeReceiptEmail,
            'stripeBillingEmail' => $this->stripeBillingEmail,
            'stripeBillingPhone' => $this->stripeBillingPhone,
            'stripeFeeDetailsJson' => $this->stripeFeeDetailsJson,
            'stripeLivemode' => $this->stripeLivemode,
            'stripeRadarRiskLevel' => $this->stripeRadarRiskLevel,
            'stripeStatementDescriptor' => $this->stripeStatementDescriptor,
            'stripeCustomerId' => $this->stripeCustomerId,
            'stripeSubscriptionId' => $this->stripeSubscriptionId,
            'stripeInvoiceId' => $this->stripeInvoiceId,
            'stripeInvoiceNumber' => $this->stripeInvoiceNumber,
        ];

        return array_filter(
            $fields,
            static fn ($value) => $value !== null && $value !== '',
        );
    }
}
