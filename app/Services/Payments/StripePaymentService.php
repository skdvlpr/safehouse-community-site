<?php

namespace App\Services\Payments;

use App\DataTransferObjects\StripeEnrichmentFields;
use App\DataTransferObjects\StripeSettlementAmounts;
use App\Models\DonationCampaign;
use App\Support\IntegrationConfig;
use Illuminate\Support\Carbon;
use RuntimeException;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;
use Stripe\StripeClient;

class StripePaymentService
{
    public function __construct(
        private readonly ?StripeClient $client = null,
    ) {}

    public static function fromConfig(): self
    {
        $secret = IntegrationConfig::string('stripe.secret');

        if ($secret === '') {
            throw new RuntimeException('Stripe secret key is not configured.');
        }

        return new self(new StripeClient($secret));
    }

    /**
     * @return array{client_secret: string, payment_intent_id: string}
     */
    public static function mockModeEnabled(): bool
    {
        $configured = config('stripe.mock');
        if ($configured !== null && $configured !== '') {
            return filter_var($configured, FILTER_VALIDATE_BOOL);
        }

        return app()->environment('local')
            && IntegrationConfig::string('stripe.secret') === '';
    }

    /**
     * @return array{client_secret: string, payment_intent_id: string}
     */
    public function createDonationIntent(
        DonationCampaign $campaign,
        int $amountCents,
        string $donorName,
        string $donorType,
        ?string $comment,
        ?string $donorEmail = null,
        ?string $donorPhone = null,
    ): array {
        if ($this->client === null) {
            throw new RuntimeException('Stripe client is not configured.');
        }

        if ($campaign->allowsRecurring()) {
            throw new RuntimeException('Recurring campaigns must use Stripe Subscriptions.');
        }

        $this->assertDonationIntentAllowed($campaign, $amountCents);

        try {
            $payload = [
                'amount' => $amountCents,
                'currency' => strtolower($campaign->currency),
                'automatic_payment_methods' => ['enabled' => true],
                'metadata' => $this->metadata($campaign, $donorName, $donorType, $comment, $donorEmail, $donorPhone, 'OneTime'),
            ];

            if ($donorEmail !== null && $donorEmail !== '') {
                $payload['receipt_email'] = $donorEmail;
            }

            $suffix = self::statementDescriptorSuffix();
            if ($suffix !== '') {
                $payload['statement_descriptor_suffix'] = $suffix;
            }

            $intent = $this->client->paymentIntents->create($payload);
        } catch (ApiErrorException $exception) {
            throw new RuntimeException('Stripe payment intent failed: '.$exception->getMessage(), 0, $exception);
        }

        $clientSecret = $intent->client_secret;
        if (! is_string($clientSecret) || $clientSecret === '') {
            throw new RuntimeException('Stripe did not return a client secret.');
        }

        return [
            'client_secret' => $clientSecret,
            'payment_intent_id' => $intent->id,
        ];
    }

    /**
     * Monthly Subscription; first invoice PaymentIntent drives Payment Element.
     *
     * @return array{client_secret: string, payment_intent_id: string, subscription_id: string, customer_id: string}
     */
    public function createDonationSubscription(
        DonationCampaign $campaign,
        int $amountCents,
        string $donorName,
        string $donorType,
        ?string $comment,
        ?string $donorEmail = null,
        ?string $donorPhone = null,
    ): array {
        if ($this->client === null) {
            throw new RuntimeException('Stripe client is not configured.');
        }

        if (! $campaign->allowsRecurring()) {
            throw new RuntimeException('One-time campaigns must use PaymentIntents.');
        }

        $this->assertDonationIntentAllowed($campaign, $amountCents);

        $metadata = $this->metadata($campaign, $donorName, $donorType, $comment, $donorEmail, $donorPhone, 'Recurring');

        try {
            $customerPayload = array_filter([
                'name' => trim($donorName),
                'email' => $donorEmail !== null && $donorEmail !== '' ? $donorEmail : null,
                'phone' => $donorPhone !== null && $donorPhone !== '' ? $donorPhone : null,
                'metadata' => $metadata,
            ], static fn ($value) => $value !== null && $value !== '');

            $customer = $this->client->customers->create($customerPayload);

            $price = $this->client->prices->create([
                'currency' => strtolower($campaign->currency),
                'unit_amount' => $amountCents,
                'recurring' => ['interval' => 'month'],
                'product_data' => [
                    'name' => mb_substr($campaign->finanziamentoTitle(), 0, 250),
                    'metadata' => [
                        'campaign_id' => (string) $campaign->id,
                    ],
                ],
            ]);

            $subscription = $this->client->subscriptions->create([
                'customer' => $customer->id,
                'items' => [['price' => $price->id]],
                'payment_behavior' => 'default_incomplete',
                'payment_settings' => [
                    'save_default_payment_method' => 'on_subscription',
                    'payment_method_types' => ['card'],
                ],
                'expand' => ['latest_invoice.payment_intent'],
                'metadata' => $metadata,
            ]);
        } catch (ApiErrorException $exception) {
            throw new RuntimeException('Stripe subscription failed: '.$exception->getMessage(), 0, $exception);
        }

        $invoice = $subscription->latest_invoice ?? null;
        $intent = is_object($invoice) ? ($invoice->payment_intent ?? null) : null;
        if (! is_object($intent) || ! isset($intent->id)) {
            throw new RuntimeException('Stripe subscription did not return a payment intent.');
        }

        $subscriptionMetadata = array_merge($metadata, [
            'stripe_subscription_id' => $subscription->id,
            'stripe_customer_id' => $customer->id,
        ]);

        try {
            $this->client->paymentIntents->update($intent->id, [
                'metadata' => $subscriptionMetadata,
            ]);
        } catch (ApiErrorException $exception) {
            throw new RuntimeException('Stripe payment intent metadata update failed: '.$exception->getMessage(), 0, $exception);
        }

        $clientSecret = $intent->client_secret ?? null;
        if (! is_string($clientSecret) || $clientSecret === '') {
            throw new RuntimeException('Stripe did not return a subscription client secret.');
        }

        return [
            'client_secret' => $clientSecret,
            'payment_intent_id' => (string) $intent->id,
            'subscription_id' => $subscription->id,
            'customer_id' => $customer->id,
        ];
    }

    public function retrievePaymentIntent(string $paymentIntentId): PaymentIntent
    {
        return $this->retrieveSettledPaymentIntent($paymentIntentId);
    }

    /**
     * PaymentIntent with latest_charge.balance_transaction expanded — Stripe fee/net SoT.
     */
    public function retrieveSettledPaymentIntent(string $paymentIntentId): PaymentIntent
    {
        if ($this->client === null) {
            throw new RuntimeException('Stripe client is not configured.');
        }

        try {
            $intent = $this->client->paymentIntents->retrieve($paymentIntentId, [
                'expand' => ['latest_charge.balance_transaction'],
            ]);
        } catch (ApiErrorException $exception) {
            throw new RuntimeException('Stripe payment intent lookup failed: '.$exception->getMessage(), 0, $exception);
        }

        if ($intent->status !== 'succeeded') {
            throw new RuntimeException('Stripe payment intent has not succeeded yet.');
        }

        return $intent;
    }

    /**
     * Gross / fee / net from Stripe BalanceTransaction (never invented locally).
     */
    public function settlementFromPaymentIntent(PaymentIntent $intent): StripeSettlementAmounts
    {
        [$charge, $balanceTransaction] = $this->resolveChargeAndBalanceTransaction($intent);

        $grossCents = (int) ($intent->amount_received ?? $intent->amount);
        $currency = (string) $intent->currency;
        $feeCents = 0;
        $netCents = $grossCents;

        if (is_object($balanceTransaction)) {
            $feeCents = (int) ($balanceTransaction->fee ?? 0);
            $netCents = (int) ($balanceTransaction->net ?? ($grossCents - $feeCents));
        }

        return StripeSettlementAmounts::fromCents([
            'gross_cents' => $grossCents,
            'fee_cents' => $feeCents,
            'net_cents' => $netCents,
            'currency' => $currency,
        ]);
    }

    /**
     * P0/P1/P2 enrichment from PaymentIntent + Charge + BalanceTransaction.
     */
    public function enrichmentFromPaymentIntent(PaymentIntent $intent): StripeEnrichmentFields
    {
        [$charge, $balanceTransaction] = $this->resolveChargeAndBalanceTransaction($intent);

        $createdAt = null;
        if (is_numeric($intent->created ?? null)) {
            $createdAt = Carbon::createFromTimestamp((int) $intent->created, 'UTC')
                ->format('Y-m-d H:i:s');
        }

        $methodType = null;
        $cardBrand = null;
        $cardLast4 = null;
        $receiptUrl = null;
        $receiptEmail = null;
        $billingEmail = null;
        $billingPhone = null;
        $riskLevel = null;
        $statementDescriptor = null;
        $chargeId = null;
        $livemode = is_bool($intent->livemode ?? null) ? (bool) $intent->livemode : null;

        if (is_object($charge)) {
            $chargeId = isset($charge->id) ? (string) $charge->id : null;
            if (is_bool($charge->livemode ?? null)) {
                $livemode = (bool) $charge->livemode;
            }
            $receiptUrl = isset($charge->receipt_url) && is_string($charge->receipt_url) ? $charge->receipt_url : null;
            $receiptEmail = isset($charge->receipt_email) && is_string($charge->receipt_email) ? $charge->receipt_email : null;
            $statementDescriptor = isset($charge->calculated_statement_descriptor) && is_string($charge->calculated_statement_descriptor)
                ? $charge->calculated_statement_descriptor
                : (isset($charge->statement_descriptor) && is_string($charge->statement_descriptor) ? $charge->statement_descriptor : null);

            $details = $charge->payment_method_details ?? null;
            if (is_object($details)) {
                $methodType = isset($details->type) ? (string) $details->type : null;
                $card = $details->card ?? null;
                if (is_object($card)) {
                    $cardBrand = isset($card->brand) ? (string) $card->brand : null;
                    $cardLast4 = isset($card->last4) ? (string) $card->last4 : null;
                }
            }

            $billing = $charge->billing_details ?? null;
            if (is_object($billing)) {
                $billingEmail = isset($billing->email) && is_string($billing->email) ? $billing->email : null;
                $billingPhone = isset($billing->phone) && is_string($billing->phone) ? $billing->phone : null;
            }

            $outcome = $charge->outcome ?? null;
            if (is_object($outcome) && isset($outcome->risk_level) && is_string($outcome->risk_level)) {
                $riskLevel = $outcome->risk_level;
            }
        }

        $btId = null;
        $feeDetailsJson = null;
        if (is_object($balanceTransaction)) {
            $btId = isset($balanceTransaction->id) ? (string) $balanceTransaction->id : null;
            $feeDetails = $balanceTransaction->fee_details ?? null;
            if (is_array($feeDetails) || is_object($feeDetails)) {
                $encoded = json_encode($feeDetails, JSON_UNESCAPED_UNICODE);
                $feeDetailsJson = is_string($encoded) ? $encoded : null;
            }
        }

        $customerId = null;
        $customer = $intent->customer ?? null;
        if (is_string($customer) && $customer !== '') {
            $customerId = $customer;
        } elseif (is_object($customer) && isset($customer->id)) {
            $customerId = (string) $customer->id;
        }

        $subscriptionId = $this->resolveSubscriptionId($intent);

        return new StripeEnrichmentFields(
            stripePaymentCreatedAt: $createdAt,
            stripeChargeId: $chargeId,
            stripeBalanceTransactionId: $btId,
            stripePaymentMethodType: $methodType,
            stripeCardBrand: $cardBrand,
            stripeCardLast4: $cardLast4,
            stripeReceiptUrl: $receiptUrl,
            stripeReceiptEmail: $receiptEmail,
            stripeBillingEmail: $billingEmail,
            stripeBillingPhone: $billingPhone,
            stripeFeeDetailsJson: $feeDetailsJson,
            stripeLivemode: $livemode,
            stripeRadarRiskLevel: $riskLevel,
            stripeStatementDescriptor: $statementDescriptor,
            stripeCustomerId: $customerId,
            stripeSubscriptionId: $subscriptionId,
        );
    }

    /**
     * Merge PaymentIntent metadata with parent Subscription metadata (renewals).
     *
     * @return array<string, string>
     */
    public function donationMetadataFromPaymentIntent(PaymentIntent $intent): array
    {
        $meta = [];
        if (isset($intent->metadata) && is_object($intent->metadata)) {
            $meta = $intent->metadata->toArray();
        }

        $subscriptionId = $this->resolveSubscriptionId($intent);
        if ($subscriptionId !== null && $this->client !== null) {
            try {
                $subscription = $this->client->subscriptions->retrieve($subscriptionId);
                if (isset($subscription->metadata) && is_object($subscription->metadata)) {
                    $meta = array_merge($subscription->metadata->toArray(), $meta);
                }
            } catch (ApiErrorException) {
                // Keep PaymentIntent metadata only.
            }
            $meta['stripe_subscription_id'] = $subscriptionId;
        }

        return array_filter(
            $meta,
            static fn ($value) => is_string($value) && $value !== '',
        );
    }

    public function resolveSubscriptionId(PaymentIntent $intent): ?string
    {
        $fromMeta = null;
        if (isset($intent->metadata) && is_object($intent->metadata)) {
            $candidate = $intent->metadata['stripe_subscription_id'] ?? $intent->metadata['subscription_id'] ?? null;
            if (is_string($candidate) && $candidate !== '') {
                $fromMeta = $candidate;
            }
        }
        if ($fromMeta !== null) {
            return $fromMeta;
        }

        $invoice = $intent->invoice ?? null;
        if (is_string($invoice) && $invoice !== '' && $this->client !== null) {
            try {
                $invoice = $this->client->invoices->retrieve($invoice);
            } catch (ApiErrorException) {
                return null;
            }
        }

        if (! is_object($invoice)) {
            return null;
        }

        $subscription = $invoice->subscription ?? null;
        if (is_string($subscription) && $subscription !== '') {
            return $subscription;
        }
        if (is_object($subscription) && isset($subscription->id)) {
            return (string) $subscription->id;
        }

        return null;
    }

    /**
     * @return array{0: object|string|null, 1: object|string|null}
     */
    private function resolveChargeAndBalanceTransaction(PaymentIntent $intent): array
    {
        $charge = $intent->latest_charge;
        if (is_string($charge) && $charge !== '' && $this->client !== null) {
            try {
                $charge = $this->client->charges->retrieve($charge, [
                    'expand' => ['balance_transaction'],
                ]);
            } catch (ApiErrorException $exception) {
                throw new RuntimeException('Stripe charge lookup failed: '.$exception->getMessage(), 0, $exception);
            }
        }

        $balanceTransaction = null;
        if (is_object($charge)) {
            $balanceTransaction = $charge->balance_transaction ?? null;
            if (is_string($balanceTransaction) && $balanceTransaction !== '' && $this->client !== null) {
                try {
                    $balanceTransaction = $this->client->balanceTransactions->retrieve($balanceTransaction);
                } catch (ApiErrorException $exception) {
                    throw new RuntimeException('Stripe balance transaction lookup failed: '.$exception->getMessage(), 0, $exception);
                }
            }
        }

        return [$charge, $balanceTransaction];
    }

    /**
     * Mock settlement: fee_cents/net_cents must come from the mock store (Stripe stand-in), never guessed %.
     *
     * @param  array<string, mixed>  $stored
     */
    public function settlementFromMockStoredIntent(array $stored): StripeSettlementAmounts
    {
        $grossCents = (int) ($stored['amount_cents'] ?? 0);
        $feeCents = (int) ($stored['fee_cents'] ?? 0);
        $netCents = array_key_exists('net_cents', $stored)
            ? (int) $stored['net_cents']
            : max(0, $grossCents - $feeCents);

        return StripeSettlementAmounts::fromCents([
            'gross_cents' => $grossCents,
            'fee_cents' => $feeCents,
            'net_cents' => $netCents,
            'currency' => (string) ($stored['currency'] ?? 'eur'),
        ]);
    }

    public function enrichmentFromMockStoredIntent(array $stored): StripeEnrichmentFields
    {
        return StripeEnrichmentFields::fromMockStoredIntent($stored);
    }

    public function constructWebhookEvent(string $payload, ?string $signature): \Stripe\Event
    {
        if ($this->client === null) {
            throw new RuntimeException('Stripe client is not configured.');
        }

        $secret = IntegrationConfig::string('stripe.webhook_secret');
        if ($secret === '' || $signature === null || $signature === '') {
            throw new RuntimeException('Stripe webhook is not configured.');
        }

        return \Stripe\Webhook::constructEvent($payload, $signature, $secret);
    }

    protected function assertDonationIntentAllowed(DonationCampaign $campaign, int $amountCents): void
    {
        if ($amountCents < $campaign->min_amount_cents) {
            throw new RuntimeException('Amount is below the campaign minimum.');
        }

        if (! $campaign->allow_custom_amount && ! in_array($amountCents, $campaign->presetAmountCents(), true)) {
            throw new RuntimeException('Custom amounts are not allowed for this campaign.');
        }
    }

    /**
     * @return array<string, string>
     */
    protected function metadata(
        DonationCampaign $campaign,
        string $donorName,
        string $donorType,
        ?string $comment,
        ?string $donorEmail = null,
        ?string $donorPhone = null,
        string $donationFrequency = 'OneTime',
    ): array {
        $frequency = $donationFrequency === 'Recurring' ? 'Recurring' : 'OneTime';

        return array_filter([
            'campaign_id' => (string) $campaign->id,
            'campaign_title' => mb_substr($campaign->finanziamentoTitle(), 0, 500),
            'donor_name' => mb_substr(trim($donorName), 0, 500),
            'donor_type' => $donorType,
            'donor_email' => $donorEmail !== null ? mb_substr($donorEmail, 0, 500) : null,
            'donor_phone' => $donorPhone !== null ? mb_substr($donorPhone, 0, 500) : null,
            'comment' => $comment !== null ? mb_substr(trim($comment), 0, 500) : null,
            'donation_frequency' => $frequency,
        ], fn ($value) => $value !== null && $value !== '');
    }

    public function paymentIntentFromEvent(\Stripe\Event $event): ?PaymentIntent
    {
        if ($event->type !== 'payment_intent.succeeded') {
            return null;
        }

        $intent = $event->data->object;

        return $intent instanceof PaymentIntent ? $intent : null;
    }

    /**
     * Resolve PaymentIntent id from payment_intent.succeeded or invoice.paid (renewals).
     */
    public function paymentIntentIdFromWebhookEvent(\Stripe\Event $event): ?string
    {
        if ($event->type === 'payment_intent.succeeded') {
            $intent = $this->paymentIntentFromEvent($event);

            return $intent?->id;
        }

        if ($event->type !== 'invoice.paid') {
            return null;
        }

        $invoice = $event->data->object ?? null;
        if (! is_object($invoice)) {
            return null;
        }

        $paymentIntent = $invoice->payment_intent ?? null;
        if (is_string($paymentIntent) && $paymentIntent !== '') {
            return $paymentIntent;
        }
        if (is_object($paymentIntent) && isset($paymentIntent->id)) {
            return (string) $paymentIntent->id;
        }

        return null;
    }

    public static function statementDescriptorSuffix(): string
    {
        $descriptor = trim(IntegrationConfig::string('stripe.statement_descriptor', 'SAFE HOUSE'));

        return $descriptor === '' ? '' : mb_substr($descriptor, 0, 22);
    }
}
