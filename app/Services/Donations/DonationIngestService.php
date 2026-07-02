<?php

namespace App\Services\Donations;

use App\DataTransferObjects\DonationIngestPayload;
use App\Services\EspoCrm\EspoCrmClient;
use App\Services\EspoCrm\EspoCrmFinanziamentoService;
use App\Services\EspoCrm\EspoCrmPartyResolver;
use App\Support\IntegrationConfig;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class DonationIngestService
{
    public function __construct(
        private readonly EspoCrmClient $client,
        private readonly EspoCrmPartyResolver $partyResolver,
        private readonly EspoCrmFinanziamentoService $finanziamentoService,
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

        $financingId = $this->finanziamentoService->ensureExists(
            $payload->campaignTitle,
            amount: (float) config('espocrm.finanziamento.default_amount', 0),
            currency: $payload->currency,
        );

        $createPayload = array_merge(
            [
                'entryType' => (string) config('espocrm.prima_nota.entry_type'),
                'amount' => $payload->amount,
                'amountCurrency' => $payload->currency,
                'internalClassification' => (string) config('espocrm.prima_nota.internal_classification'),
                'transactionDate' => Carbon::parse($payload->donatedAt)->toDateString(),
                'financingId' => $financingId,
            ],
            $payload->primaNotaDonationFields(),
            $this->partyResolver->resolveSubjectPartyFields($payload),
            $this->partyResolver->resolveBeneficiaryPartyFields($payload),
        );

        $assignedUserId = IntegrationConfig::string('espocrm.assigned_user_id');
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
            'select' => 'id,donationPaymentReference',
            'maxSize' => 1,
            'where' => [
                [
                    'type' => 'contains',
                    'attribute' => 'donationPaymentReference',
                    'value' => $externalId,
                ],
            ],
        ]);

        $id = $result['list'][0]['id'] ?? null;

        return is_string($id) && $id !== '' ? $id : null;
    }
}
