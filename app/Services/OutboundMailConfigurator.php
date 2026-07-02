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

    public function contactRecipient(): string
    {
        return IntegrationConfig::string('contact.notification_email');
    }

    public function canSendContactNotifications(): bool
    {
        return $this->isConfigured() && filter_var($this->contactRecipient(), FILTER_VALIDATE_EMAIL) !== false;
    }

    public function apply(): void
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
            'mail.mailers.smtp.username' => IntegrationConfig::string('mail.username'),
            'mail.mailers.smtp.password' => IntegrationConfig::string('mail.password'),
            'mail.mailers.smtp.scheme' => $scheme,
        ]);

        $fromAddress = IntegrationConfig::string('mail.from_address');
        $fromName = IntegrationConfig::string('mail.from_name');

        if ($fromAddress !== '') {
            config([
                'mail.from.address' => $fromAddress,
                'mail.from.name' => $fromName !== '' ? $fromName : (string) config('app.name'),
            ]);
        }
    }
}
