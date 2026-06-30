<?php

namespace App\Filament\Resources\PageResource\Pages;

use App\Filament\Resources\PageResource;
use App\Filament\Resources\PageResource\Concerns\NormalizesPageCarouselMeta;
use Filament\Resources\Pages\CreateRecord;

class CreatePage extends CreateRecord
{
    use NormalizesPageCarouselMeta;

    protected static string $resource = PageResource::class;

    /**
     * Strip empty locale values to avoid storing blank translations.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->normalizePageMeta($this->restructureTranslatableData($data));
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
