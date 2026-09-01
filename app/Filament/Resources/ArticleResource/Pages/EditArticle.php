<?php

namespace App\Filament\Resources\ArticleResource\Pages;

use App\Filament\Resources\ArticleResource;
use App\Filament\Resources\ArticleResource\Actions\PreviewArticleAction;
use App\Filament\Resources\ArticleResource\Concerns\FiltersTranslatableArticleFields;
use App\Filament\Resources\ArticleResource\Concerns\NormalizesArticleCarouselMeta;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditArticle extends EditRecord
{
    use FiltersTranslatableArticleFields, NormalizesArticleCarouselMeta;

    protected static string $resource = ArticleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            PreviewArticleAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if ($this->record->author_id === null && auth()->check()) {
            $data['author_id'] = auth()->id();
        }

        return $this->normalizeArticleMeta($this->filterTranslatable($data));
    }
}
