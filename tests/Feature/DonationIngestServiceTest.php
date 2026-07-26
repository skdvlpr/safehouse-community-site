<?php

namespace Tests\Feature;

use App\DataTransferObjects\DonationIngestPayload;
use App\Models\DonationCampaign;
use App\Services\Donations\DonationIngestService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class DonationIngestServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('espocrm.base_url', 'https://crm.test');
        config()->set('espocrm.api_key', 'test-espo-key');
        config()->set('espocrm.assigned_user_id', 'api-user-id');
        config()->set('espocrm.finanziamento.default_close_date', '2026-12-31');
        config()->set('espocrm.prima_nota.default_subject_name', 'Donatore');
        config()->set('espocrm.prima_nota.default_beneficiary_name', 'Safe House');
    }

    public function test_ingest_creates_prima_nota_for_stripe_payment(): void
    {
        $this->fakeCrmIngest(
            financingId: 'opp-existing',
            financingName: 'Raccolta fondi per Safe House',
            subjectAccountMatches: 0,
            beneficiaryAccountMatches: 1,
            beneficiaryAccountId: 'acc-safe-house',
        );

        $result = $this->ingestSamplePayment();

        $this->assertSame('created', $result['status']);
        $this->assertSame('pn-new', $result['prima_nota_id']);
        $this->assertSame('opp-existing', $result['financing_id']);
    }

    public function test_ingest_posts_party_linked_prima_nota_fields_to_crm(): void
    {
        $this->fakeCrmIngest(
            financingId: 'opp-42',
            financingName: 'Raccolta fondi per Safe House',
            subjectAccountMatches: 0,
            beneficiaryAccountMatches: 1,
            beneficiaryAccountId: 'acc-safe-house',
        );

        $this->ingestSamplePayment();

        Http::assertSent(function ($request): bool {
            if ($request->method() !== 'POST' || ! str_contains($request->url(), '/api/v1/PrimaNota')) {
                return false;
            }

            $payload = $request->data();

            return ($payload['donationPaymentProvider'] ?? '') === 'Stripe'
                && ($payload['donationPaymentReference'] ?? '') === '#pi_abc'
                && ($payload['donationDonorCategory'] ?? '') === 'Organization'
                && ($payload['donationComment'] ?? '') === 'Test'
                && ! array_key_exists('name', $payload)
                && ! array_key_exists('description', $payload)
                && ($payload['entryType'] ?? '') === 'Income'
                && ($payload['amount'] ?? null) === 15.0
                && ($payload['amountGross'] ?? null) === 15.0
                && ($payload['commissionAmount'] ?? null) === 0.0
                && ($payload['commissionPercent'] ?? null) === 0.0
                && ($payload['subjectName'] ?? '') === 'Anna Bianchi'
                && ($payload['createSubjectAccount'] ?? false) === true
                && ($payload['subjectEmailAddress'] ?? '') === 'anna@example.com'
                && ($payload['beneficiaryName'] ?? '') === 'Safe House'
                && ($payload['beneficiaryPartyId'] ?? '') === 'acc-safe-house'
                && ($payload['beneficiaryPartyType'] ?? '') === 'Account'
                && ! array_key_exists('subjectPartyId', $payload)
                && ! array_key_exists('createSubjectContact', $payload)
                && ($payload['assignedUserId'] ?? '') === 'api-user-id'
                && ($payload['financingId'] ?? '') === 'opp-42';
        });
    }

    public function test_ingest_links_existing_contact_for_individual_donor(): void
    {
        $this->fakeCrmIngest(
            financingId: 'opp-1',
            financingName: 'Camp',
            subjectContactMatches: 1,
            subjectContactId: 'contact-existing',
            beneficiaryAccountMatches: 1,
            beneficiaryAccountId: 'acc-safe-house',
        );

        app(DonationIngestService::class)->ingest(new DonationIngestPayload(
            provider: 'stripe',
            externalId: 'pi_repeat_contact',
            amountGross: 5,
            commissionAmount: 0,
            commissionPercent: 0,
            netAmount: 5,
            currency: 'EUR',
            campaignTitle: 'Camp',
            donorName: 'Luigi Verdi',
            comment: null,
            donorType: 'individual',
            donatedAt: now()->toIso8601String(),
            donorEmail: 'luigi@example.com',
        ));

        Http::assertSent(function ($request): bool {
            if ($request->method() !== 'POST' || ! str_contains($request->url(), '/api/v1/PrimaNota')) {
                return false;
            }

            $payload = $request->data();

            return ($payload['subjectPartyId'] ?? '') === 'contact-existing'
                && ($payload['subjectPartyType'] ?? '') === 'Contact'
                && ($payload['subjectName'] ?? '') === 'Luigi Verdi'
                && ($payload['subjectEmailAddress'] ?? '') === 'luigi@example.com'
                && ! array_key_exists('createSubjectContact', $payload)
                && ($payload['beneficiaryName'] ?? '') === 'Safe House'
                && ($payload['beneficiaryPartyId'] ?? '') === 'acc-safe-house';
        });
    }

    public function test_ingest_creates_finanziamento_with_campaign_goal_amount(): void
    {
        Http::fake(function ($request) {
            $url = $request->url();
            $method = $request->method();

            if ($method === 'GET' && str_contains($url, '/api/v1/PrimaNota')) {
                return Http::response(['total' => 0, 'list' => []]);
            }

            if ($method === 'GET' && str_contains($url, '/api/v1/Opportunity')) {
                return Http::response(['total' => 0, 'list' => []]);
            }

            if ($method === 'POST' && str_contains($url, '/api/v1/Opportunity')) {
                $payload = $request->data();

                return ($payload['name'] ?? '') === 'Nuova raccolta'
                    && ($payload['amount'] ?? null) === 700.0
                    && ($payload['amountCurrency'] ?? '') === 'EUR'
                    ? Http::response(['id' => 'opp-goal'])
                    : Http::response(['message' => 'bad opportunity payload'], 400);
            }

            if ($method === 'GET' && str_contains($url, '/api/v1/Contact')) {
                return Http::response(['total' => 0, 'list' => []]);
            }

            if ($method === 'GET' && str_contains($url, '/api/v1/Account')) {
                return Http::response([
                    'total' => 1,
                    'list' => [['id' => 'acc-safe-house', 'name' => 'Safe House']],
                ]);
            }

            if ($method === 'POST' && str_contains($url, '/api/v1/PrimaNota')) {
                return Http::response(['id' => 'pn-new', 'financingId' => 'opp-goal']);
            }

            return Http::response(['message' => 'Unexpected request: '.$method.' '.$url], 500);
        });

        $result = app(DonationIngestService::class)->ingest(new DonationIngestPayload(
            provider: 'stripe',
            externalId: 'pi_goal_fin',
            amountGross: 10,
            commissionAmount: 0,
            commissionPercent: 0,
            netAmount: 10,
            currency: 'EUR',
            campaignTitle: 'Nuova raccolta',
            donorName: 'Paolo Neri',
            comment: null,
            donorType: 'individual',
            donatedAt: '2026-06-30T10:00:00+00:00',
            financingGoalAmount: 700,
        ));

        $this->assertSame('created', $result['status']);
        $this->assertSame('opp-goal', $result['financing_id']);
    }

    public function test_ingest_creates_finanziamento_when_missing_in_crm(): void
    {
        Http::fake(function ($request) {
            $url = $request->url();
            $method = $request->method();

            if ($method === 'GET' && str_contains($url, '/api/v1/PrimaNota')) {
                return Http::response(['total' => 0, 'list' => []]);
            }

            if ($method === 'GET' && str_contains($url, '/api/v1/Opportunity')) {
                return Http::response(['total' => 0, 'list' => []]);
            }

            if ($method === 'POST' && str_contains($url, '/api/v1/Opportunity')) {
                $payload = $request->data();

                return ($payload['name'] ?? '') === 'Nuova raccolta'
                    && ($payload['stage'] ?? '') === 'Fundraising'
                    && ($payload['closeDate'] ?? '') === '2026-12-31'
                    && ($payload['amount'] ?? null) === 0.0
                    && ($payload['amountCurrency'] ?? '') === 'EUR'
                    && ($payload['assignedUserId'] ?? '') === 'api-user-id'
                    ? Http::response(['id' => 'opp-created'])
                    : Http::response(['message' => 'bad opportunity payload'], 400);
            }

            if ($method === 'GET' && str_contains($url, '/api/v1/Contact')) {
                return Http::response(['total' => 0, 'list' => []]);
            }

            if ($method === 'GET' && str_contains($url, '/api/v1/Account')) {
                return Http::response([
                    'total' => 1,
                    'list' => [['id' => 'acc-safe-house', 'name' => 'Safe House']],
                ]);
            }

            if ($method === 'POST' && str_contains($url, '/api/v1/PrimaNota')) {
                return Http::response(['id' => 'pn-new', 'financingId' => 'opp-created']);
            }

            return Http::response(['message' => 'Unexpected request: '.$method.' '.$url], 500);
        });

        $result = app(DonationIngestService::class)->ingest(new DonationIngestPayload(
            provider: 'stripe',
            externalId: 'pi_missing_fin',
            amountGross: 10,
            commissionAmount: 0,
            commissionPercent: 0,
            netAmount: 10,
            currency: 'EUR',
            campaignTitle: 'Nuova raccolta',
            donorName: 'Paolo Neri',
            comment: null,
            donorType: 'individual',
            donatedAt: '2026-06-30T10:00:00+00:00',
        ));

        $this->assertSame('created', $result['status']);
        $this->assertSame('opp-created', $result['financing_id']);

        Http::assertSent(function ($request): bool {
            return $request->method() === 'POST'
                && str_contains($request->url(), '/api/v1/Opportunity')
                && ($request->data()['name'] ?? '') === 'Nuova raccolta';
        });
    }

    public function test_ingest_is_idempotent_for_duplicate_stripe_payment(): void
    {
        Http::fake([
            'https://crm.test/api/v1/PrimaNota*' => Http::response([
                'total' => 1,
                'list' => [[
                    'id' => 'pn-existing',
                    'donationPaymentReference' => '#pi_dup',
                    'stripeChargeId' => 'ch_already',
                    'commissionAmount' => 0.5,
                ]],
            ]),
        ]);

        $result = app(DonationIngestService::class)->ingest(new DonationIngestPayload(
            provider: 'stripe',
            externalId: 'pi_dup',
            amountGross: 5,
            commissionAmount: 0,
            commissionPercent: 0,
            netAmount: 5,
            currency: 'EUR',
            campaignTitle: 'Any',
            donorName: 'Repeat',
            comment: null,
            donorType: 'individual',
            donatedAt: now()->toIso8601String(),
        ));

        $this->assertSame('duplicate', $result['status']);
        Http::assertSentCount(1);
    }

    public function test_ingest_backfills_incomplete_stripe_row_without_charge_id(): void
    {
        Http::fake([
            'https://crm.test/api/v1/PrimaNota*' => Http::sequence()
                ->push([
                    'total' => 1,
                    'list' => [[
                        'id' => 'pn-incomplete',
                        'donationPaymentReference' => '#pi_incomplete',
                        'stripeChargeId' => null,
                        'commissionAmount' => 0,
                        'amount' => 5,
                        'amountGross' => 5,
                        'financingId' => 'opp-1',
                    ]],
                ], 200)
                ->push([
                    'id' => 'pn-incomplete',
                    'commissionAmount' => 0.33,
                    'stripeChargeId' => 'ch_backfill',
                ], 200),
        ]);

        $result = app(DonationIngestService::class)->ingest(new DonationIngestPayload(
            provider: 'stripe',
            externalId: 'pi_incomplete',
            amountGross: 5,
            commissionAmount: 0.33,
            commissionPercent: 6.6,
            netAmount: 4.67,
            currency: 'EUR',
            campaignTitle: 'Any',
            donorName: 'Backfill',
            comment: null,
            donorType: 'individual',
            donatedAt: now()->toIso8601String(),
            stripeEnrichment: new \App\DataTransferObjects\StripeEnrichmentFields(
                stripeChargeId: 'ch_backfill',
                stripeBalanceTransactionId: 'txn_backfill',
                stripePaymentMethodType: 'link',
            ),
        ));

        $this->assertSame('backfilled', $result['status']);
        $this->assertSame('pn-incomplete', $result['prima_nota_id']);

        Http::assertSent(function ($request): bool {
            return $request->method() === 'PUT'
                && str_contains($request->url(), '/api/v1/PrimaNota/pn-incomplete')
                && ($request->data()['commissionAmount'] ?? null) === 0.33
                && ($request->data()['amount'] ?? null) === 4.67
                && ($request->data()['stripeChargeId'] ?? null) === 'ch_backfill'
                && ($request->data()['stripePaymentMethodType'] ?? null) === 'link';
        });
    }

    public function test_ingest_creates_contact_for_anonymous_individual_donor(): void
    {
        $this->fakeCrmIngest(
            financingId: 'opp-anon',
            financingName: 'Anon raccolta',
            subjectContactMatches: 0,
            beneficiaryAccountMatches: 1,
            beneficiaryAccountId: 'acc-safe-house',
        );

        app(DonationIngestService::class)->ingest(new DonationIngestPayload(
            provider: 'stripe',
            externalId: 'pi_anon',
            amountGross: 5,
            commissionAmount: 0,
            commissionPercent: 0,
            netAmount: 5,
            currency: 'EUR',
            campaignTitle: 'Anon raccolta',
            donorName: '   ',
            comment: null,
            donorType: 'individual',
            donatedAt: now()->toIso8601String(),
            donorPhone: '+393331112222',
        ));

        Http::assertSent(function ($request): bool {
            return $request->method() === 'POST'
                && ($request->data()['subjectName'] ?? '') === 'Donatore'
                && ($request->data()['createSubjectContact'] ?? false) === true
                && ($request->data()['subjectPhoneNumber'] ?? '') === '+393331112222';
        });
    }

    public function test_campaign_finanziamento_title_override(): void
    {
        $campaign = DonationCampaign::factory()->create([
            'espocrm_finanziamento_name' => 'CRM Custom Title',
        ]);

        $this->assertSame('CRM Custom Title', $campaign->finanziamentoTitle());
    }

    /**
     * @return array{status: string, prima_nota_id: string, financing_id: string}
     */
    private function ingestSamplePayment(): array
    {
        return app(DonationIngestService::class)->ingest(new DonationIngestPayload(
            provider: 'stripe',
            externalId: 'pi_abc',
            amountGross: 15,
            commissionAmount: 0,
            commissionPercent: 0,
            netAmount: 15,
            currency: 'EUR',
            campaignTitle: 'Raccolta fondi per Safe House',
            donorName: 'Anna Bianchi',
            comment: 'Test',
            donorType: 'organization',
            donatedAt: '2026-06-29T12:00:00+00:00',
            donorEmail: 'anna@example.com',
        ));
    }

    private function fakeCrmIngest(
        string $financingId,
        string $financingName,
        int $subjectContactMatches = 0,
        ?string $subjectContactId = null,
        int $subjectAccountMatches = 0,
        ?string $subjectAccountId = null,
        int $beneficiaryAccountMatches = 1,
        ?string $beneficiaryAccountId = 'acc-safe-house',
    ): void {
        Http::fake(function ($request) use (
            $financingId,
            $financingName,
            $subjectContactMatches,
            $subjectContactId,
            $subjectAccountMatches,
            $subjectAccountId,
            $beneficiaryAccountMatches,
            $beneficiaryAccountId,
        ) {
            $url = $request->url();
            $method = $request->method();

            if ($method === 'GET' && str_contains($url, '/api/v1/PrimaNota')) {
                return Http::response(['total' => 0, 'list' => []]);
            }

            if ($method === 'GET' && str_contains($url, '/api/v1/Opportunity')) {
                return Http::response([
                    'total' => 1,
                    'list' => [['id' => $financingId, 'name' => $financingName]],
                ]);
            }

            if ($method === 'GET' && str_contains($url, '/api/v1/Contact')) {
                $attribute = $request->data()['where'][0]['attribute'] ?? '';

                if ($subjectContactMatches === 1 && in_array($attribute, ['emailAddress', 'phoneNumber', 'phoneNumberNumeric'], true)) {
                    return Http::response([
                        'total' => 1,
                        'list' => [['id' => $subjectContactId, 'name' => 'Luigi Verdi']],
                    ]);
                }

                return Http::response(['total' => 0, 'list' => []]);
            }

            if ($method === 'GET' && str_contains($url, '/api/v1/Account')) {
                $attribute = $request->data()['where'][0]['attribute'] ?? '';
                $value = $request->data()['where'][0]['value'] ?? '';

                if ($value === 'Safe House' && $attribute === 'name') {
                    if ($beneficiaryAccountMatches === 1) {
                        return Http::response([
                            'total' => 1,
                            'list' => [['id' => $beneficiaryAccountId, 'name' => 'Safe House']],
                        ]);
                    }

                    return Http::response(['total' => 0, 'list' => []]);
                }

                if ($subjectAccountMatches === 1 && in_array($attribute, ['emailAddress', 'phoneNumber', 'phoneNumberNumeric', 'name'], true)) {
                    return Http::response([
                        'total' => 1,
                        'list' => [['id' => $subjectAccountId, 'name' => 'Anna Bianchi']],
                    ]);
                }

                return Http::response(['total' => 0, 'list' => []]);
            }

            if ($method === 'POST' && str_contains($url, '/api/v1/PrimaNota')) {
                return Http::response(['id' => 'pn-new', 'financingId' => $financingId]);
            }

            return Http::response(['message' => 'Unexpected request: '.$method.' '.$url], 500);
        });
    }
}
