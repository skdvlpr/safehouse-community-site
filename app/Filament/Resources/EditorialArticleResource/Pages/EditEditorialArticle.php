<?php

namespace App\Filament\Resources\EditorialArticleResource\Pages;

use App\Filament\Resources\ArticleResource\Concerns\FiltersTranslatableArticleFields;
use App\Filament\Resources\ArticleResource\Concerns\NormalizesArticleCarouselMeta;
use App\Filament\Resources\EditorialArticleResource;
use App\Filament\Resources\EditorialArticleResource\Actions\PreviewEditorialArticleAction;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEditorialArticle extends EditRecord
{
    use FiltersTranslatableArticleFields, NormalizesArticleCarouselMeta;

    protected static string $resource = EditorialArticleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            PreviewEditorialArticleAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->normalizeArticleMeta($this->filterTranslatable($data));
    }
}
