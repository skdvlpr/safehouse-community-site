<?php

namespace App\Services\Payments;

use App\Models\DonationCampaign;
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
        $secret = (string) config('stripe.secret');

        if ($secret === '') {
            throw new RuntimeException('Stripe secret key is not configured.');
        }

        return new self(new StripeClient($secret));
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
    ): array {
        if ($this->client === null) {
            throw new RuntimeException('Stripe client is not configured.');
        }

        if ($amountCents < $campaign->min_amount_cents) {
            throw new RuntimeException('Amount is below the campaign minimum.');
        }

        if (! $campaign->allow_custom_amount && ! in_array($amountCents, $campaign->presetAmountCents(), true)) {
            throw new RuntimeException('Custom amounts are not allowed for this campaign.');
        }

        try {
            $intent = $this->client->paymentIntents->create([
                'amount' => $amountCents,
                'currency' => strtolower($campaign->currency),
                'automatic_payment_methods' => ['enabled' => true],
                'metadata' => $this->metadata($campaign, $donorName, $donorType, $comment),
            ]);
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

    public function constructWebhookEvent(string $payload, ?string $signature): \Stripe\Event
    {
        if ($this->client === null) {
            throw new RuntimeException('Stripe client is not configured.');
        }

        $secret = (string) config('stripe.webhook_secret');
        if ($secret === '' || $signature === null || $signature === '') {
            throw new RuntimeException('Stripe webhook is not configured.');
        }

        return \Stripe\Webhook::constructEvent($payload, $signature, $secret);
    }

    /**
     * @return array<string, string>
     */
    private function metadata(
        DonationCampaign $campaign,
        string $donorName,
        string $donorType,
        ?string $comment,
    ): array {
        return array_filter([
            'campaign_id' => (string) $campaign->id,
            'campaign_title' => mb_substr($campaign->finanziamentoTitle(), 0, 500),
            'donor_name' => mb_substr(trim($donorName), 0, 500),
            'donor_type' => $donorType,
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
}
