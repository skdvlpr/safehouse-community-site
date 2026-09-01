<?php

namespace App\Filament\Resources\ArticleResource\Concerns;

use App\Filament\Support\CarouselFormFields;
use App\Support\PageCarousel;

trait NormalizesArticleCarouselMeta
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (isset($data['meta']) && is_array($data['meta'])) {
            $data['meta'] = CarouselFormFields::normalizeCarouselMetaForForm($data['meta']);
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function normalizeArticleMeta(array $data): array
    {
        if (! array_key_exists('meta', $data)) {
            return $data;
        }

        $meta = $data['meta'];

        if (! is_array($meta)) {
            $data['meta'] = null;

            return $data;
        }

        $data['meta'] = PageCarousel::normalizeCarouselOnly($meta);

        return $data;
    }
}
