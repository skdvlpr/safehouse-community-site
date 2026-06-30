<?php

namespace App\Filament\Resources\PageResource\Pages;

use App\Filament\Resources\PageResource;
use App\Filament\Resources\PageResource\Actions\PreviewPageAction;
use App\Filament\Resources\PageResource\Concerns\NormalizesPageCarouselMeta;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPage extends EditRecord
{
    use NormalizesPageCarouselMeta;

    protected static string $resource = PageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            PreviewPageAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    /**
     * Strip empty locale values to avoid storing blank translations.
     */
    protected function mutateFormDataBeforeSave(array $data): array
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
