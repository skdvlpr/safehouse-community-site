<?php

namespace App\Services\Payments;

use App\Support\IntegrationConfig;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;
use Throwable;

/**
 * Resolves the public Customer Portal login URL for the configured Stripe mode.
 * Prefer CMS override; otherwise fetch/enable login_page via Billing Portal API.
 */
class StripeCustomerPortalService
{
    private const CACHE_KEY = 'stripe:customer_portal_login_url';

    public function __construct(
        private readonly ?StripeClient $client = null,
    ) {}

    public static function fromConfig(): self
    {
        $secret = IntegrationConfig::string('stripe.secret');
        if ($secret === '') {
            return new self(null);
        }

        return new self(new StripeClient($secret));
    }

    public function loginUrl(): ?string
    {
        $configured = trim(IntegrationConfig::string('stripe.customer_portal_login_url'));
        if ($configured !== '') {
            return $configured;
        }

        if ($this->client === null) {
            return null;
        }

        return Cache::remember(self::CACHE_KEY, now()->addHour(), function (): ?string {
            try {
                return $this->fetchOrEnableLoginUrl();
            } catch (Throwable $exception) {
                Log::warning('Unable to resolve Stripe Customer Portal login URL.', [
                    'message' => $exception->getMessage(),
                ]);

                return null;
            }
        });
    }

    public function forgetCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * One-time Customer Portal session URL (preferred after a successful recurring payment).
     */
    public function sessionUrl(string $customerId, string $returnUrl): ?string
    {
        if ($this->client === null || trim($customerId) === '') {
            return null;
        }

        try {
            $session = $this->client->billingPortal->sessions->create([
                'customer' => $customerId,
                'return_url' => $returnUrl,
            ]);
        } catch (ApiErrorException $exception) {
            Log::warning('Unable to create Stripe Customer Portal session.', [
                'message' => $exception->getMessage(),
                'customer_id' => $customerId,
            ]);

            return null;
        }

        $url = trim((string) ($session->url ?? ''));

        return $url !== '' ? $url : null;
    }

    private function fetchOrEnableLoginUrl(): ?string
    {
        if ($this->client === null) {
            return null;
        }

        try {
            $configs = $this->client->billingPortal->configurations->all(['limit' => 10]);
        } catch (ApiErrorException $exception) {
            throw new RuntimeException('Stripe portal list failed: '.$exception->getMessage(), 0, $exception);
        }

        foreach ($configs->data as $config) {
            $url = $this->ensureLoginPageEnabled($config->id);
            if ($url !== null) {
                return $url;
            }
        }

        $created = $this->client->billingPortal->configurations->create([
            'business_profile' => [
                'headline' => 'Safe House — gestisci la tua donazione',
            ],
            'features' => [
                'customer_update' => [
                    'enabled' => true,
                    'allowed_updates' => ['email'],
                ],
                'invoice_history' => ['enabled' => true],
                'payment_method_update' => ['enabled' => true],
                'subscription_cancel' => [
                    'enabled' => true,
                    'mode' => 'at_period_end',
                ],
            ],
            'login_page' => ['enabled' => true],
        ]);

        return $this->loginUrlFromConfig($created);
    }

    private function ensureLoginPageEnabled(string $configurationId): ?string
    {
        if ($this->client === null) {
            return null;
        }

        $updated = $this->client->billingPortal->configurations->update($configurationId, [
            'login_page' => ['enabled' => true],
            'features' => [
                'subscription_cancel' => [
                    'enabled' => true,
                    'mode' => 'at_period_end',
                ],
                'payment_method_update' => ['enabled' => true],
                'invoice_history' => ['enabled' => true],
            ],
        ]);

        return $this->loginUrlFromConfig($updated);
    }

    private function loginUrlFromConfig(object $config): ?string
    {
        $login = $config->login_page ?? null;
        if (! is_object($login)) {
            return null;
        }

        $url = trim((string) ($login->url ?? ''));

        return $url !== '' ? $url : null;
    }
}
