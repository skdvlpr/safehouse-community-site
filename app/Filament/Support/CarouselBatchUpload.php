<?php

namespace App\Filament\Support;

use Illuminate\Support\Str;

class CarouselBatchUpload
{
    /**
     * @param  list<string>  $paths
     * @param  array<string|int, array<string, mixed>>  $currentSlides
     * @return array<string|int, array<string, mixed>>
     */
    public static function mergePaths(array $paths, array $currentSlides): array
    {
        $max = (int) config('page_carousel.max_slides', 12);
        $locales = config('locales.available', ['it', 'ru', 'en']);
        $emptyAlt = array_fill_keys($locales, '');

        foreach ($paths as $path) {
            if (count($currentSlides) >= $max) {
                break;
            }

            if (! is_string($path) || $path === '') {
                continue;
            }

            $alreadyExists = collect($currentSlides)->contains(
                fn (array $slide): bool => static::slidePath($slide) === $path,
            );

            if ($alreadyExists) {
                continue;
            }

            $currentSlides[(string) Str::uuid()] = [
                'path' => CarouselFormFields::fileUploadPathState($path),
                'alt' => $emptyAlt,
            ];
        }

        return $currentSlides;
    }

    /**
     * @param  array<string, mixed>  $slide
     */
    public static function slidePath(array $slide): ?string
    {
        return CarouselFormFields::extractStoredPath($slide['path'] ?? null);
    }
}
