<?php

namespace App\Filament\Resources\PageResource\Concerns;

use App\Support\PageCarousel;

trait NormalizesPageCarouselMeta
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function normalizePageMeta(array $data): array
    {
        if (! array_key_exists('meta', $data)) {
            return $data;
        }

        $meta = $data['meta'];

        if (! is_array($meta)) {
            $data['meta'] = null;

            return $data;
        }

        $data['meta'] = PageCarousel::normalizeMeta($meta);

        return $data;
    }
}
