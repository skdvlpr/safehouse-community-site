<?php

namespace App\Support;

use App\Services\SiteAppearanceSettings;
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
     * @return array{url: string, alt: string}|null
     */
    public static function firstSlide(?array $meta, ?string $locale = null): ?array
    {
        $slides = self::slides($meta, $locale);

        return $slides[0] ?? null;
    }

    /**
     * @param  array<string, mixed>|null  $meta
     * @return array<string, mixed>|null
     */
    public static function normalizeCarouselOnly(?array $meta): ?array
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
            $meta['services'] = self::normalizeServiceCards($meta['services'] ?? []);

            return app(SiteAppearanceSettings::class)->normalizePageBackgroundMeta($meta) ?? $meta;
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
        $meta['services'] = self::normalizeServiceCards($meta['services'] ?? []);
        $meta = app(SiteAppearanceSettings::class)->normalizePageBackgroundMeta($meta) ?? $meta;

        return $meta;
    }

    /**
     * @return list<array{title: array<string, string>, body: array<string, string>, stats: array<string, string>}>
     */
    private static function normalizeServiceCards(mixed $cards): array
    {
        if (! is_array($cards)) {
            return [];
        }

        $locales = config('locales.available', ['it', 'ru', 'en']);
        $normalized = [];

        foreach ($cards as $card) {
            if (! is_array($card)) {
                continue;
            }

            $title = self::normalizeTranslatableField($card['title'] ?? null, $locales);
            $body = self::normalizeTranslatableField($card['body'] ?? null, $locales);
            $stats = self::normalizeTranslatableField($card['stats'] ?? null, $locales);

            if ($title === [] && $body === [] && $stats === []) {
                continue;
            }

            $normalized[] = [
                'title' => $title,
                'body' => $body,
                'stats' => $stats,
            ];
        }

        return array_values($normalized);
    }

    /**
     * @param  list<string>  $locales
     * @return array<string, string>
     */
    private static function normalizeTranslatableField(mixed $value, array $locales): array
    {
        if (! is_array($value)) {
            return [];
        }

        $normalized = [];

        foreach ($locales as $locale) {
            $localized = $value[$locale] ?? null;

            if (is_string($localized) && $localized !== '') {
                $normalized[$locale] = $localized;
            }
        }

        return $normalized;
    }

    private static function normalizePath(mixed $path): ?string
    {
        if (is_string($path) && $path !== '') {
            return $path;
        }

        if (is_array($path)) {
            foreach ($path as $value) {
                if (is_string($value) && $value !== '') {
                    return $value;
                }
            }

            return null;
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
