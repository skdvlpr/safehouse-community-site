<?php

namespace App\Filament\Resources\DonationCampaignResource\Pages;

use App\Filament\Resources\DonationCampaignResource;
use App\Filament\Resources\DonationCampaignResource\Concerns\SyncsDonationCampaignFinanziamento;
use Filament\Resources\Pages\CreateRecord;

class CreateDonationCampaign extends CreateRecord
{
    use SyncsDonationCampaignFinanziamento;

    protected static string $resource = DonationCampaignResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->cleanTranslatable($data);
    }

    protected function afterCreate(): void
    {
        $this->syncFinanziamentoToEspo($this->getRecord());
    }

    private function cleanTranslatable(array $data): array
    {
        foreach (['title', 'description', 'form_notice', 'privacy_notice'] as $field) {
            if (isset($data[$field]) && is_array($data[$field])) {
                $data[$field] = array_filter($data[$field], fn ($v) => $v !== null && $v !== '');
            }
        }

        if (isset($data['preset_amounts']) && is_array($data['preset_amounts'])) {
            $data['preset_amounts'] = array_values(array_map('intval', $data['preset_amounts']));
        }

        return $data;
    }
}
