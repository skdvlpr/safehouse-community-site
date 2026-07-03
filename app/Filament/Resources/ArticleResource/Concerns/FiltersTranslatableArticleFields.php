<?php

namespace App\Filament\Resources\ArticleResource\Concerns;

trait FiltersTranslatableArticleFields
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function filterTranslatable(array $data): array
    {
        foreach (['title', 'slug', 'excerpt', 'body'] as $field) {
            if (isset($data[$field]) && is_array($data[$field])) {
                $data[$field] = array_filter($data[$field], fn ($value) => $value !== null && $value !== '');
            }
        }

        return $data;
    }
}
