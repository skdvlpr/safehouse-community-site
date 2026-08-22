<?php

namespace App\Support;

use App\Models\Page;
use App\Services\PageService;
use App\Support\CanonicalSlug;
use Illuminate\Support\Collection;

class Navigation
{
    /**
     * @return list<string>
     */
    public static function standardPageKeys(): array
    {
        $configured = config('navigation.standard_page_keys', []);

        if (is_array($configured) && $configured !== []) {
            return array_values(array_unique(array_filter(
                array_map('strval', $configured),
                static fn (string $key): bool => $key !== '',
            )));
        }

        $keys = [];

        foreach (array_merge(config('navigation.header', []), config('navigation.footer', [])) as $item) {
            if (isset($item['page_key']) && is_string($item['page_key']) && $item['page_key'] !== '') {
                $keys[] = $item['page_key'];
            }
        }

        return array_values(array_unique($keys));
    }

    /**
     * @return Collection<int, Page>
     */
    public static function extraMenuPages(string $locale): Collection
    {
        return app(PageService::class)->extraMenuPages($locale);
    }

    /**
     * @param  array<string, mixed>  $item
     */
    public static function url(array $item, string $locale): string
    {
        if (($item['type'] ?? null) === 'pages_dropdown') {
            $first = self::extraMenuPages($locale)->first();

            if ($first !== null) {
                return app(PageService::class)->publicUrl($first, $locale) ?? url('/'.$locale);
            }

            return url('/'.$locale);
        }

        if (isset($item['page_key'])) {
            $url = app(PageService::class)->urlForKey($item['page_key'], $locale);

            return $url ?? url('/'.$locale);
        }

        return route($item['route'], array_merge(['locale' => $locale], $item['params'] ?? []));
    }

    /**
     * @param  array<string, mixed>  $item
     */
    public static function isActive(array $item, string $locale): bool
    {
        if (($item['type'] ?? null) === 'pages_dropdown') {
            return self::isExtraMenuPageActive($locale);
        }

        if (isset($item['route'])) {
            $route = $item['route'];

            return request()->routeIs($route.($route === 'home' ? '' : '*'));
        }

        if (! isset($item['page_key']) || ! request()->routeIs('pages.show')) {
            return false;
        }

        $page = app(PageService::class)->findByKey($item['page_key']);

        if ($page === null) {
            return false;
        }

        $slug = CanonicalSlug::resolveFromModel($page, 'slug');

        return is_string($slug) && $slug !== '' && request()->route('pageSlug') === $slug;
    }

    public static function isExtraMenuPageActive(string $locale): bool
    {
        if (! request()->routeIs('pages.show')) {
            return false;
        }

        $slug = request()->route('pageSlug');

        if (! is_string($slug) || $slug === '') {
            return false;
        }

        return self::extraMenuPages($locale)->contains(
            fn (Page $page): bool => self::pageSlugForLocale($page, $locale) === $slug,
        );
    }

    public static function pageTitle(Page $page, string $locale): string
    {
        return (string) ($page->getTranslation('title', $locale, false)
            ?: $page->getTranslation('title', 'it', false)
            ?: $page->key
            ?: '');
    }

    public static function pageSlugForLocale(Page $page, string $locale): ?string
    {
        return CanonicalSlug::resolveFromModel($page, 'slug');
    }
}
