<?php

namespace Tests\Unit;

use App\Services\EspoCrm\EspoCrmAssignedUserOptions;
use App\Services\EspoCrm\EspoCrmAssignedUserResolver;
use App\Services\EspoCrm\EspoCrmClient;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class EspoCrmAssignedUserOptionsTest extends TestCase
{
    public function test_options_include_site_api_user_and_crm_users(): void
    {
        app()->setLocale('it');

        config()->set('espocrm.base_url', 'https://crm.test');
        config()->set('espocrm.api_key', 'secret');
        config()->set('espocrm.assigned_user_id', '');

        Http::fake([
            'crm.test/api/v1/App/user' => Http::response([
                'user' => ['id' => 'api-1', 'userName' => 'site_api', 'name' => 'Site API'],
            ]),
            'crm.test/api/v1/User*' => Http::response([
                'total' => 2,
                'list' => [
                    ['id' => 'api-1', 'userName' => 'site_api', 'name' => 'Site API', 'isActive' => true],
                    ['id' => 'human-1', 'userName' => 'matteo', 'name' => 'Matteo Grossi', 'isActive' => true],
                ],
            ]),
        ]);

        $options = app(EspoCrmAssignedUserOptions::class);
        $options->forgetCache();

        $mapped = $options->optionsForSelect();

        $this->assertArrayHasKey('api-1', $mapped);
        $this->assertStringContainsString('Sito API', $mapped['api-1']);
        $this->assertArrayHasKey('human-1', $mapped);
        $this->assertStringContainsString('Matteo', $mapped['human-1']);
    }

    public function test_resolver_returns_null_when_unconfigured(): void
    {
        config()->set('espocrm.assigned_user_id', '');

        $client = new EspoCrmClient('https://crm.test', 'secret');

        $this->assertNull(app(EspoCrmAssignedUserResolver::class)->resolveUsing($client));
    }

    public function test_resolver_falls_back_to_api_user_when_configured_user_unreadable(): void
    {
        config()->set('espocrm.base_url', 'https://crm.test');
        config()->set('espocrm.api_key', 'secret');
        config()->set('espocrm.assigned_user_id', 'human-forbidden');

        Http::fake([
            'crm.test/api/v1/User/human-forbidden' => Http::response(['message' => 'Forbidden'], 403),
            'crm.test/api/v1/App/user' => Http::response([
                'user' => ['id' => 'api-1', 'userName' => 'site_api'],
            ]),
        ]);

        $client = EspoCrmClient::fromConfig();
        $resolved = app(EspoCrmAssignedUserResolver::class)->resolveUsing($client);

        $this->assertSame('api-1', $resolved);
    }
}
