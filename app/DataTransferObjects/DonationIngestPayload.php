<?php

namespace App\DataTransferObjects;

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
    ) {}

    public function platformLabel(): string
    {
        return match ($this->provider) {
            'stripe' => 'Stripe',
            default => ucfirst($this->provider),
        };
    }

    /**
     * Value for GET PrimaNota idempotency search (contains on description).
     */
    public function idempotencySearchValue(): string
    {
        return $this->externalId;
    }

    public function orderReference(): string
    {
        return '#'.$this->externalId;
    }

    public function primaNotaDescription(): string
    {
        $lines = [
            sprintf(
                'Donazione %s ordine %s',
                $this->platformLabel(),
                $this->orderReference(),
            ),
        ];

        if ($this->donorType !== null && $this->donorType !== '') {
            $lines[] = 'Tipo: '.$this->donorType;
        }

        if ($this->comment !== null && $this->comment !== '') {
            $lines[] = $this->comment;
        }

        return implode("\n", $lines);
    }

    /** Soggetto pagamento — payer / donor name. */
    public function subjectName(): string
    {
        $name = trim($this->donorName);

        if ($name !== '') {
            return $name;
        }

        return (string) config('espocrm.prima_nota.default_subject_name', 'Donatore');
    }

    /** Beneficiario — receiving organization. */
    public function beneficiaryName(): string
    {
        return (string) config('espocrm.prima_nota.default_beneficiary_name', 'Safe House');
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
