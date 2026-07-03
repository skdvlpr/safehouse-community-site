<?php

namespace App\Filament\Resources\EditorialArticleResource\Pages;

use App\Enums\ArticleSection;
use App\Filament\Resources\ArticleResource\Concerns\FiltersTranslatableArticleFields;
use App\Filament\Resources\ArticleResource\Concerns\NormalizesArticleCarouselMeta;
use App\Filament\Resources\EditorialArticleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEditorialArticle extends CreateRecord
{
    use FiltersTranslatableArticleFields, NormalizesArticleCarouselMeta;

    protected static string $resource = EditorialArticleResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['section'] = ArticleSection::Editorial;
        $data['author_id'] = auth()->id();

        return $this->normalizeArticleMeta($this->filterTranslatable($data));
    }
}
