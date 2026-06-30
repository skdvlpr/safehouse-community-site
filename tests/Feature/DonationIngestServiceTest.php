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
        $this->fakeExistingFinanziamento('opp-existing', 'Raccolta fondi per Safe House');

        $result = $this->ingestSamplePayment();

        $this->assertSame('created', $result['status']);
        $this->assertSame('pn-new', $result['prima_nota_id']);
        $this->assertSame('opp-existing', $result['financing_id']);
    }

    public function test_ingest_posts_expected_prima_nota_fields_to_crm(): void
    {
        $this->fakeExistingFinanziamento('opp-42', 'Raccolta fondi per Safe House');

        $this->ingestSamplePayment();

        Http::assertSent(function ($request): bool {
            if ($request->method() !== 'POST' || ! str_contains($request->url(), '/api/v1/PrimaNota')) {
                return false;
            }

            $payload = $request->data();

            return ($payload['description'] ?? '') === "Donazione Stripe ordine #pi_abc\nTipo: organization\nTest"
                && ($payload['entryType'] ?? '') === 'Income'
                && ($payload['amount'] ?? null) === 15.0
                && ($payload['amountCurrency'] ?? '') === 'EUR'
                && ($payload['internalClassification'] ?? '') === 'Donation'
                && ($payload['subjectName'] ?? '') === 'Anna Bianchi'
                && ($payload['beneficiaryName'] ?? '') === 'Safe House'
                && ($payload['assignedUserId'] ?? '') === 'api-user-id'
                && ($payload['financingId'] ?? '') === 'opp-42'
                && ! array_key_exists('createSubjectContact', $payload)
                && isset($payload['transactionDate']);
        });
    }

    public function test_ingest_throws_when_finanziamento_not_found_in_crm(): void
    {
        Http::fake([
            'https://crm.test/api/v1/PrimaNota*' => Http::response(['total' => 0, 'list' => []]),
            'https://crm.test/api/v1/Opportunity*' => Http::response(['total' => 0, 'list' => []]),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Finanziamento not found');

        app(DonationIngestService::class)->ingest(new DonationIngestPayload(
            provider: 'stripe',
            externalId: 'pi_missing_fin',
            amount: 10,
            currency: 'EUR',
            campaignTitle: 'Nuova raccolta',
            donorName: 'Paolo Neri',
            comment: null,
            donorType: 'individual',
            donatedAt: '2026-06-30T10:00:00+00:00',
        ));
    }

    public function test_ingest_throws_when_multiple_finanziamenti_match(): void
    {
        Http::fake([
            'https://crm.test/api/v1/PrimaNota*' => Http::response(['total' => 0, 'list' => []]),
            'https://crm.test/api/v1/Opportunity*' => Http::response([
                'total' => 2,
                'list' => [
                    ['id' => 'opp-1', 'name' => 'Duplicato'],
                    ['id' => 'opp-2', 'name' => 'Duplicato'],
                ],
            ]),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Multiple EspoCRM Finanziamenti');

        app(DonationIngestService::class)->ingest(new DonationIngestPayload(
            provider: 'stripe',
            externalId: 'pi_dup_fin',
            amount: 10,
            currency: 'EUR',
            campaignTitle: 'Duplicato',
            donorName: 'Paolo Neri',
            comment: null,
            donorType: null,
            donatedAt: now()->toIso8601String(),
        ));
    }

    public function test_ingest_is_idempotent_for_duplicate_stripe_payment(): void
    {
        Http::fake([
            'https://crm.test/api/v1/PrimaNota*' => Http::response([
                'total' => 1,
                'list' => [['id' => 'pn-existing', 'description' => 'Donazione Stripe ordine #pi_dup']],
            ]),
        ]);

        $result = app(DonationIngestService::class)->ingest(new DonationIngestPayload(
            provider: 'stripe',
            externalId: 'pi_dup',
            amount: 5,
            currency: 'EUR',
            campaignTitle: 'Any',
            donorName: 'Repeat',
            comment: null,
            donorType: null,
            donatedAt: now()->toIso8601String(),
        ));

        $this->assertSame('duplicate', $result['status']);
        $this->assertSame('pn-existing', $result['prima_nota_id']);

        Http::assertSentCount(1);
        Http::assertSent(fn ($request) => $request->method() === 'GET');
    }

    public function test_ingest_uses_default_subject_when_donor_name_empty(): void
    {
        $this->fakeExistingFinanziamento('opp-anon', 'Anon raccolta');

        app(DonationIngestService::class)->ingest(new DonationIngestPayload(
            provider: 'stripe',
            externalId: 'pi_anon',
            amount: 5,
            currency: 'EUR',
            campaignTitle: 'Anon raccolta',
            donorName: '   ',
            comment: null,
            donorType: null,
            donatedAt: now()->toIso8601String(),
        ));

        Http::assertSent(function ($request): bool {
            return $request->method() === 'POST'
                && ($request->data()['subjectName'] ?? '') === 'Donatore'
                && ($request->data()['beneficiaryName'] ?? '') === 'Safe House';
        });
    }

    public function test_idempotency_search_uses_contains_on_external_id(): void
    {
        $this->fakeExistingFinanziamento('opp-1', 'Camp');

        app(DonationIngestService::class)->ingest(new DonationIngestPayload(
            provider: 'stripe',
            externalId: 'pi_contains_check',
            amount: 5,
            currency: 'EUR',
            campaignTitle: 'Camp',
            donorName: 'X',
            comment: null,
            donorType: null,
            donatedAt: now()->toIso8601String(),
        ));

        Http::assertSent(function ($request): bool {
            return $request->method() === 'GET'
                && str_contains($request->url(), '/api/v1/PrimaNota')
                && ($request->data()['where'][0]['type'] ?? '') === 'contains'
                && ($request->data()['where'][0]['attribute'] ?? '') === 'description'
                && ($request->data()['where'][0]['value'] ?? '') === 'pi_contains_check';
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
            amount: 15,
            currency: 'EUR',
            campaignTitle: 'Raccolta fondi per Safe House',
            donorName: 'Anna Bianchi',
            comment: 'Test',
            donorType: 'organization',
            donatedAt: '2026-06-29T12:00:00+00:00',
        ));
    }

    private function fakeExistingFinanziamento(string $financingId, string $name): void
    {
        Http::fake([
            'https://crm.test/api/v1/PrimaNota*' => Http::sequence()
                ->push(['total' => 0, 'list' => []])
                ->push(['id' => 'pn-new', 'financingId' => $financingId]),
            'https://crm.test/api/v1/Opportunity*' => Http::response([
                'total' => 1,
                'list' => [['id' => $financingId, 'name' => $name]],
            ]),
        ]);
    }
}
