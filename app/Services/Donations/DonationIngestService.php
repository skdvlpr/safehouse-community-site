<?php

namespace App\Services\Donations;

use App\DataTransferObjects\DonationIngestPayload;
use App\Services\EspoCrm\EspoCrmClient;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class DonationIngestService
{
    public function __construct(
        private readonly EspoCrmClient $client,
    ) {}

    /**
     * @return array{status: string, prima_nota_id: string, financing_id: string}
     */
    public function ingest(DonationIngestPayload $payload): array
    {
        $existingId = $this->findPrimaNotaIdByExternalId($payload->idempotencySearchValue());
        if ($existingId !== null) {
            return [
                'status' => 'duplicate',
                'prima_nota_id' => $existingId,
                'financing_id' => '',
            ];
        }

        $financingId = $this->resolveFinanziamentoId($payload->campaignTitle);

        $createPayload = [
            'description' => $payload->primaNotaDescription(),
            'entryType' => (string) config('espocrm.prima_nota.entry_type'),
            'amount' => $payload->amount,
            'amountCurrency' => $payload->currency,
            'internalClassification' => (string) config('espocrm.prima_nota.internal_classification'),
            'transactionDate' => Carbon::parse($payload->donatedAt)->toDateString(),
            'subjectName' => $payload->subjectName(),
            'beneficiaryName' => $payload->beneficiaryName(),
            'financingId' => $financingId,
        ];

        $assignedUserId = (string) config('espocrm.assigned_user_id', '');
        if ($assignedUserId !== '') {
            $createPayload['assignedUserId'] = $assignedUserId;
        } else {
            Log::warning('EspoCRM assignedUserId is not configured; PrimaNota will use CRM defaults.', [
                'external_id' => $payload->externalId,
            ]);
        }

        $primaNota = $this->client->create(
            (string) config('espocrm.prima_nota.entity'),
            $createPayload,
        );

        return [
            'status' => 'created',
            'prima_nota_id' => (string) ($primaNota['id'] ?? ''),
            'financing_id' => $financingId,
        ];
    }

    private function findPrimaNotaIdByExternalId(string $externalId): ?string
    {
        $result = $this->client->search((string) config('espocrm.prima_nota.entity'), [
            'select' => 'id,description',
            'maxSize' => 1,
            'where' => [
                [
                    'type' => 'contains',
                    'attribute' => 'description',
                    'value' => $externalId,
                ],
            ],
        ]);

        $id = $result['list'][0]['id'] ?? null;

        return is_string($id) && $id !== '' ? $id : null;
    }

    private function resolveFinanziamentoId(string $campaignTitle): string
    {
        $entity = (string) config('espocrm.finanziamento.entity');

        $existing = $this->client->search($entity, [
            'select' => 'id,name,stage',
            'maxSize' => 5,
            'where' => [
                [
                    'type' => 'equals',
                    'attribute' => 'name',
                    'value' => $campaignTitle,
                ],
            ],
        ]);

        $matches = $existing['list'] ?? [];
        $count = is_array($matches) ? count($matches) : 0;

        if ($count === 0) {
            Log::error('EspoCRM Finanziamento not found for donation campaign.', [
                'campaign_title' => $campaignTitle,
            ]);

            throw new RuntimeException(
                "EspoCRM Finanziamento not found for campaign name \"{$campaignTitle}\"."
            );
        }

        if ($count > 1) {
            Log::error('Multiple EspoCRM Finanziamenti matched donation campaign name.', [
                'campaign_title' => $campaignTitle,
                'match_count' => $count,
            ]);

            throw new RuntimeException(
                "Multiple EspoCRM Finanziamenti match campaign name \"{$campaignTitle}\"."
            );
        }

        $existingId = $matches[0]['id'] ?? null;
        if (! is_string($existingId) || $existingId === '') {
            throw new RuntimeException('EspoCRM Finanziamento search returned an invalid id.');
        }

        return $existingId;
    }
}
