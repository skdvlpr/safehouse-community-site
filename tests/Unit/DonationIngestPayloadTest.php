<?php

namespace Tests\Unit;

use App\DataTransferObjects\DonationIngestPayload;
use Tests\TestCase;

class DonationIngestPayloadTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('espocrm.prima_nota.default_subject_name', 'Donatore');
        config()->set('espocrm.prima_nota.default_beneficiary_name', 'Safe House');
    }

    public function test_builds_description_with_split_party_fields(): void
    {
        $payload = new DonationIngestPayload(
            provider: 'stripe',
            externalId: 'pi_test_123',
            amount: 25.5,
            currency: 'EUR',
            campaignTitle: 'Raccolta fondi',
            donorName: 'Mario Rossi',
            comment: 'Grazie',
            donorType: 'individual',
            donatedAt: '2026-06-29T12:00:00+00:00',
        );

        $this->assertSame('pi_test_123', $payload->idempotencySearchValue());
        $this->assertSame(
            "Donazione Stripe ordine #pi_test_123\nTipo: individual\nGrazie",
            $payload->primaNotaDescription(),
        );
        $this->assertSame('Mario Rossi', $payload->subjectName());
        $this->assertSame('Safe House', $payload->beneficiaryName());
    }

    public function test_falls_back_to_configured_default_subject_name(): void
    {
        $payload = new DonationIngestPayload(
            provider: 'stripe',
            externalId: 'pi_anon',
            amount: 10,
            currency: 'EUR',
            campaignTitle: 'Raccolta',
            donorName: '',
            comment: null,
            donorType: null,
            donatedAt: '2026-06-29T12:00:00+00:00',
        );

        $this->assertSame('Donatore', $payload->subjectName());
        $this->assertSame('Safe House', $payload->beneficiaryName());
    }
}
