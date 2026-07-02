<?php

namespace App\Filament\Resources\ArticleResource\Pages;

use App\Filament\Resources\ArticleResource;
use App\Filament\Resources\ArticleResource\Concerns\NormalizesArticleCarouselMeta;
use Filament\Resources\Pages\CreateRecord;

class CreateArticle extends CreateRecord
{
    use NormalizesArticleCarouselMeta;

    protected static string $resource = ArticleResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->normalizeArticleMeta($this->filterTranslatable($data));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function filterTranslatable(array $data): array
    {
        foreach (['title', 'slug', 'excerpt', 'body'] as $field) {
            if (isset($data[$field]) && is_array($data[$field])) {
                $data[$field] = array_filter($data[$field], fn ($value) => $value !== null && $value !== '');
            }
        }

        return $data;
    }
}
