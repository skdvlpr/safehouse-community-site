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
        $existing = $this->findPrimaNotaByExternalId($payload->idempotencySearchValue());
        if ($existing !== null) {
            $existingId = (string) ($existing['id'] ?? '');
            if ($existingId !== '' && $this->shouldBackfillIncompleteStripeRow($existing, $payload)) {
                $this->client->update(
                    (string) config('espocrm.prima_nota.entity'),
                    $existingId,
                    $this->settlementAndEnrichmentPayload($payload),
                );

                return [
                    'status' => 'backfilled',
                    'prima_nota_id' => $existingId,
                    'financing_id' => (string) ($existing['financingId'] ?? ''),
                ];
            }

            return [
                'status' => 'duplicate',
                'prima_nota_id' => $existingId,
                'financing_id' => '',
            ];
        }

        $financingId = $this->finanziamentoService->ensureExists(
            $payload->campaignTitle,
            amount: $payload->financingGoalAmount ?? (float) config('espocrm.finanziamento.default_amount', 0),
            currency: $payload->currency,
        );

        $createPayload = array_merge(
            [
                'entryType' => (string) config('espocrm.prima_nota.entry_type'),
                'amount' => $payload->netAmount,
                'amountCurrency' => $payload->currency,
                'amountGross' => $payload->amountGross,
                'amountGrossCurrency' => $payload->currency,
                'commissionAmount' => $payload->commissionAmount,
                'commissionAmountCurrency' => $payload->currency,
                'commissionPercent' => $payload->commissionPercent,
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

    /**
     * @return array<string, mixed>|null
     */
    private function findPrimaNotaByExternalId(string $externalId): ?array
    {
        $result = $this->client->search((string) config('espocrm.prima_nota.entity'), [
            'select' => 'id,donationPaymentReference,financingId,stripeChargeId,commissionAmount,amount,amountGross',
            'maxSize' => 1,
            'where' => [
                [
                    'type' => 'contains',
                    'attribute' => 'donationPaymentReference',
                    'value' => $externalId,
                ],
            ],
        ]);

        $row = $result['list'][0] ?? null;

        return is_array($row) ? $row : null;
    }

    /**
     * @param  array<string, mixed>  $existing
     */
    private function shouldBackfillIncompleteStripeRow(array $existing, DonationIngestPayload $payload): bool
    {
        if (strtolower($payload->provider) !== 'stripe') {
            return false;
        }

        $existingChargeId = trim((string) ($existing['stripeChargeId'] ?? ''));
        if ($existingChargeId !== '') {
            return false;
        }

        $incomingChargeId = trim((string) ($payload->stripeEnrichment?->stripeChargeId ?? ''));
        if ($incomingChargeId !== '') {
            return true;
        }

        // Fee arrived later (BalanceTransaction) even if charge id still missing.
        return $payload->commissionAmount > 0
            && (float) ($existing['commissionAmount'] ?? 0) <= 0;
    }

    /**
     * @return array<string, mixed>
     */
    private function settlementAndEnrichmentPayload(DonationIngestPayload $payload): array
    {
        return array_merge(
            [
                'amount' => $payload->netAmount,
                'amountCurrency' => $payload->currency,
                'amountGross' => $payload->amountGross,
                'amountGrossCurrency' => $payload->currency,
                'commissionAmount' => $payload->commissionAmount,
                'commissionAmountCurrency' => $payload->currency,
                'commissionPercent' => $payload->commissionPercent,
                'paymentStatus' => 'Paid',
            ],
            $payload->stripeEnrichment?->toPrimaNotaFields() ?? [],
        );
    }
}
