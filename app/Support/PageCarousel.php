<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class PageCarousel
{
    /**
     * @param  array<string, mixed>|null  $meta
     * @return list<array{url: string, alt: string}>
     */
    public static function slides(?array $meta, ?string $locale = null): array
    {
        if ($meta === null) {
            return [];
        }

        $locale ??= app()->getLocale();
        $items = $meta['carousel'] ?? [];

        if (! is_array($items)) {
            return [];
        }

        $slides = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $path = self::normalizePath($item['path'] ?? null);

            if ($path === null) {
                continue;
            }

            $url = self::resolveUrl($path);

            if ($url === null) {
                continue;
            }

            $alt = self::resolveAlt($item['alt'] ?? null, $locale);

            $slides[] = [
                'url' => $url,
                'alt' => $alt,
            ];
        }

        return $slides;
    }

    /**
     * @param  array<string, mixed>|null  $meta
     * @return array<string, mixed>|null
     */
    public static function normalizeMeta(?array $meta): ?array
    {
        if ($meta === null) {
            return null;
        }

        $items = $meta['carousel'] ?? [];

        if (! is_array($items)) {
            $meta['carousel'] = [];

            return $meta;
        }

        $normalized = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $path = self::normalizePath($item['path'] ?? null);

            if ($path === null) {
                continue;
            }

            $alt = [];

            foreach (config('locales.available', ['it', 'ru', 'en']) as $locale) {
                $value = $item['alt'][$locale] ?? $item["alt_{$locale}"] ?? null;

                if (is_string($value) && $value !== '') {
                    $alt[$locale] = $value;
                }
            }

            $normalized[] = [
                'path' => $path,
                'alt' => $alt,
            ];
        }

        $meta['carousel'] = array_values($normalized);

        return $meta;
    }

    private static function normalizePath(mixed $path): ?string
    {
        if (is_string($path) && $path !== '') {
            return $path;
        }

        if (is_array($path)) {
            $first = $path[0] ?? null;

            return is_string($first) && $first !== '' ? $first : null;
        }

        return null;
    }

    private static function resolveUrl(string $path): ?string
    {
        $disk = config('page_carousel.disk', 'public');

        if (Storage::disk($disk)->exists($path)) {
            return Storage::disk($disk)->url($path);
        }

        if (is_file(public_path($path))) {
            return asset($path);
        }

        return null;
    }

    private static function resolveAlt(mixed $alt, string $locale): string
    {
        if (is_string($alt) && $alt !== '') {
            return $alt;
        }

        if (! is_array($alt)) {
            return '';
        }

        $localized = $alt[$locale] ?? $alt['it'] ?? null;

        return is_string($localized) ? $localized : '';
    }
}
