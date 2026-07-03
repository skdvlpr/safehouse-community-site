<?php

namespace App\Filament\Resources\EditorialArticleCategoryResource\Pages;

use App\Enums\ArticleSection;
use App\Filament\Resources\EditorialArticleCategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEditorialArticleCategory extends CreateRecord
{
    protected static string $resource = EditorialArticleCategoryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['section'] = ArticleSection::Editorial;

        return $this->filterTranslatable($data);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function filterTranslatable(array $data): array
    {
        foreach (['name', 'slug', 'description'] as $field) {
            if (isset($data[$field]) && is_array($data[$field])) {
                $data[$field] = array_filter($data[$field], fn ($value) => $value !== null && $value !== '');
            }
        }

        return $data;
    }
}
