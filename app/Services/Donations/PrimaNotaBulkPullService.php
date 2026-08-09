<?php

namespace App\Services\Donations;

use App\Exceptions\UnsupportedCurrencyException;
use App\Services\Payments\StripePaymentService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Bulk backfill PrimaNota from payment providers (Stripe first).
 * Invoked by CRM via shared sync token — Stripe secrets stay on the site.
 */
class PrimaNotaBulkPullService
{
    public const PROVIDER_STRIPE = 'Stripe';

    /** Future providers — accepted in UI but not implemented yet. */
    public const PROVIDERS_RESERVED = [
        'Satispay',
        'Revolut',
        'BankTransfer',
        'BankApp',
    ];

    public function __construct(
        private readonly StripePaymentService $stripePaymentService,
        private readonly DonationIngestPayloadMapper $payloadMapper,
        private readonly DonationIngestService $donationIngestService,
        private readonly PrimaNotaPaymentStatusService $paymentStatusService,
    ) {}

    /**
     * @param  list<string>  $providers
     * @param  list<string>|null  $currencies  Uppercase ISO codes; null/empty → EUR only
     * @return array{
     *     providers: list<string>,
     *     currencies: list<string>,
     *     mode: string,
     *     fromDate: ?string,
     *     scanned: int,
     *     created: int,
     *     updated: int,
     *     duplicate: int,
     *     skipped: int,
     *     failed: int,
     *     markedInviato: int,
     *     statusRefreshed: int,
     *     truncated: bool,
     *     errors: list<array{provider: string, externalId: string, message: string}>,
     *     unsupportedProviders: list<string>,
     *     skippedCurrencies: list<string>,
     *     log: list<string>,
     *     nextStartingAfter: ?string
     * }
     */
    public function pull(
        array $providers,
        string $mode,
        ?string $fromDate,
        int $maxItems = 200,
        ?array $currencies = null,
        ?string $startingAfter = null,
    ): array {
        $providers = array_values(array_unique(array_filter(
            array_map(static fn ($p) => trim((string) $p), $providers),
            static fn (string $p) => $p !== ''
        )));

        if ($providers === []) {
            throw new \InvalidArgumentException('Select at least one payment provider.');
        }

        $selectedCurrencies = $this->normalizeCurrencyList($currencies);
        if ($selectedCurrencies === []) {
            $selectedCurrencies = ['EUR'];
        }

        $mode = $mode === 'from_date' ? 'from_date' : 'all';
        $fromDateNormalized = null;
        $createdGte = null;

        if ($mode === 'from_date') {
            $fromDateNormalized = trim((string) $fromDate);
            if ($fromDateNormalized === '' || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $fromDateNormalized)) {
                throw new \InvalidArgumentException('fromDate must be YYYY-MM-DD when mode is from_date.');
            }
            $createdGte = Carbon::createFromFormat('Y-m-d', $fromDateNormalized, 'UTC')
                ->startOfDay()
                ->timestamp;
        }

        $maxItems = max(1, min(500, $maxItems));

        $result = [
            'providers' => $providers,
            'currencies' => $selectedCurrencies,
            'mode' => $mode,
            'fromDate' => $fromDateNormalized,
            'scanned' => 0,
            'created' => 0,
            'updated' => 0,
            'duplicate' => 0,
            'restored' => 0,
            'skipped' => 0,
            'failed' => 0,
            'markedInviato' => 0,
            'statusRefreshed' => 0,
            'truncated' => false,
            'errors' => [],
            'unsupportedProviders' => [],
            'skippedCurrencies' => [],
            'log' => [],
            'nextStartingAfter' => null,
        ];

        $startingAfter = is_string($startingAfter) ? trim($startingAfter) : '';
        if ($startingAfter === '') {
            $startingAfter = null;
        }

        $this->logStep(
            $result,
            'START providers=['.implode(',', $providers).'] currencies=['.implode(',', $selectedCurrencies).']'
            .' mode='.$mode
            .($fromDateNormalized ? ' fromDate='.$fromDateNormalized : '')
            .' maxItems='.$maxItems
            .($startingAfter ? ' startingAfter='.$startingAfter : '')
        );

        foreach ($providers as $provider) {
            if ($provider === self::PROVIDER_STRIPE) {
                $this->pullStripe($createdGte, $maxItems, $selectedCurrencies, $result, $startingAfter);
                $this->syncStatusesAfterStripePull($result);

                continue;
            }

            if (in_array($provider, self::PROVIDERS_RESERVED, true)) {
                $result['unsupportedProviders'][] = $provider;
                $this->logStep($result, "SKIP provider={$provider} (not implemented yet)");

                continue;
            }

            throw new \InvalidArgumentException("Unknown payment provider: {$provider}");
        }

        $this->logStep(
            $result,
            'DONE scanned='.$result['scanned']
            .' created='.$result['created']
            .' updated='.$result['updated']
            .' duplicate='.$result['duplicate']
            .' skipped='.$result['skipped']
            .' failed='.$result['failed']
            .' markedInviato='.$result['markedInviato']
            .' payoutsScanned='.$result['statusRefreshed']
            .' truncated='.($result['truncated'] ? 'yes' : 'no')
        );

        return $result;
    }

    /**
     * @param  array{log: list<string>}  $result
     */
    private function logStep(array &$result, string $message): void
    {
        $line = '['.Carbon::now('UTC')->format('H:i:s').'Z] '.$message;
        $result['log'][] = $line;

        // Cap in-memory log for very large runs (UI still gets useful tail).
        if (count($result['log']) > 400) {
            $result['log'] = array_merge(
                array_slice($result['log'], 0, 20),
                ['… log truncated …'],
                array_slice($result['log'], -300)
            );
        }
    }

    /**
     * @param  list<string>|null  $currencies
     * @return list<string>
     */
    private function normalizeCurrencyList(?array $currencies): array
    {
        if ($currencies === null) {
            return [];
        }

        $out = [];
        foreach ($currencies as $currency) {
            $code = strtoupper(trim((string) $currency));
            if ($code !== '' && preg_match('/^[A-Z]{3}$/', $code) && ! in_array($code, $out, true)) {
                $out[] = $code;
            }
        }

        return $out;
    }

    /**
     * @param  array{
     *     scanned: int,
     *     created: int,
     *     updated: int,
     *     duplicate: int,
     *     skipped: int,
     *     failed: int,
     *     markedInviato: int,
     *     statusRefreshed: int,
     *     truncated: bool,
     *     errors: list<array{provider: string, externalId: string, message: string}>,
     *     skippedCurrencies: list<string>,
     *     log: list<string>
     * }  $result
     */
    private function noteSkippedCurrency(array &$result, string $currency): void
    {
        $currency = strtoupper(trim($currency));
        if ($currency === '') {
            $currency = '?';
        }

        if (! in_array($currency, $result['skippedCurrencies'], true)) {
            $result['skippedCurrencies'][] = $currency;
        }
    }

    private function isCurrencyValidationFailure(Throwable $exception): bool
    {
        if ($exception instanceof UnsupportedCurrencyException) {
            return true;
        }

        return str_contains($exception->getMessage(), 'validCurrency');
    }

    /**
     * @param  list<string>  $selectedCurrencies
     * @param  array{
     *     scanned: int,
     *     created: int,
     *     updated: int,
     *     duplicate: int,
     *     skipped: int,
     *     failed: int,
     *     markedInviato: int,
     *     statusRefreshed: int,
     *     truncated: bool,
     *     errors: list<array{provider: string, externalId: string, message: string}>,
     *     skippedCurrencies: list<string>,
     *     log: list<string>
     * }  $result
     */
    private function pullStripe(
        ?int $createdGte,
        int $maxItems,
        array $selectedCurrencies,
        array &$result,
        ?string $startingAfter = null,
    ): void {
        $this->logStep($result, 'STRIPE request list PaymentIntents (succeeded only will be ingested)');

        $processed = 0;
        $pageIndex = 0;
        $lastExternalId = null;

        while ($processed < $maxItems) {
            $pageLimit = min(100, $maxItems - $processed);
            $pageIndex++;

            try {
                $page = $this->stripePaymentService->listPaymentIntentsPage($createdGte, $startingAfter, $pageLimit);
            } catch (Throwable $exception) {
                $this->logStep($result, 'STRIPE ERROR list PaymentIntents page='.$pageIndex.': '.$exception->getMessage());
                throw $exception;
            }

            $items = $page['items'];
            $hasMore = (bool) ($page['has_more'] ?? false);
            $this->logStep(
                $result,
                'STRIPE response page='.$pageIndex.' items='.count($items).' hasMore='.($hasMore ? 'yes' : 'no')
                .($startingAfter ? ' after='.$startingAfter : '')
            );

            if ($items === []) {
                break;
            }

            foreach ($items as $intent) {
                if ($processed >= $maxItems) {
                    $result['truncated'] = true;
                    break 2;
                }

                $result['scanned']++;
                $processed++;
                $externalId = (string) ($intent->id ?? '');
                if ($externalId !== '') {
                    $lastExternalId = $externalId;
                }

                if ($externalId === '') {
                    $result['skipped']++;
                    $this->logStep($result, 'SKIP empty PaymentIntent id');

                    continue;
                }

                if (($intent->status ?? '') !== 'succeeded') {
                    $result['skipped']++;
                    $this->logStep($result, "SKIP {$externalId} status=".((string) ($intent->status ?? '')));

                    continue;
                }

                $intentCurrency = strtoupper(trim((string) ($intent->currency ?? '')));
                if ($intentCurrency !== '' && ! in_array($intentCurrency, $selectedCurrencies, true)) {
                    $result['skipped']++;
                    $this->noteSkippedCurrency($result, $intentCurrency);
                    $this->logStep($result, "SKIP {$externalId} currency={$intentCurrency} (not selected)");

                    continue;
                }

                try {
                    $payload = null;
                    $payload = $this->payloadMapper->fromPaymentIntent($intent);

                    $payloadCurrency = strtoupper(trim((string) $payload->currency));
                    if ($payloadCurrency !== '' && ! in_array($payloadCurrency, $selectedCurrencies, true)) {
                        $result['skipped']++;
                        $this->noteSkippedCurrency($result, $payloadCurrency);
                        $this->logStep($result, "SKIP {$externalId} currency={$payloadCurrency} (not selected after map)");

                        continue;
                    }

                    $this->logStep($result, "INGEST request {$externalId}");
                    $ingest = $this->donationIngestService->ingest($payload, [
                        'bulkSkipForceResync' => true,
                    ]);
                    $status = (string) ($ingest['status'] ?? '');
                    $primaNotaId = (string) ($ingest['prima_nota_id'] ?? '');

                    if ($status === 'created') {
                        $result['created']++;
                    } elseif ($status === 'restored') {
                        $result['created']++;
                        $result['restored']++;
                    } elseif ($status === 'updated') {
                        $result['updated']++;
                    } elseif ($status === 'duplicate') {
                        $result['duplicate']++;
                    } else {
                        $result['updated']++;
                    }

                    $this->logStep(
                        $result,
                        "INGEST response {$externalId} status={$status}"
                        .($primaNotaId !== '' ? " primaNotaId={$primaNotaId}" : '')
                    );
                } catch (Throwable $exception) {
                    if ($this->isCurrencyValidationFailure($exception)) {
                        $result['skipped']++;
                        $currency = '';
                        if (isset($payload) && is_object($payload) && isset($payload->currency)) {
                            $currency = (string) $payload->currency;
                        } elseif (is_object($intent) && isset($intent->currency)) {
                            $currency = (string) $intent->currency;
                        }
                        $this->noteSkippedCurrency($result, $currency);
                        $this->logStep($result, "SKIP {$externalId} unsupported currency={$currency}: ".$exception->getMessage());
                        Log::info('PrimaNota bulk pull skipped unsupported currency.', [
                            'payment_intent_id' => $externalId,
                            'currency' => $currency,
                            'error' => $exception->getMessage(),
                        ]);

                        continue;
                    }

                    $result['failed']++;
                    if (count($result['errors']) < 25) {
                        $result['errors'][] = [
                            'provider' => self::PROVIDER_STRIPE,
                            'externalId' => $externalId,
                            'message' => $exception->getMessage(),
                        ];
                    }
                    $this->logStep($result, "ERROR ingest {$externalId}: ".$exception->getMessage());
                    Log::warning('PrimaNota bulk pull Stripe item failed.', [
                        'payment_intent_id' => $externalId,
                        'error' => $exception->getMessage(),
                    ]);
                }
            }

            if ($processed >= $maxItems) {
                if ($hasMore || count($items) >= $pageLimit) {
                    $result['truncated'] = true;
                }
                break;
            }

            $last = $items[array_key_last($items)] ?? null;
            $startingAfter = is_object($last) ? (string) ($last->id ?? '') : null;

            if (! $hasMore || $startingAfter === '') {
                break;
            }
        }

        if ($result['truncated'] && is_string($lastExternalId) && $lastExternalId !== '') {
            $result['nextStartingAfter'] = $lastExternalId;
        }

        $this->logStep(
            $result,
            'STRIPE ingest finished scanned='.$result['scanned']
            .' created='.$result['created']
            .' updated='.$result['updated']
            .' duplicate='.$result['duplicate']
            .' skipped='.$result['skipped']
            .' failed='.$result['failed']
            .($result['nextStartingAfter'] ? ' nextStartingAfter='.$result['nextStartingAfter'] : '')
        );
    }

    /**
     * One batch pass over recent paid Stripe payouts → Inviato (avoids N× reverse-scans).
     * Import must wait for this before returning success to CRM.
     *
     * @param  array{markedInviato: int, statusRefreshed: int, log: list<string>}  $result
     */
    private function syncStatusesAfterStripePull(array &$result): void
    {
        $this->logStep($result, 'STATUS sync start: match Planned Stripe rows to recent paid automatic payouts');

        try {
            $sync = $this->paymentStatusService->syncInviatoFromRecentPaidPayouts(40);
            $result['statusRefreshed'] += (int) ($sync['payoutsScanned'] ?? 0);
            $result['markedInviato'] += (int) ($sync['markedInviato'] ?? 0);
            $this->logStep(
                $result,
                'STATUS sync done plannedLoaded='.((int) ($sync['plannedLoaded'] ?? 0))
                .' payoutsScanned='.((int) ($sync['payoutsScanned'] ?? 0))
                .' markedInviato='.((int) ($sync['markedInviato'] ?? 0))
            );
        } catch (Throwable $exception) {
            $this->logStep($result, 'STATUS sync ERROR: '.$exception->getMessage());
            Log::warning('PrimaNota bulk pull batch status sync failed.', [
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
