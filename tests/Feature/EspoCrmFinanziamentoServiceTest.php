<?php

namespace Tests\Feature;

use App\Services\EspoCrm\EspoCrmFinanziamentoService;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class EspoCrmFinanziamentoServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('espocrm.base_url', 'https://crm.test');
        config()->set('espocrm.api_key', 'test-espo-key');
        config()->set('espocrm.assigned_user_id', 'api-user-id');
        config()->set('espocrm.finanziamento.default_stage', 'Fundraising');
        config()->set('espocrm.finanziamento.default_close_date', '2026-12-31');
        config()->set('espocrm.finanziamento.default_probability', 60);
    }

    public function test_ensure_exists_returns_existing_opportunity_id(): void
    {
        Http::fake([
            'https://crm.test/api/v1/Opportunity*' => Http::response([
                'total' => 1,
                'list' => [['id' => 'opp-existing', 'name' => 'Raccolta test']],
            ]),
        ]);

        $id = app(EspoCrmFinanziamentoService::class)->ensureExists('Raccolta test');

        $this->assertSame('opp-existing', $id);
        Http::assertSentCount(1);
    }

    public function test_ensure_exists_creates_opportunity_when_missing(): void
    {
        Http::fake(function ($request) {
            if ($request->method() === 'GET') {
                return Http::response(['total' => 0, 'list' => []]);
            }

            if ($request->method() === 'POST') {
                return Http::response(['id' => 'opp-new']);
            }

            return Http::response([], 405);
        });

        $id = app(EspoCrmFinanziamentoService::class)->ensureExists('Nuova campagna');

        $this->assertSame('opp-new', $id);

        Http::assertSent(function ($request): bool {
            return $request->method() === 'POST'
                && ($request->data()['name'] ?? '') === 'Nuova campagna'
                && ($request->data()['stage'] ?? '') === 'Fundraising'
                && ($request->data()['closeDate'] ?? '') === '2026-12-31'
                && ($request->data()['assignedUserId'] ?? '') === 'api-user-id';
        });
    }

    public function test_ensure_exists_rejects_empty_name(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Finanziamento name is empty');

        app(EspoCrmFinanziamentoService::class)->ensureExists('   ');
    }
}
