<?php

namespace App\Services\EspoCrm;

use App\DataTransferObjects\DonationIngestPayload;
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
            createAccountFlag: 'createSubjectAccount',
            createContactFlag: 'createSubjectContact',
            label: 'Soggetto pagamento',
        );
    }

    /**
     * @return array<string, bool|string>
     */
    public function resolveBeneficiaryPartyFields(DonationIngestPayload $payload): array
    {
        $entityType = (string) config('espocrm.prima_nota.beneficiary_party_entity', 'Account');

        return $this->resolvePartyFields(
            prefix: 'beneficiary',
            entityType: $entityType,
            name: $payload->beneficiaryName(),
            createAccountFlag: 'createBeneficiaryAccount',
            createContactFlag: 'createBeneficiaryContact',
            label: 'Beneficiario',
        );
    }

    /**
     * @return array<string, bool|string>
     */
    private function resolvePartyFields(
        string $prefix,
        string $entityType,
        string $name,
        string $createAccountFlag,
        string $createContactFlag,
        string $label,
    ): array {
        $matches = $this->findByExactName($entityType, $name);
        $count = count($matches);

        if ($count > 1) {
            Log::error("Multiple EspoCRM {$entityType} records matched {$label} name.", [
                'name' => $name,
                'match_count' => $count,
            ]);

            throw new RuntimeException(
                "Multiple EspoCRM {$entityType} records match {$label} name \"{$name}\"."
            );
        }

        if ($count === 1) {
            return [
                $prefix.'PartyId' => $matches[0]['id'],
                $prefix.'PartyType' => $entityType,
            ];
        }

        if ($entityType === 'Account') {
            return [
                $prefix.'Name' => $name,
                $createAccountFlag => true,
            ];
        }

        return [
            $prefix.'Name' => $name,
            $createContactFlag => true,
        ];
    }

    /**
     * @return list<array{id: string}>
     */
    private function findByExactName(string $entityType, string $name): array
    {
        $result = $this->client->search($entityType, [
            'select' => 'id,name',
            'maxSize' => 5,
            'where' => [
                [
                    'type' => 'equals',
                    'attribute' => 'name',
                    'value' => $name,
                ],
            ],
        ]);

        $list = $result['list'] ?? [];

        if (! is_array($list)) {
            return [];
        }

        return array_values(array_filter(array_map(function ($row): ?array {
            if (! is_array($row)) {
                return null;
            }

            $id = $row['id'] ?? null;

            return is_string($id) && $id !== '' ? ['id' => $id] : null;
        }, $list)));
    }
}
