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

    public function test_builds_donation_fields_for_stripe_payment(): void
    {
        $payload = new DonationIngestPayload(
            provider: 'stripe',
            externalId: 'pi_test_123',
            amountGross: 25.5,
            commissionAmount: 0,
            commissionPercent: 0,
            netAmount: 25.5,
            currency: 'EUR',
            campaignTitle: 'Raccolta fondi',
            donorName: 'Mario Rossi',
            comment: 'Grazie',
            donorType: 'individual',
            donatedAt: '2026-06-29T12:00:00+00:00',
        );

        $this->assertSame('pi_test_123', $payload->idempotencySearchValue());
        $this->assertSame([
            'donationPaymentProvider' => 'Stripe',
            'donationPaymentReference' => '#pi_test_123',
            'donationDonorCategory' => 'Individual',
            'donationComment' => 'Grazie',
        ], $payload->primaNotaDonationFields());
        $this->assertSame('Mario Rossi', $payload->subjectName());
        $this->assertSame('Safe House', $payload->beneficiaryName());
    }

    public function test_omits_optional_donation_fields_when_missing(): void
    {
        $payload = new DonationIngestPayload(
            provider: 'stripe',
            externalId: 'pi_anon',
            amountGross: 10,
            commissionAmount: 0,
            commissionPercent: 0,
            netAmount: 10,
            currency: 'EUR',
            campaignTitle: 'Raccolta',
            donorName: '',
            comment: null,
            donorType: null,
            donatedAt: '2026-06-29T12:00:00+00:00',
        );

        $this->assertSame('Donatore', $payload->subjectName());
        $this->assertSame('Safe House', $payload->beneficiaryName());
        $this->assertSame([
            'donationPaymentProvider' => 'Stripe',
            'donationPaymentReference' => '#pi_anon',
        ], $payload->primaNotaDonationFields());
    }

    public function test_non_stripe_provider_maps_to_other_enum_not_invented_label(): void
    {
        $payload = DonationIngestPayload::withGrossOnly(
            provider: 'paypal',
            externalId: 'ext_1',
            gross: 5,
            currency: 'EUR',
            campaignTitle: 'Raccolta',
            donorName: 'X',
            comment: null,
            donorType: null,
            donatedAt: '2026-06-29T12:00:00+00:00',
        );

        $this->assertSame('Other', $payload->platformLabel());
        $this->assertSame('Other', $payload->primaNotaDonationFields()['donationPaymentProvider']);
    }
}
