<?php

namespace Tests\Unit;

use App\DataTransferObjects\FundraisingProgress;
use App\Models\DonationCampaign;
use App\Services\Donations\CampaignFundraisingProgressService;
use App\Services\EspoCrm\EspoCrmClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use RuntimeException;
use Tests\TestCase;

class CampaignFundraisingProgressServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_progress_from_crm_opportunity(): void
    {
        config([
            'espocrm.base_url' => 'https://crm.example.test',
            'espocrm.api_key' => 'secret',
            'espocrm.finanziamento.entity' => 'Opportunity',
        ]);

        $client = $this->createMock(EspoCrmClient::class);
        $client->expects($this->once())
            ->method('search')
            ->with('Opportunity', $this->callback(function (array $query): bool {
                return ($query['where'][0]['value'] ?? null) === 'Donate to Safe House';
            }))
            ->willReturn([
                'list' => [[
                    'id' => 'opp-1',
                    'stage' => 'Fundraising',
                    'fundraisingCollectedAmount' => 575,
                    'fundraisingTargetAmount' => 700,
                    'fundraisingProgressPercent' => 82,
                    'amountCurrency' => 'EUR',
                ]],
            ]);

        $this->app->instance(EspoCrmClient::class, $client);

        $campaign = DonationCampaign::factory()->create([
            'slug' => 'safe-house',
            'espocrm_finanziamento_name' => 'Donate to Safe House',
        ]);

        $service = app(CampaignFundraisingProgressService::class);
        $progress = $service->forCampaign($campaign);

        $this->assertInstanceOf(FundraisingProgress::class, $progress);
        $this->assertSame(575.0, $progress->collected);
        $this->assertSame(700.0, $progress->target);
        $this->assertSame(82, $progress->percent);
        $this->assertSame('575 €', $progress->formatMoney(575));
    }

    public function test_caches_progress_as_array_not_object(): void
    {
        config([
            'espocrm.base_url' => 'https://crm.example.test',
            'espocrm.api_key' => 'secret',
        ]);

        $client = $this->createMock(EspoCrmClient::class);
        $client->expects($this->once())
            ->method('search')
            ->willReturn([
                'list' => [[
                    'id' => 'opp-1',
                    'stage' => 'Fundraising',
                    'fundraisingCollectedAmount' => 575,
                    'fundraisingTargetAmount' => 700,
                    'fundraisingProgressPercent' => 82,
                    'amountCurrency' => 'EUR',
                ]],
            ]);

        $this->app->instance(EspoCrmClient::class, $client);

        $campaign = DonationCampaign::factory()->create([
            'slug' => 'cache-shape',
            'espocrm_finanziamento_name' => 'Donate to Safe House',
        ]);

        Cache::flush();

        $service = app(CampaignFundraisingProgressService::class);
        $service->forCampaign($campaign);

        $cached = Cache::get('campaign_fundraising_progress:cache-shape');

        $this->assertIsArray($cached);
        $this->assertSame(575.0, $cached['collected']);
        $this->assertSame(700.0, $cached['target']);
    }

    public function test_returns_null_when_crm_is_not_configured(): void
    {
        config([
            'espocrm.base_url' => '',
            'espocrm.api_key' => '',
        ]);

        $campaign = DonationCampaign::factory()->create(['slug' => 'offline']);

        $progress = app(CampaignFundraisingProgressService::class)->forCampaign($campaign);

        $this->assertNull($progress);
    }

    public function test_returns_null_when_opportunity_stage_is_not_fundraising(): void
    {
        config([
            'espocrm.base_url' => 'https://crm.example.test',
            'espocrm.api_key' => 'secret',
        ]);

        $client = $this->createMock(EspoCrmClient::class);
        $client->method('search')->willReturn([
            'list' => [[
                'id' => 'opp-1',
                'stage' => 'Prospecting',
                'fundraisingCollectedAmount' => 100,
                'fundraisingTargetAmount' => 500,
                'fundraisingProgressPercent' => 20,
                'amountCurrency' => 'EUR',
            ]],
        ]);

        $this->app->instance(EspoCrmClient::class, $client);

        $campaign = DonationCampaign::factory()->create([
            'slug' => 'draft',
            'espocrm_finanziamento_name' => 'Draft campaign',
        ]);

        Cache::flush();

        $progress = app(CampaignFundraisingProgressService::class)->forCampaign($campaign);

        $this->assertNull($progress);
    }

    public function test_returns_null_when_crm_client_cannot_be_resolved(): void
    {
        config([
            'espocrm.base_url' => 'https://crm.example.test',
            'espocrm.api_key' => 'secret',
        ]);

        $this->app->bind(EspoCrmClient::class, fn () => throw new RuntimeException('missing config'));

        $campaign = DonationCampaign::factory()->create(['slug' => 'broken']);

        $progress = app(CampaignFundraisingProgressService::class)->forCampaign($campaign);

        $this->assertNull($progress);
    }
}
