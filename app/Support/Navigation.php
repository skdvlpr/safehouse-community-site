<?php

namespace App\Support;

use App\Services\PageService;

class Navigation
{
    /**
     * @param  array<string, mixed>  $item
     */
    public static function url(array $item, string $locale): string
    {
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

        $slug = $page->getTranslation('slug', $locale, false) ?: $page->getTranslation('slug', 'it');

        return request()->route('pageSlug') === $slug;
    }
}
