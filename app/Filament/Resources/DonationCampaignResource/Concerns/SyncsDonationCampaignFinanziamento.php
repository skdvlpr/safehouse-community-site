<?php

namespace App\Filament\Resources\DonationCampaignResource\Concerns;

use App\Models\DonationCampaign;
use App\Services\EspoCrm\EspoCrmFinanziamentoService;
use Filament\Notifications\Notification;
use RuntimeException;

trait SyncsDonationCampaignFinanziamento
{
    protected function syncFinanziamentoToEspo(DonationCampaign $campaign): void
    {
        try {
            if ($campaign->wasChanged('title') && ! $campaign->wasChanged('espocrm_finanziamento_name')) {
                $campaign->espocrm_finanziamento_name = null;
                $campaign->saveQuietly();
            }

            $name = $campaign->finanziamentoTitle();
            app(EspoCrmFinanziamentoService::class)->ensureExists(
                $name,
                amount: $campaign->fundraisingGoalAmount(),
                currency: (string) $campaign->currency,
            );

            if ($campaign->espocrm_finanziamento_name !== $name) {
                $campaign->forceFill(['espocrm_finanziamento_name' => $name])->saveQuietly();
            }
        } catch (RuntimeException $exception) {
            Notification::make()
                ->title(__('cms.notifications.espocrm_sync_failed'))
                ->body($exception->getMessage())
                ->danger()
                ->persistent()
                ->send();

            throw $exception;
        }
    }
}
