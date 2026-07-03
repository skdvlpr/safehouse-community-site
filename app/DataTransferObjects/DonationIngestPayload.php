<?php

namespace App\DataTransferObjects;

use App\Support\IntegrationConfig;

readonly class DonationIngestPayload
{
    public function __construct(
        public string $provider,
        public string $externalId,
        public float $amount,
        public string $currency,
        public string $campaignTitle,
        public string $donorName,
        public ?string $comment,
        public ?string $donorType,
        public string $donatedAt,
        public ?float $financingGoalAmount = null,
        public ?string $donorEmail = null,
        public ?string $donorPhone = null,
    ) {}

    public function platformLabel(): string
    {
        return match ($this->provider) {
            'stripe' => 'Stripe',
            default => ucfirst($this->provider),
        };
    }

    /**
     * Value for GET PrimaNota idempotency search (contains on donationPaymentReference).
     */
    public function idempotencySearchValue(): string
    {
        return $this->externalId;
    }

    public function orderReference(): string
    {
        return '#'.$this->externalId;
    }

    public function donorTypeLabel(): string
    {
        return match ($this->donorType) {
            'individual' => 'Individual',
            'organization' => 'Organization',
            default => '',
        };
    }

    /**
     * @return array<string, string>
     */
    public function primaNotaDonationFields(): array
    {
        $fields = [
            'donationPaymentProvider' => $this->platformLabel(),
            'donationPaymentReference' => $this->orderReference(),
        ];

        $donorCategory = $this->donorTypeLabel();
        if ($donorCategory !== '') {
            $fields['donationDonorCategory'] = $donorCategory;
        }

        if ($this->comment !== null && trim($this->comment) !== '') {
            $fields['donationComment'] = trim($this->comment);
        }

        return $fields;
    }

    /** Soggetto pagamento — payer / donor name. */
    public function subjectName(): string
    {
        $name = trim($this->donorName);

        if ($name !== '') {
            return $name;
        }

        return IntegrationConfig::string('espocrm.prima_nota.default_subject_name', 'Donatore');
    }

    /** Beneficiario — receiving organization. */
    public function beneficiaryName(): string
    {
        return IntegrationConfig::string('espocrm.prima_nota.default_beneficiary_name', 'Safe House');
    }

    public function isOrganization(): bool
    {
        return $this->donorType === 'organization';
    }

    public function subjectPartyEntityType(): string
    {
        return $this->isOrganization() ? 'Account' : 'Contact';
    }
}
