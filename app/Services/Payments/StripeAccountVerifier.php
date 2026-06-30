<?php

namespace App\Services\Payments;

use RuntimeException;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class StripeAccountVerifier
{
    /**
     * @return list<array{label: string, status: string, detail: string}>
     */
    public function runChecks(): array
    {
        if (StripePaymentService::mockModeEnabled()) {
            return [
                $this->pass('Stripe mode', 'Mock — local simulation without API keys'),
                $this->pass('Mock complete endpoint', '/api/donations/mock/{paymentIntent}/complete'),
                $this->pass('EspoCRM ingest', 'Same DonationIngestService path as production webhook'),
            ];
        }

        $checks = [];

        $publishableKey = (string) config('stripe.key');
        $secretKey = (string) config('stripe.secret');
        $webhookSecret = (string) config('stripe.webhook_secret');

        $checks[] = $this->checkKeyPresent('STRIPE_KEY (publishable)', $publishableKey);
        $checks[] = $this->checkKeyPresent('STRIPE_SECRET', $secretKey);
        $checks[] = $this->checkKeyPresent('STRIPE_WEBHOOK_SECRET', $webhookSecret, required: false);

        $publishableMode = $this->keyMode($publishableKey);
        $secretMode = $this->keyMode($secretKey);

        if ($publishableKey !== '' && $publishableMode === null) {
            $checks[] = $this->fail('Publishable key format', 'Expected pk_test_… or pk_live_…');
        } elseif ($publishableKey !== '') {
            $checks[] = $this->pass('Publishable key mode', strtoupper($publishableMode ?? 'unknown'));
        }

        if ($secretKey !== '' && $secretMode === null) {
            $checks[] = $this->fail('Secret key format', 'Expected sk_test_… or sk_live_…');
        } elseif ($secretKey !== '') {
            $checks[] = $this->pass('Secret key mode', strtoupper($secretMode ?? 'unknown'));
        }

        if ($publishableMode !== null && $secretMode !== null && $publishableMode !== $secretMode) {
            $checks[] = $this->fail('Key mode match', "Publishable is {$publishableMode}, secret is {$secretMode}");
        }

        if ($secretKey === '' || $secretMode === null) {
            return $checks;
        }

        try {
            $client = new StripeClient($secretKey);
            $balance = $client->balance->retrieve();
            /** @var \Stripe\Account $account */
            $account = $client->request('get', '/v1/account', [], []);
        } catch (ApiErrorException $exception) {
            $checks[] = $this->fail('Stripe API / account', $exception->getMessage());

            return $checks;
        } catch (RuntimeException $exception) {
            $checks[] = $this->fail('Stripe client', $exception->getMessage());

            return $checks;
        }

        $checks[] = $this->pass(
            'Stripe API connection',
            ($balance->livemode ? 'Live' : 'Test').' mode — account '.$account->id,
        );

        $checks[] = ($account->charges_enabled ?? false)
            ? $this->pass('Charges enabled', 'Card payments allowed')
            : $this->fail('Charges enabled', 'Complete Stripe onboarding / verification in Dashboard');

        $checks[] = ($account->payouts_enabled ?? false)
            ? $this->pass('Payouts enabled', 'Bank account linked — donations can settle')
            : $this->fail('Payouts enabled', 'Add association bank account in Stripe → Settings → Payouts');

        $defaultCurrency = strtolower((string) ($account->default_currency ?? ''));
        $expectedCurrency = strtolower((string) config('stripe.currency', 'eur'));
        $checks[] = $defaultCurrency === $expectedCurrency || $defaultCurrency === ''
            ? $this->pass('Default currency', strtoupper($expectedCurrency))
            : $this->fail('Default currency', "Account default is {$defaultCurrency}, site expects {$expectedCurrency}");

        $descriptor = (string) config('stripe.statement_descriptor', '');
        if ($descriptor === '') {
            $checks[] = $this->fail('Statement descriptor', 'Set STRIPE_STATEMENT_DESCRIPTOR (max 22 chars)');
        } elseif (strlen($descriptor) > 22) {
            $checks[] = $this->fail('Statement descriptor', 'Max 22 characters for card statements');
        } else {
            $checks[] = $this->pass('Statement descriptor', $descriptor);
        }

        $webhookUrl = (string) config('stripe.webhook_url', '');
        if ($webhookUrl !== '') {
            $checks[] = $this->pass('Webhook URL (register in Dashboard)', $webhookUrl);
            $checks[] = $this->pass('Webhook event', 'payment_intent.succeeded');
        } elseif ($webhookSecret === '') {
            $checks[] = $this->fail('Webhook', 'Set STRIPE_WEBHOOK_SECRET or use stripe listen locally');
        } else {
            $checks[] = $this->pass('Webhook secret', 'Configured (verify endpoint in Dashboard for production)');
        }

        return $checks;
    }

    public function allPassed(array $checks): bool
    {
        foreach ($checks as $check) {
            if (($check['status'] ?? '') !== 'pass') {
                return false;
            }
        }

        return $checks !== [];
    }

    public function keyMode(string $key): ?string
    {
        if (str_starts_with($key, 'pk_test_') || str_starts_with($key, 'sk_test_')) {
            return 'test';
        }

        if (str_starts_with($key, 'pk_live_') || str_starts_with($key, 'sk_live_')) {
            return 'live';
        }

        return null;
    }

    /**
     * @return array{label: string, status: string, detail: string}
     */
    private function checkKeyPresent(string $label, string $value, bool $required = true): array
    {
        if ($value !== '') {
            return $this->pass($label, 'Set');
        }

        return $required
            ? $this->fail($label, 'Missing in .env')
            : $this->pass($label, 'Optional — not set');
    }

    /**
     * @return array{label: string, status: string, detail: string}
     */
    private function pass(string $label, string $detail): array
    {
        return ['label' => $label, 'status' => 'pass', 'detail' => $detail];
    }

    /**
     * @return array{label: string, status: string, detail: string}
     */
    private function fail(string $label, string $detail): array
    {
        return ['label' => $label, 'status' => 'fail', 'detail' => $detail];
    }
}
