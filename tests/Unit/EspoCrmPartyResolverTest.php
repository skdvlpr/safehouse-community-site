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

    public function test_resolves_existing_contact_by_email(): void
    {
        $client = $this->createMock(EspoCrmClient::class);
        $client->method('search')
            ->willReturnCallback(function (string $entityType, array $params): array {
                if ($entityType === 'Contact' && ($params['where'][0]['attribute'] ?? '') === 'emailAddress') {
                    return ['total' => 1, 'list' => [['id' => 'contact-by-email', 'name' => 'Other Name', 'emailAddress' => 'mario@example.com', 'phoneNumber' => null]]];
                }

                return ['total' => 0, 'list' => []];
            });

        $resolver = new EspoCrmPartyResolver($client);

        $this->assertSame([
            'subjectName' => 'Mario Rossi',
            'subjectPartyId' => 'contact-by-email',
            'subjectPartyType' => 'Contact',
            'subjectEmailAddress' => 'mario@example.com',
        ], $resolver->resolveSubjectPartyFields($this->payload(
            donorName: 'Mario Rossi',
            donorType: 'individual',
            donorEmail: 'mario@example.com',
        )));
    }

    public function test_backfills_missing_phone_when_matched_by_email(): void
    {
        $client = $this->createMock(EspoCrmClient::class);
        $client->method('search')
            ->willReturnCallback(function (string $entityType, array $params): array {
                if ($entityType === 'Contact' && ($params['where'][0]['attribute'] ?? '') === 'emailAddress') {
                    return ['total' => 1, 'list' => [[
                        'id' => 'contact-email-only',
                        'name' => 'Mario Rossi',
                        'emailAddress' => 'mario@example.com',
                        'phoneNumber' => null,
                    ]]];
                }

                return ['total' => 0, 'list' => []];
            });
        $client->expects($this->once())
            ->method('update')
            ->with('Contact', 'contact-email-only', ['phoneNumber' => '+393331112222']);

        $resolver = new EspoCrmPartyResolver($client);

        $this->assertSame([
            'subjectName' => 'Mario Rossi',
            'subjectPartyId' => 'contact-email-only',
            'subjectPartyType' => 'Contact',
            'subjectEmailAddress' => 'mario@example.com',
            'subjectPhoneNumber' => '+393331112222',
        ], $resolver->resolveSubjectPartyFields($this->payload(
            donorName: 'Mario Rossi',
            donorType: 'individual',
            donorEmail: 'mario@example.com',
            donorPhone: '+393331112222',
        )));
    }

    public function test_does_not_resolve_subject_by_name_only(): void
    {
        $client = $this->createMock(EspoCrmClient::class);
        $client->method('search')
            ->willReturnCallback(function (string $entityType, array $params): array {
                if (($params['where'][0]['attribute'] ?? '') === 'name') {
                    return ['total' => 1, 'list' => [['id' => 'contact-by-name', 'name' => 'Mario Rossi']]];
                }

                return ['total' => 0, 'list' => []];
            });

        $resolver = new EspoCrmPartyResolver($client);

        $this->assertSame([
            'subjectName' => 'Mario Rossi',
            'createSubjectContact' => true,
            'subjectEmailAddress' => 'mario@example.com',
        ], $resolver->resolveSubjectPartyFields($this->payload(
            donorName: 'Mario Rossi',
            donorType: 'individual',
            donorEmail: 'mario@example.com',
        )));
    }

    public function test_resolves_existing_contact_by_phone_numeric(): void
    {
        $client = $this->createMock(EspoCrmClient::class);
        $client->method('search')
            ->willReturnCallback(function (string $entityType, array $params): array {
                if ($entityType === 'Contact' && ($params['where'][0]['attribute'] ?? '') === 'phoneNumber') {
                    return ['total' => 0, 'list' => []];
                }

                if ($entityType === 'Contact' && ($params['where'][0]['attribute'] ?? '') === 'phoneNumberNumeric') {
                    return ['total' => 1, 'list' => [['id' => 'contact-by-phone', 'name' => 'Luigi Verdi']]];
                }

                return ['total' => 0, 'list' => []];
            });

        $resolver = new EspoCrmPartyResolver($client);

        $this->assertSame([
            'subjectName' => 'Different Spelling',
            'subjectPartyId' => 'contact-by-phone',
            'subjectPartyType' => 'Contact',
            'subjectPhoneNumber' => '+393331112222',
        ], $resolver->resolveSubjectPartyFields($this->payload(
            donorName: 'Different Spelling',
            donorType: 'individual',
            donorPhone: '+393331112222',
        )));
    }

    public function test_creates_contact_with_email_and_phone_when_new_donor(): void
    {
        $client = $this->createMock(EspoCrmClient::class);
        $client->method('search')
            ->willReturn(['total' => 0, 'list' => []]);

        $resolver = new EspoCrmPartyResolver($client);

        $this->assertSame([
            'subjectName' => 'Anna Bianchi',
            'createSubjectContact' => true,
            'subjectEmailAddress' => 'anna@example.com',
            'subjectPhoneNumber' => '+393331112222',
        ], $resolver->resolveSubjectPartyFields($this->payload(
            donorName: 'Anna Bianchi',
            donorType: 'individual',
            donorEmail: 'anna@example.com',
            donorPhone: '+393331112222',
        )));
    }

    public function test_creates_account_for_new_organization_donor(): void
    {
        $client = $this->createMock(EspoCrmClient::class);
        $client->method('search')
            ->willReturn(['total' => 0, 'list' => []]);

        $resolver = new EspoCrmPartyResolver($client);

        $this->assertSame([
            'subjectName' => 'Acme SRL',
            'createSubjectAccount' => true,
            'subjectEmailAddress' => 'info@acme.example',
        ], $resolver->resolveSubjectPartyFields($this->payload(
            donorName: 'Acme SRL',
            donorType: 'organization',
            donorEmail: 'info@acme.example',
        )));
    }

    public function test_creates_contact_for_new_individual_donor(): void
    {
        $client = $this->createMock(EspoCrmClient::class);
        $client->method('search')
            ->willReturn(['total' => 0, 'list' => []]);

        $resolver = new EspoCrmPartyResolver($client);

        $this->assertSame([
            'subjectName' => 'Anna Bianchi',
            'createSubjectContact' => true,
            'subjectPhoneNumber' => '+393339998877',
        ], $resolver->resolveSubjectPartyFields($this->payload(
            donorName: 'Anna Bianchi',
            donorType: 'individual',
            donorPhone: '+393339998877',
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

    public function test_creates_contact_when_api_user_cannot_read_contacts(): void
    {
        $client = $this->createMock(EspoCrmClient::class);
        $client->method('search')
            ->willReturnCallback(function (): array {
                throw new RuntimeException('EspoCRM API error (No read access.): ""');
            });

        $resolver = new EspoCrmPartyResolver($client);

        $this->assertSame([
            'subjectName' => 'Anna Bianchi',
            'createSubjectContact' => true,
            'subjectEmailAddress' => 'anna@example.com',
        ], $resolver->resolveSubjectPartyFields($this->payload(
            donorName: 'Anna Bianchi',
            donorType: 'individual',
            donorEmail: 'anna@example.com',
        )));
    }

    public function test_subject_rejects_multiple_email_matches(): void
    {
        $client = $this->createMock(EspoCrmClient::class);
        $client->method('search')
            ->willReturnCallback(function (string $entityType, array $params): array {
                if ($entityType === 'Contact' && ($params['where'][0]['attribute'] ?? '') === 'emailAddress') {
                    return [
                        'total' => 2,
                        'list' => [
                            ['id' => 'contact-1', 'name' => 'Mario Rossi'],
                            ['id' => 'contact-2', 'name' => 'Mario R.'],
                        ],
                    ];
                }

                return ['total' => 0, 'list' => []];
            });

        $resolver = new EspoCrmPartyResolver($client);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Multiple EspoCRM Contact records match Soggetto pagamento contact details.');

        $resolver->resolveSubjectPartyFields($this->payload(
            donorName: 'Mario Rossi',
            donorType: 'individual',
            donorEmail: 'mario@example.com',
        ));
    }

    /**
     * @return array<string, mixed>
     */
    private function searchArgs(string $name): array
    {
        return [
            'select' => 'id,name,emailAddress,phoneNumber',
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
        ?string $donorEmail = null,
        ?string $donorPhone = null,
    ): DonationIngestPayload {
        return new DonationIngestPayload(
            provider: 'stripe',
            externalId: 'pi_party_test',
            amountGross: 10,
            commissionAmount: 0,
            commissionPercent: 0,
            netAmount: 10,
            currency: 'EUR',
            campaignTitle: 'Raccolta',
            donorName: $donorName,
            comment: null,
            donorType: $donorType,
            donatedAt: now()->toIso8601String(),
            donorEmail: $donorEmail,
            donorPhone: $donorPhone,
        );
    }
}
