<?php

namespace App\Filament\Resources\PageResource\Pages;

use App\Filament\Resources\PageResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePage extends CreateRecord
{
    protected static string $resource = PageResource::class;

    /**
     * Strip empty locale values to avoid storing blank translations.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->restructureTranslatableData($data);
    }

    private function restructureTranslatableData(array $data): array
    {
        $translatableFields = ['title', 'slug', 'body'];

        foreach ($translatableFields as $field) {
            if (isset($data[$field]) && is_array($data[$field])) {
                $data[$field] = array_filter($data[$field], fn ($value) => $value !== null && $value !== '');
            }
        }

        return $data;
    }
}
