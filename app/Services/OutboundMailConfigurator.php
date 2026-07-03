<?php

namespace App\Services;

use App\Support\IntegrationConfig;

class OutboundMailConfigurator
{
    public function isConfigured(): bool
    {
        return $this->host() !== '';
    }

    public function host(): string
    {
        return IntegrationConfig::string('mail.host');
    }

    public function canSendSportelloNotifications(): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }

        $fromAddress = $this->websiteFromAddress();

        return IntegrationConfig::string('mail.username') !== ''
            && filter_var($fromAddress, FILTER_VALIDATE_EMAIL) !== false;
    }

    public function websiteFromAddress(): string
    {
        $configured = IntegrationConfig::string('contact.website_from_address');

        if ($configured !== '' && filter_var($configured, FILTER_VALIDATE_EMAIL) !== false) {
            return $configured;
        }

        $fallback = (string) config('contact_mail.website_from_address', 'website@safehouse.community');

        return filter_var($fallback, FILTER_VALIDATE_EMAIL) !== false
            ? $fallback
            : IntegrationConfig::string('mail.from_address');
    }

    public function websiteFromName(): string
    {
        $configured = IntegrationConfig::string('contact.website_from_name');

        if ($configured !== '') {
            return $configured;
        }

        $fallback = (string) config('contact_mail.website_from_name', '');

        return $fallback !== '' ? $fallback : IntegrationConfig::string('mail.from_name');
    }

    public function apply(): void
    {
        $this->applySmtp(
            IntegrationConfig::string('mail.username'),
            IntegrationConfig::string('mail.password'),
            IntegrationConfig::string('mail.from_address'),
            IntegrationConfig::string('mail.from_name'),
        );
    }

    public function applyForSportello(): void
    {
        $this->applySmtp(
            IntegrationConfig::string('mail.username'),
            IntegrationConfig::string('mail.password'),
            $this->websiteFromAddress(),
            $this->websiteFromName(),
        );
    }

    private function applySmtp(string $username, string $password, string $fromAddress, string $fromName): void
    {
        $host = $this->host();

        if ($host === '') {
            return;
        }

        $port = (int) IntegrationConfig::string('mail.port', '587');
        $encryption = strtolower(IntegrationConfig::string('mail.encryption', 'tls'));
        $scheme = $encryption === 'ssl' ? 'smtps' : null;

        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => $host,
            'mail.mailers.smtp.port' => $port > 0 ? $port : 587,
            'mail.mailers.smtp.username' => $username,
            'mail.mailers.smtp.password' => $password,
            'mail.mailers.smtp.scheme' => $scheme,
        ]);

        if ($fromAddress !== '') {
            config([
                'mail.from.address' => $fromAddress,
                'mail.from.name' => $fromName !== '' ? $fromName : (string) config('app.name'),
            ]);
        }
    }
}
