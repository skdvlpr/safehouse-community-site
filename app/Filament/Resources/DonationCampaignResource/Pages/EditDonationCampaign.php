<?php

namespace App\Filament\Resources\DonationCampaignResource\Pages;

use App\Filament\Resources\DonationCampaignResource;
use Filament\Resources\Pages\EditRecord;

class EditDonationCampaign extends EditRecord
{
    protected static string $resource = DonationCampaignResource::class;

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

        return $data;
    }
}
