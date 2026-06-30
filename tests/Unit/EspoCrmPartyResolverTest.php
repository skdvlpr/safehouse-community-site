<?php

namespace Tests\Unit;

use App\DataTransferObjects\DonationIngestPayload;
use App\Services\EspoCrm\EspoCrmClient;
use App\Services\EspoCrm\EspoCrmPartyResolver;
use RuntimeException;
use Tests\TestCase;

class EspoCrmPartyResolverTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('espocrm.base_url', 'https://crm.test');
        config()->set('espocrm.api_key', 'test-espo-key');
        config()->set('espocrm.prima_nota.default_beneficiary_name', 'Safe House');
    }

    public function test_resolves_existing_contact_for_individual_donor(): void
    {
        $client = $this->createMock(EspoCrmClient::class);
        $client->method('search')
            ->willReturnMap([
                ['Contact', $this->searchArgs('Mario Rossi'), ['total' => 1, 'list' => [['id' => 'contact-1', 'name' => 'Mario Rossi']]]],
            ]);

        $resolver = new EspoCrmPartyResolver($client);
        $payload = $this->payload(donorName: 'Mario Rossi', donorType: 'individual');

        $this->assertSame([
            'subjectName' => 'Mario Rossi',
            'subjectPartyId' => 'contact-1',
            'subjectPartyType' => 'Contact',
        ], $resolver->resolveSubjectPartyFields($payload));
    }

    public function test_creates_account_for_new_organization_donor(): void
    {
        $client = $this->createMock(EspoCrmClient::class);
        $client->method('search')
            ->willReturnMap([
                ['Account', $this->searchArgs('Acme SRL'), ['total' => 0, 'list' => []]],
            ]);

        $resolver = new EspoCrmPartyResolver($client);

        $this->assertSame([
            'subjectName' => 'Acme SRL',
            'createSubjectAccount' => true,
        ], $resolver->resolveSubjectPartyFields($this->payload(
            donorName: 'Acme SRL',
            donorType: 'organization',
        )));
    }

    public function test_creates_contact_for_new_individual_donor(): void
    {
        $client = $this->createMock(EspoCrmClient::class);
        $client->method('search')
            ->willReturnMap([
                ['Contact', $this->searchArgs('Anna Bianchi'), ['total' => 0, 'list' => []]],
            ]);

        $resolver = new EspoCrmPartyResolver($client);

        $this->assertSame([
            'subjectName' => 'Anna Bianchi',
            'createSubjectContact' => true,
        ], $resolver->resolveSubjectPartyFields($this->payload(
            donorName: 'Anna Bianchi',
            donorType: 'individual',
        )));
    }

    public function test_links_existing_beneficiary_account(): void
    {
        $client = $this->createMock(EspoCrmClient::class);
        $client->method('search')
            ->willReturnMap([
                ['Account', $this->searchArgs('Safe House'), ['total' => 1, 'list' => [['id' => 'acc-sh', 'name' => 'Safe House']]]],
            ]);

        $resolver = new EspoCrmPartyResolver($client);

        $this->assertSame([
            'beneficiaryName' => 'Safe House',
            'beneficiaryPartyId' => 'acc-sh',
            'beneficiaryPartyType' => 'Account',
        ], $resolver->resolveBeneficiaryPartyFields($this->payload()));
    }

    public function test_creates_beneficiary_account_when_missing(): void
    {
        $client = $this->createMock(EspoCrmClient::class);
        $client->method('search')
            ->willReturnMap([
                ['Account', $this->searchArgs('Safe House'), ['total' => 0, 'list' => []]],
            ]);

        $resolver = new EspoCrmPartyResolver($client);

        $this->assertSame([
            'beneficiaryName' => 'Safe House',
            'createBeneficiaryAccount' => true,
        ], $resolver->resolveBeneficiaryPartyFields($this->payload()));
    }

    public function test_uses_configured_beneficiary_account_id_without_search(): void
    {
        config()->set('espocrm.prima_nota.beneficiary_account_id', 'acc-canonical');

        $client = $this->createMock(EspoCrmClient::class);
        $client->expects($this->never())->method('search');

        $resolver = new EspoCrmPartyResolver($client);

        $this->assertSame([
            'beneficiaryName' => 'Safe House',
            'beneficiaryPartyId' => 'acc-canonical',
            'beneficiaryPartyType' => 'Account',
        ], $resolver->resolveBeneficiaryPartyFields($this->payload()));
    }

    public function test_picks_oldest_beneficiary_account_when_multiple_match(): void
    {
        $client = $this->createMock(EspoCrmClient::class);
        $client->method('search')
            ->willReturnMap([
                ['Account', $this->searchArgs('Safe House'), [
                    'total' => 2,
                    'list' => [
                        ['id' => '6a430d744f60431c2', 'name' => 'Safe House'],
                        ['id' => '6a44256eafb7fc2c0', 'name' => 'Safe House'],
                    ],
                ]],
            ]);

        $resolver = new EspoCrmPartyResolver($client);

        $this->assertSame([
            'beneficiaryName' => 'Safe House',
            'beneficiaryPartyId' => '6a430d744f60431c2',
            'beneficiaryPartyType' => 'Account',
        ], $resolver->resolveBeneficiaryPartyFields($this->payload()));
    }

    public function test_subject_still_rejects_multiple_contact_matches(): void
    {
        $client = $this->createMock(EspoCrmClient::class);
        $client->method('search')
            ->willReturnMap([
                ['Contact', $this->searchArgs('Mario Rossi'), [
                    'total' => 2,
                    'list' => [
                        ['id' => 'contact-1', 'name' => 'Mario Rossi'],
                        ['id' => 'contact-2', 'name' => 'Mario Rossi'],
                    ],
                ]],
            ]);

        $resolver = new EspoCrmPartyResolver($client);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Multiple EspoCRM Contact records match Soggetto pagamento name "Mario Rossi".');

        $resolver->resolveSubjectPartyFields($this->payload(donorName: 'Mario Rossi', donorType: 'individual'));
    }

    /**
     * @return array<string, mixed>
     */
    private function searchArgs(string $name): array
    {
        return [
            'select' => 'id,name',
            'maxSize' => 5,
            'orderBy' => 'id',
            'order' => 'asc',
            'where' => [
                [
                    'type' => 'equals',
                    'attribute' => 'name',
                    'value' => $name,
                ],
                [
                    'type' => 'equals',
                    'attribute' => 'deleted',
                    'value' => false,
                ],
            ],
        ];
    }

    private function payload(
        string $donorName = 'Mario Rossi',
        string $donorType = 'individual',
    ): DonationIngestPayload {
        return new DonationIngestPayload(
            provider: 'stripe',
            externalId: 'pi_party_test',
            amount: 10,
            currency: 'EUR',
            campaignTitle: 'Raccolta',
            donorName: $donorName,
            comment: null,
            donorType: $donorType,
            donatedAt: now()->toIso8601String(),
        );
    }
}
