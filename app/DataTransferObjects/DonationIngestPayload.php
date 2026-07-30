<?php

namespace App\DataTransferObjects;

use App\Support\IntegrationConfig;

readonly class DonationIngestPayload
{
    public function __construct(
        public string $provider,
        public string $externalId,
        public float $amountGross,
        public float $commissionAmount,
        public float $commissionPercent,
        public float $netAmount,
        public string $currency,
        public string $campaignTitle,
        public string $donorName,
        public ?string $comment,
        public ?string $donorType,
        public string $donatedAt,
        public ?float $financingGoalAmount = null,
        public ?string $donorEmail = null,
        public ?string $donorPhone = null,
        public string $donationFrequency = 'OneTime',
        public ?StripeEnrichmentFields $stripeEnrichment = null,
    ) {}

    /**
     * Backward-compatible helper for tests: zero Stripe fee, net = gross.
     */
    public static function withGrossOnly(
        string $provider,
        string $externalId,
        float $gross,
        string $currency,
        string $campaignTitle,
        string $donorName,
        ?string $comment,
        ?string $donorType,
        string $donatedAt,
        ?float $financingGoalAmount = null,
        ?string $donorEmail = null,
        ?string $donorPhone = null,
        string $donationFrequency = 'OneTime',
        ?StripeEnrichmentFields $stripeEnrichment = null,
    ): self {
        return new self(
            provider: $provider,
            externalId: $externalId,
            amountGross: $gross,
            commissionAmount: 0.0,
            commissionPercent: 0.0,
            netAmount: $gross,
            currency: $currency,
            campaignTitle: $campaignTitle,
            donorName: $donorName,
            comment: $comment,
            donorType: $donorType,
            donatedAt: $donatedAt,
            financingGoalAmount: $financingGoalAmount,
            donorEmail: $donorEmail,
            donorPhone: $donorPhone,
            donationFrequency: $donationFrequency,
            stripeEnrichment: $stripeEnrichment,
        );
    }

    public function platformLabel(): string
    {
        // Stripe website ingest path must always map to CRM enum value "Stripe".
        if ($this->provider === 'stripe') {
            return 'Stripe';
        }

        return 'Other';
    }

    /**
     * Value for GET PrimaNota idempotency search (equals on donationPaymentReference = #externalId).
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
     * @return array<string, mixed>
     */
    public function primaNotaDonationFields(): array
    {
        $fields = [
            'donationPaymentProvider' => $this->platformLabel(),
            'donationPaymentReference' => $this->orderReference(),
            'donationFrequency' => $this->normalizedDonationFrequency(),
            'paymentStatus' => 'Planned',
        ];

        $donorCategory = $this->donorTypeLabel();
        if ($donorCategory !== '') {
            $fields['donationDonorCategory'] = $donorCategory;
        }

        if ($this->comment !== null && trim($this->comment) !== '') {
            $fields['donationComment'] = trim($this->comment);
        }

        if ($this->stripeEnrichment !== null) {
            $fields = array_merge($fields, $this->stripeEnrichment->toPrimaNotaFields());
        }

        return $fields;
    }

    public function normalizedDonationFrequency(): string
    {
        return $this->donationFrequency === 'Recurring' ? 'Recurring' : 'OneTime';
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
