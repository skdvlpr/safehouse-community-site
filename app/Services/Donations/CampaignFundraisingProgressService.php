<?php

namespace App\Services\Donations;

use App\DataTransferObjects\FundraisingProgress;
use App\Models\DonationCampaign;
use App\Services\EspoCrm\EspoCrmClient;
use App\Support\IntegrationConfig;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class CampaignFundraisingProgressService
{
    /** @var list<string> */
    private const PROGRESS_STAGES = [
        'Fundraising',
        'Closed Won',
        'Closed Lost',
    ];

    /**
     * @param  Collection<int, DonationCampaign>  $campaigns
     * @return array<string, FundraisingProgress>
     */
    public function forCampaigns(Collection $campaigns): array
    {
        $progress = [];

        foreach ($campaigns as $campaign) {
            $resolved = $this->forCampaign($campaign);
            if ($resolved !== null) {
                $progress[$campaign->slug] = $resolved;
            }
        }

        return $progress;
    }

    public function forCampaign(DonationCampaign $campaign): ?FundraisingProgress
    {
        if ($campaign->allowsRecurring()) {
            return null;
        }

        if (! $this->isConfigured()) {
            return null;
        }

        $cacheKey = 'campaign_fundraising_progress:'.$campaign->slug;
        $cached = Cache::get($cacheKey);

        if (is_array($cached)) {
            return FundraisingProgress::fromArray($cached);
        }

        if ($cached !== null) {
            Cache::forget($cacheKey);
        }

        $progress = $this->fetchFromCrm($campaign);

        if ($progress !== null) {
            Cache::put($cacheKey, $progress->toArray(), now()->addMinute());
        }

        return $progress;
    }

    public function forgetCampaign(DonationCampaign $campaign): void
    {
        Cache::forget('campaign_fundraising_progress:'.$campaign->slug);
    }

    private function isConfigured(): bool
    {
        return IntegrationConfig::string('espocrm.base_url') !== ''
            && IntegrationConfig::string('espocrm.api_key') !== '';
    }

    private function fetchFromCrm(DonationCampaign $campaign): ?FundraisingProgress
    {
        $client = $this->client();
        if ($client === null) {
            return null;
        }

        $name = $campaign->finanziamentoTitle();
        $entity = (string) config('espocrm.finanziamento.entity', 'Opportunity');

        try {
            $result = $client->search($entity, [
                'select' => implode(',', [
                    'id',
                    'name',
                    'stage',
                    'amount',
                    'amountCurrency',
                    'fundraisingCollectedAmount',
                    'fundraisingTargetAmount',
                    'fundraisingProgressPercent',
                ]),
                'maxSize' => 2,
                'where' => [
                    [
                        'type' => 'equals',
                        'attribute' => 'name',
                        'value' => $name,
                    ],
                ],
            ]);
        } catch (RuntimeException $exception) {
            Log::warning('Unable to load fundraising progress from EspoCRM.', [
                'campaign_slug' => $campaign->slug,
                'finanziamento_name' => $name,
                'message' => $exception->getMessage(),
            ]);

            return null;
        } catch (Throwable $exception) {
            report($exception);

            return null;
        }

        $matches = $result['list'] ?? [];
        if (! is_array($matches) || count($matches) !== 1) {
            return null;
        }

        $row = $matches[0];
        $stage = (string) ($row['stage'] ?? '');
        if (! in_array($stage, self::PROGRESS_STAGES, true)) {
            return null;
        }

        $collected = (float) ($row['fundraisingCollectedAmount'] ?? 0);
        $target = (float) ($row['fundraisingTargetAmount'] ?? $row['amount'] ?? 0);
        $percent = (int) ($row['fundraisingProgressPercent'] ?? 0);
        $currency = strtoupper((string) ($row['amountCurrency'] ?? $campaign->currency ?? 'EUR'));

        if ($target <= 0 && $collected <= 0) {
            return null;
        }

        if ($target > 0 && $percent === 0) {
            $percent = (int) min(100, max(0, round($collected / $target * 100)));
        }

        return new FundraisingProgress(
            collected: $collected,
            target: $target,
            percent: $percent,
            currency: $currency,
        );
    }

    private function client(): ?EspoCrmClient
    {
        if (! $this->isConfigured()) {
            return null;
        }

        try {
            return app(EspoCrmClient::class);
        } catch (RuntimeException) {
            return null;
        }
    }
}
