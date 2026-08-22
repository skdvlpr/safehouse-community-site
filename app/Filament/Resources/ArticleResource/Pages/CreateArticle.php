<?php

namespace App\Filament\Resources\ArticleResource\Pages;

use App\Enums\ArticleSection;
use App\Filament\Resources\ArticleResource;
use App\Filament\Resources\ArticleResource\Concerns\FiltersTranslatableArticleFields;
use App\Filament\Resources\ArticleResource\Concerns\NormalizesArticleCarouselMeta;
use Filament\Resources\Pages\CreateRecord;

class CreateArticle extends CreateRecord
{
    use FiltersTranslatableArticleFields, NormalizesArticleCarouselMeta;

    protected static string $resource = ArticleResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['section'] = ArticleSection::News;

        return $this->normalizeArticleMeta($this->filterTranslatable($data));
    }
}
