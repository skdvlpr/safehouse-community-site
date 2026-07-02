<?php

namespace Tests\Feature;

use App\Services\EspoCrm\EspoCrmVerifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class EspoCrmVerifierTest extends TestCase
{
    use RefreshDatabase;

    public function test_verifier_accepts_configured_assignee_different_from_api_user(): void
    {
        config()->set('espocrm.base_url', 'https://crm.test');
        config()->set('espocrm.api_key', 'test-espo-key');
        config()->set('espocrm.assigned_user_id', 'human-user-id');

        Http::fake([
            'https://crm.test/api/v1/App/user' => Http::response([
                'user' => [
                    'id' => 'api-user-id',
                    'userName' => 'api_user',
                ],
            ], 200),
            'https://crm.test/api/v1/User/human-user-id' => Http::response([
                'id' => 'human-user-id',
                'name' => 'Office Manager',
            ], 200),
            'https://crm.test/api/v1/PrimaNota*' => Http::response(['total' => 0, 'list' => []], 200),
            'https://crm.test/api/v1/Account*' => Http::response(['total' => 0, 'list' => []], 200),
            'https://crm.test/api/v1/Contact*' => Http::response(['total' => 0, 'list' => []], 200),
            'https://crm.test/api/v1/NonprofitEspocrm/reporting/meal-count/summary' => Http::response([
                'metricList' => ['totalMeals'],
                'year' => ['totalMeals' => 1],
            ], 200),
            'https://crm.test/api/v1/NonprofitEspocrm/reporting/association-meal-count/summary' => Http::response([
                'metricList' => ['portionCount'],
                'year' => ['portionCount' => 2],
            ], 200),
        ]);

        $checks = app(EspoCrmVerifier::class)->runChecks();

        $this->assertTrue(app(EspoCrmVerifier::class)->allPassed($checks));
        $this->assertTrue(
            collect($checks)->contains(
                fn (array $check): bool => $check['label'] === 'Assigned user id'
                    && $check['status'] === 'pass'
                    && str_contains($check['detail'], 'human-user-id'),
            ),
        );
    }
}
