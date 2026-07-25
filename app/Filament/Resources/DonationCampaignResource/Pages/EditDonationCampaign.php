<?php

namespace App\Filament\Resources\DonationCampaignResource\Pages;

use App\Filament\Resources\DonationCampaignResource;
use App\Filament\Resources\DonationCampaignResource\Concerns\SyncsDonationCampaignFinanziamento;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDonationCampaign extends EditRecord
{
    use SyncsDonationCampaignFinanziamento;

    protected static string $resource = DonationCampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        foreach (['title', 'description', 'form_notice', 'privacy_notice'] as $field) {
            if (isset($data[$field]) && is_array($data[$field])) {
                $data[$field] = array_filter($data[$field], fn ($v) => $v !== null && $v !== '');
            }
        }

        if (isset($data['preset_amounts']) && is_array($data['preset_amounts'])) {
            $data['preset_amounts'] = array_values(array_map('intval', $data['preset_amounts']));
        }

        if (array_key_exists('fundraising_goal_eur', $data)) {
            $data['fundraising_goal_cents'] = $data['fundraising_goal_eur'];
            unset($data['fundraising_goal_eur']);
        }

        if (! empty($data['allows_recurring'])) {
            $data['fundraising_goal_cents'] = null;
        }

        return $data;
    }

    protected function afterSave(): void
    {
        if ($this->record->wasChanged(['title', 'espocrm_finanziamento_name', 'slug', 'fundraising_goal_cents'])) {
            $this->syncFinanziamentoToEspo($this->getRecord());
        }
    }
}
