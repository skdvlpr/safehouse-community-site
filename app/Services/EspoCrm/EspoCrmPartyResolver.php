<?php

namespace App\Services\EspoCrm;

use App\DataTransferObjects\DonationIngestPayload;
use App\Support\DonorContact;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class EspoCrmPartyResolver
{
    public function __construct(
        private readonly EspoCrmClient $client,
    ) {}

    /**
     * @return array<string, bool|string>
     */
    public function resolveSubjectPartyFields(DonationIngestPayload $payload): array
    {
        $name = $payload->subjectName();
        $entityType = $payload->subjectPartyEntityType();

        return $this->resolvePartyFields(
            prefix: 'subject',
            entityType: $entityType,
            name: $name,
            email: $payload->donorEmail,
            phone: $payload->donorPhone,
            createAccountFlag: 'createSubjectAccount',
            createContactFlag: 'createSubjectContact',
            label: 'Soggetto pagamento',
            matchByContactChannel: true,
            matchByName: false,
        );
    }

    /**
     * @return array<string, bool|string>
     */
    public function resolveBeneficiaryPartyFields(DonationIngestPayload $payload): array
    {
        $entityType = (string) config('espocrm.prima_nota.beneficiary_party_entity', 'Account');
        $name = $payload->beneficiaryName();

        return $this->resolvePartyFields(
            prefix: 'beneficiary',
            entityType: $entityType,
            name: $name,
            email: null,
            phone: null,
            createAccountFlag: 'createBeneficiaryAccount',
            createContactFlag: 'createBeneficiaryContact',
            label: 'Beneficiario',
            matchByContactChannel: false,
            matchByName: true,
            allowDuplicateMatches: true,
        );
    }

    /**
     * @return array<string, bool|string>
     */
    private function resolvePartyFields(
        string $prefix,
        string $entityType,
        string $name,
        ?string $email,
        ?string $phone,
        string $createAccountFlag,
        string $createContactFlag,
        string $label,
        bool $matchByContactChannel,
        bool $matchByName,
        bool $allowDuplicateMatches = false,
    ): array {
        $matches = $this->findMatches(
            entityType: $entityType,
            name: $name,
            email: $email,
            phone: $phone,
            matchByContactChannel: $matchByContactChannel,
            matchByName: $matchByName,
        );
        $count = count($matches);

        if ($count > 1 && $allowDuplicateMatches) {
            Log::warning("Multiple EspoCRM {$entityType} records matched {$label}; using the oldest id.", [
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'match_count' => $count,
                'selected_id' => $matches[0]['id'],
            ]);
            $matches = [$matches[0]];
            $count = 1;
        }

        if ($count > 1) {
            Log::error("Multiple EspoCRM {$entityType} records matched {$label}.", [
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'match_count' => $count,
            ]);

            throw new RuntimeException(
                "Multiple EspoCRM {$entityType} records match {$label} contact details."
            );
        }

        if ($count === 1) {
            return [
                $prefix.'Name' => $name,
                $prefix.'PartyId' => $matches[0]['id'],
                $prefix.'PartyType' => $entityType,
            ];
        }

        if ($entityType === 'Account') {
            return array_filter([
                $prefix.'Name' => $name,
                $createAccountFlag => true,
                $prefix.'EmailAddress' => $email,
                $prefix.'PhoneNumber' => $phone,
            ], fn ($value) => $value !== null && $value !== '');
        }

        return array_filter([
            $prefix.'Name' => $name,
            $createContactFlag => true,
            $prefix.'EmailAddress' => $email,
            $prefix.'PhoneNumber' => $phone,
        ], fn ($value) => $value !== null && $value !== '');
    }

    /**
     * @return list<array{id: string}>
     */
    private function findMatches(
        string $entityType,
        string $name,
        ?string $email,
        ?string $phone,
        bool $matchByContactChannel,
        bool $matchByName,
    ): array {
        if ($matchByContactChannel) {
            if ($email !== null && $email !== '') {
                $matches = $this->findByField($entityType, 'emailAddress', $email);
                if ($matches !== []) {
                    return $matches;
                }
            }

            if ($phone !== null && $phone !== '') {
                $matches = $this->findByPhone($entityType, $phone);
                if ($matches !== []) {
                    return $matches;
                }
            }

            return [];
        }

        if ($matchByName) {
            return $this->findByExactName($entityType, $name);
        }

        return [];
    }

    /**
     * @return list<array{id: string}>
     */
    private function findByPhone(string $entityType, string $e164Phone): array
    {
        $matches = $this->findByField($entityType, 'phoneNumber', $e164Phone);
        if ($matches !== []) {
            return $matches;
        }

        $numeric = DonorContact::phoneNumericKey($e164Phone);
        if ($numeric === '') {
            return [];
        }

        return $this->findByField($entityType, 'phoneNumberNumeric', $numeric);
    }

    /**
     * @return list<array{id: string}>
     */
    private function findByField(string $entityType, string $attribute, string $value): array
    {
        try {
            $result = $this->client->search($entityType, [
                'select' => 'id,name',
                'maxSize' => 5,
                'orderBy' => 'id',
                'order' => 'asc',
                'where' => [
                    [
                        'type' => 'equals',
                        'attribute' => $attribute,
                        'value' => $value,
                    ],
                    [
                        'type' => 'equals',
                        'attribute' => 'deleted',
                        'value' => false,
                    ],
                ],
            ]);
        } catch (RuntimeException $exception) {
            if (str_contains($exception->getMessage(), 'No read access')) {
                Log::warning("EspoCRM API user cannot read {$entityType} by {$attribute}.", [
                    'value' => $value,
                ]);

                return [];
            }

            throw $exception;
        }

        return $this->extractMatches($result);
    }

    /**
     * @param  array<string, mixed>  $result
     * @return list<array{id: string}>
     */
    private function extractMatches(array $result): array
    {
        $list = $result['list'] ?? [];

        if (! is_array($list)) {
            return [];
        }

        $matches = array_values(array_filter(array_map(function ($row): ?array {
            if (! is_array($row)) {
                return null;
            }

            $id = $row['id'] ?? null;

            return is_string($id) && $id !== '' ? ['id' => $id] : null;
        }, $list)));

        return $this->sortMatchesById($matches);
    }

    /**
     * @return list<array{id: string}>
     */
    private function findByExactName(string $entityType, string $name): array
    {
        try {
            $result = $this->client->search($entityType, [
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
            ]);
        } catch (RuntimeException $exception) {
            if (str_contains($exception->getMessage(), 'No read access')) {
                Log::warning("EspoCRM API user cannot read {$entityType}; creating party inline.", [
                    'name' => $name,
                ]);

                return [];
            }

            throw $exception;
        }

        return $this->extractMatches($result);
    }

    /**
     * @param  list<array{id: string}>  $matches
     * @return list<array{id: string}>
     */
    private function sortMatchesById(array $matches): array
    {
        usort($matches, fn (array $a, array $b): int => strcmp($a['id'], $b['id']));

        return $matches;
    }
}
