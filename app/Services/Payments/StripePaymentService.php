<?php

namespace App\Services\Payments;

use App\DataTransferObjects\StripeSettlementAmounts;
use App\Models\DonationCampaign;
use App\Support\IntegrationConfig;
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

        $this->assertDonationIntentAllowed($campaign, $amountCents);

        try {
            $payload = [
                'amount' => $amountCents,
                'currency' => strtolower($campaign->currency),
                'automatic_payment_methods' => ['enabled' => true],
                'setup_future_usage' => 'off_session',
                'metadata' => $this->metadata($campaign, $donorName, $donorType, $comment, $donorEmail, $donorPhone),
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
        $grossCents = (int) ($intent->amount_received ?? $intent->amount);
        $currency = (string) $intent->currency;
        $feeCents = 0;
        $netCents = $grossCents;

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

        if (is_object($charge)) {
            $balanceTransaction = $charge->balance_transaction ?? null;
            if (is_string($balanceTransaction) && $balanceTransaction !== '' && $this->client !== null) {
                try {
                    $balanceTransaction = $this->client->balanceTransactions->retrieve($balanceTransaction);
                } catch (ApiErrorException $exception) {
                    throw new RuntimeException('Stripe balance transaction lookup failed: '.$exception->getMessage(), 0, $exception);
                }
            }
            if (is_object($balanceTransaction)) {
                $feeCents = (int) ($balanceTransaction->fee ?? 0);
                $netCents = (int) ($balanceTransaction->net ?? ($grossCents - $feeCents));
            }
        }

        return StripeSettlementAmounts::fromCents([
            'gross_cents' => $grossCents,
            'fee_cents' => $feeCents,
            'net_cents' => $netCents,
            'currency' => $currency,
        ]);
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
    ): array {
        return array_filter([
            'campaign_id' => (string) $campaign->id,
            'campaign_title' => mb_substr($campaign->finanziamentoTitle(), 0, 500),
            'donor_name' => mb_substr(trim($donorName), 0, 500),
            'donor_type' => $donorType,
            'donor_email' => $donorEmail !== null ? mb_substr($donorEmail, 0, 500) : null,
            'donor_phone' => $donorPhone !== null ? mb_substr($donorPhone, 0, 500) : null,
            'comment' => $comment !== null ? mb_substr(trim($comment), 0, 500) : null,
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

    public static function statementDescriptorSuffix(): string
    {
        $descriptor = trim(IntegrationConfig::string('stripe.statement_descriptor', 'SAFE HOUSE'));

        return $descriptor === '' ? '' : mb_substr($descriptor, 0, 22);
    }
}
