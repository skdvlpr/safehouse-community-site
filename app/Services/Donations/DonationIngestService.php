<?php

namespace App\Services\Donations;

use App\DataTransferObjects\DonationIngestPayload;
use App\Exceptions\UnsupportedCurrencyException;
use App\Services\EspoCrm\EspoCrmClient;
use App\Services\EspoCrm\EspoCrmFinanziamentoService;
use App\Services\EspoCrm\EspoCrmPartyResolver;
use App\Services\Payments\StripePaymentService;
use App\Support\IntegrationConfig;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class DonationIngestService
{
    public function __construct(
        private readonly EspoCrmClient $client,
        private readonly EspoCrmPartyResolver $partyResolver,
        private readonly EspoCrmFinanziamentoService $finanziamentoService,
        private readonly StripePaymentService $stripePaymentService,
    ) {}

    /**
     * @param  array{bulkSkipForceResync?: bool}  $options
     * @return array{status: string, prima_nota_id: string, financing_id: string}
     */
    public function ingest(DonationIngestPayload $payload, array $options = []): array
    {
        $lockKey = 'donation-ingest:'.sha1($payload->idempotencySearchValue());

        try {
            return Cache::lock($lockKey, 30)->block(15, function () use ($payload, $options): array {
                return $this->ingestUnderLock($payload, $options);
            });
        } catch (LockTimeoutException $exception) {
            $lookup = $this->findOrRestorePrimaNotaByExternalId($payload->idempotencySearchValue());
            $existing = $lookup['row'];
            if ($existing !== null) {
                return [
                    'status' => $lookup['restored'] ? 'restored' : 'duplicate',
                    'prima_nota_id' => (string) ($existing['id'] ?? ''),
                    'financing_id' => (string) ($existing['financingId'] ?? ''),
                ];
            }

            throw new RuntimeException(
                'Donation ingest lock timeout for '.$payload->externalId,
                0,
                $exception,
            );
        }
    }

    /**
     * @param  array{bulkSkipForceResync?: bool}  $options
     * @return array{status: string, prima_nota_id: string, financing_id: string}
     */
    private function ingestUnderLock(DonationIngestPayload $payload, array $options = []): array
    {
        $this->assertCurrencyAllowed($payload);

        $lookup = $this->findOrRestorePrimaNotaByExternalId($payload->idempotencySearchValue());
        $existing = $lookup['row'];
        $justRestored = $lookup['restored'];
        if ($existing !== null) {
            $existingId = (string) ($existing['id'] ?? '');
            if ($existingId !== '' && strtolower($payload->provider) === 'stripe') {
                $existingChargeId = trim((string) ($existing['stripeChargeId'] ?? ''));
                if (! empty($options['bulkSkipForceResync']) && $existingChargeId !== '' && ! $justRestored) {
                    return [
                        'status' => 'duplicate',
                        'prima_nota_id' => $existingId,
                        'financing_id' => (string) ($existing['financingId'] ?? ''),
                    ];
                }

                // Keep CRM money + Stripe enrichment current on every webhook/ingest hit.
                $resyncPayload = $this->forceResyncStripeSnapshotPayload($payload);
                if ($resyncPayload !== []) {
                    try {
                        $this->client->update(
                            (string) config('espocrm.prima_nota.entity'),
                            $existingId,
                            $resyncPayload,
                        );
                    } catch (\Throwable $exception) {
                        Log::warning('Stripe PrimaNota force resync failed on duplicate ingest.', [
                            'prima_nota_id' => $existingId,
                            'error' => $exception->getMessage(),
                        ]);
                    }
                }

                $this->syncStripeCrmLink($payload, $existingId);

                return [
                    'status' => $justRestored ? 'restored' : 'updated',
                    'prima_nota_id' => $existingId,
                    'financing_id' => (string) ($existing['financingId'] ?? ''),
                ];
            }

            if ($existingId !== '' && $this->shouldBackfillIncompleteStripeRow($existing, $payload)) {
                $existingChargeId = trim((string) ($existing['stripeChargeId'] ?? ''));
                $updatePayload = $existingChargeId === ''
                    ? $this->settlementAndEnrichmentPayload($payload)
                    : $this->gapFillEnrichmentPayload($existing, $payload);

                if ($updatePayload !== []) {
                    $this->client->update(
                        (string) config('espocrm.prima_nota.entity'),
                        $existingId,
                        $updatePayload,
                    );
                }

                $this->syncStripeCrmLink($payload, $existingId);

                return [
                    'status' => 'backfilled',
                    'prima_nota_id' => $existingId,
                    'financing_id' => (string) ($existing['financingId'] ?? ''),
                ];
            }

            if ($existingId !== '') {
                $this->syncStripeCrmLink($payload, $existingId);
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

        $primaNotaId = (string) ($primaNota['id'] ?? '');
        $this->syncStripeCrmLink($payload, $primaNotaId);

        return [
            'status' => 'created',
            'prima_nota_id' => $primaNotaId,
            'financing_id' => $financingId,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    /**
     * @return array{row: ?array<string, mixed>, restored: bool}
     */
    private function findOrRestorePrimaNotaByExternalId(string $externalId): array
    {
        $reference = str_starts_with($externalId, '#') ? $externalId : '#'.$externalId;
        $existing = $this->findLivePrimaNotaByReference($reference);
        if ($existing !== null) {
            return ['row' => $existing, 'restored' => false];
        }

        // Soft-deleted rows are invisible to list API; CRM restores via ORM withDeleted.
        try {
            $restored = $this->client->action(
                (string) config('espocrm.prima_nota.entity'),
                'restoreByDonationPaymentReference',
                ['donationPaymentReference' => $reference],
            );
        } catch (\Throwable $exception) {
            // NotFound / no soft-deleted match → fall through to create path.
            Log::info('PrimaNota soft-delete restore lookup miss.', [
                'reference' => $reference,
                'error' => $exception->getMessage(),
            ]);

            return ['row' => null, 'restored' => false];
        }

        $id = trim((string) ($restored['id'] ?? ''));
        $wasRestored = ! empty($restored['restored']);
        if ($id === '') {
            return ['row' => null, 'restored' => false];
        }

        $row = $this->findLivePrimaNotaByReference($reference);
        if ($row === null) {
            $row = [
                'id' => $id,
                'donationPaymentReference' => $reference,
                'financingId' => $restored['financingId'] ?? null,
            ];
        }

        if ($wasRestored) {
            Log::info('Restored soft-deleted PrimaNota for donation ingest.', [
                'reference' => $reference,
                'prima_nota_id' => $id,
            ]);
        }

        return ['row' => $row, 'restored' => $wasRestored];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function findLivePrimaNotaByReference(string $reference): ?array
    {
        $result = $this->client->search((string) config('espocrm.prima_nota.entity'), [
            'select' => 'id,donationPaymentReference,financingId,stripeChargeId,commissionAmount,amount,amountGross,stripeBillingEmail,stripeReceiptEmail,stripeBillingPhone,subjectEmailAddress,subjectPhoneNumber',
            'maxSize' => 5,
            'where' => [
                [
                    'type' => 'equals',
                    'attribute' => 'donationPaymentReference',
                    'value' => $reference,
                ],
            ],
        ]);

        $row = $result['list'][0] ?? null;

        return is_array($row) ? $row : null;
    }

    /**
     * @deprecated Use findOrRestorePrimaNotaByExternalId
     * @return array<string, mixed>|null
     */
    private function findPrimaNotaByExternalId(string $externalId): ?array
    {
        return $this->findOrRestorePrimaNotaByExternalId($externalId)['row'];
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
        if ($existingChargeId === '') {
            $incomingChargeId = trim((string) ($payload->stripeEnrichment?->stripeChargeId ?? ''));
            if ($incomingChargeId !== '') {
                return true;
            }

            // Fee arrived later (BalanceTransaction) even if charge id still missing.
            return $payload->commissionAmount > 0
                && (float) ($existing['commissionAmount'] ?? 0) <= 0;
        }

        return $this->shouldGapFillStripeEnrichment($existing, $payload);
    }

    /**
     * After charge id is set, still allow PUT when enrichment/channel snapshot
     * fields are empty in CRM but present on the Stripe payload.
     *
     * @param  array<string, mixed>  $existing
     */
    private function shouldGapFillStripeEnrichment(array $existing, DonationIngestPayload $payload): bool
    {
        $incoming = $payload->stripeEnrichment?->toPrimaNotaFields() ?? [];

        foreach ([
            'stripeBillingEmail',
            'stripeReceiptEmail',
            'stripeBillingPhone',
            'stripeReceiptUrl',
            'stripePaymentMethodType',
            'stripeBalanceTransactionId',
            'stripeStatementDescriptor',
            'stripeRadarRiskLevel',
        ] as $field) {
            if (! array_key_exists($field, $existing)) {
                continue;
            }
            $have = trim((string) ($existing[$field] ?? ''));
            $want = trim((string) ($incoming[$field] ?? ''));
            if ($have === '' && $want !== '') {
                return true;
            }
        }

        if (
            array_key_exists('subjectEmailAddress', $existing)
            && trim((string) ($existing['subjectEmailAddress'] ?? '')) === ''
            && $payload->donorEmail !== null
            && trim($payload->donorEmail) !== ''
        ) {
            return true;
        }

        if (
            array_key_exists('subjectPhoneNumber', $existing)
            && trim((string) ($existing['subjectPhoneNumber'] ?? '')) === ''
            && $payload->donorPhone !== null
            && trim($payload->donorPhone) !== ''
        ) {
            return true;
        }

        return false;
    }

    /**
     * Only attributes that are empty on the existing row and present on the payload.
     * Never re-sends money/settlement fields once charge id exists.
     *
     * @param  array<string, mixed>  $existing
     * @return array<string, mixed>
     */
    private function gapFillEnrichmentPayload(array $existing, DonationIngestPayload $payload): array
    {
        $fields = [];
        $incoming = $payload->stripeEnrichment?->toPrimaNotaFields() ?? [];

        // Only fields we selected on the existing row — never "fill" attrs we did not load
        // (missing key looks blank and would re-PUT already-set Stripe values → CRM lock).
        $candidates = [
            'stripeBillingEmail',
            'stripeReceiptEmail',
            'stripeBillingPhone',
            'stripeReceiptUrl',
            'stripePaymentMethodType',
            'stripeBalanceTransactionId',
            'stripeStatementDescriptor',
            'stripeRadarRiskLevel',
            'stripeInvoiceId',
            'stripeInvoiceNumber',
        ];

        foreach ($candidates as $field) {
            if (! array_key_exists($field, $existing)) {
                continue;
            }
            $want = $incoming[$field] ?? null;
            if ($this->isBlank($existing[$field] ?? null) && ! $this->isBlank($want)) {
                $fields[$field] = $want;
            }
        }

        if (
            array_key_exists('subjectEmailAddress', $existing)
            && $this->isBlank($existing['subjectEmailAddress'] ?? null)
            && $payload->donorEmail !== null
            && trim($payload->donorEmail) !== ''
        ) {
            $fields['subjectEmailAddress'] = trim($payload->donorEmail);
        }

        if (
            array_key_exists('subjectPhoneNumber', $existing)
            && $this->isBlank($existing['subjectPhoneNumber'] ?? null)
            && $payload->donorPhone !== null
            && trim($payload->donorPhone) !== ''
        ) {
            $fields['subjectPhoneNumber'] = trim($payload->donorPhone);
        }

        return $fields;
    }

    private function isBlank(mixed $value): bool
    {
        if ($value === null) {
            return true;
        }

        if (is_string($value)) {
            return trim($value) === '';
        }

        return false;
    }

    /**
     * Full settlement + enrichment overwrite for an existing Stripe PrimaNota.
     * Does NOT touch paymentStatus (status machine owns that).
     *
     * @return array<string, mixed>
     */
    public function forceResyncStripeSnapshotPayload(DonationIngestPayload $payload): array
    {
        if (strtolower($payload->provider) !== 'stripe') {
            return [];
        }

        $fields = array_merge(
            [
                'amount' => $payload->netAmount,
                'amountCurrency' => $payload->currency,
                'amountGross' => $payload->amountGross,
                'amountGrossCurrency' => $payload->currency,
                'commissionAmount' => $payload->commissionAmount,
                'commissionAmountCurrency' => $payload->currency,
                'commissionPercent' => $payload->commissionPercent,
                'transactionDate' => Carbon::parse($payload->donatedAt)->toDateString(),
                'donationFrequency' => $payload->normalizedDonationFrequency(),
            ],
            $payload->stripeEnrichment?->toPrimaNotaFields() ?? [],
        );

        $subjectName = trim($payload->subjectName());
        if ($subjectName !== '') {
            $fields['subjectName'] = $subjectName;
        }

        if ($payload->comment !== null && trim($payload->comment) !== '') {
            $fields['donationComment'] = trim($payload->comment);
        }

        $donorCategory = $payload->donorTypeLabel();
        if ($donorCategory !== '') {
            $fields['donationDonorCategory'] = $donorCategory;
        }

        if ($payload->donorEmail !== null && trim($payload->donorEmail) !== '') {
            $fields['subjectEmailAddress'] = trim($payload->donorEmail);
        }
        if ($payload->donorPhone !== null && trim($payload->donorPhone) !== '') {
            $fields['subjectPhoneNumber'] = trim($payload->donorPhone);
        }

        return $fields;
    }

    private function assertCurrencyAllowed(DonationIngestPayload $payload): void
    {
        $currency = strtoupper(trim($payload->currency));
        $allowed = array_values(array_filter(array_map(
            static fn ($c): string => strtoupper(trim((string) $c)),
            (array) config('espocrm.allowed_currencies', ['EUR'])
        )));

        if ($allowed === []) {
            $allowed = ['EUR'];
        }

        if ($currency === '' || ! in_array($currency, $allowed, true)) {
            throw new UnsupportedCurrencyException(
                "Currency {$currency} is not enabled in CRM (allowed: ".implode(', ', $allowed).').'
            );
        }
    }

    /**
     * Best-effort: stamp Stripe PI description + CRM deep link after ingest.
     */
    private function syncStripeCrmLink(DonationIngestPayload $payload, string $primaNotaId): void
    {
        if ($primaNotaId === '' || strtolower($payload->provider) !== 'stripe') {
            return;
        }

        $piId = ltrim($payload->externalId, '#');
        if ($piId === '' || ! str_starts_with($piId, 'pi_')) {
            return;
        }

        try {
            $this->stripePaymentService->attachPrimaNotaLinkToPaymentIntent($piId, $primaNotaId);
        } catch (\Throwable $exception) {
            Log::warning('Stripe CRM link sync failed after PrimaNota ingest.', [
                'payment_intent_id' => $piId,
                'prima_nota_id' => $primaNotaId,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function settlementAndEnrichmentPayload(DonationIngestPayload $payload): array
    {
        $fields = array_merge(
            $this->forceResyncStripeSnapshotPayload($payload),
            [
                'paymentStatus' => 'Planned',
            ],
        );

        return $fields;
    }
}
