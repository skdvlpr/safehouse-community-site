<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Page;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;

class PageService
{
    public function findPublishedBySlug(string $locale, string $slug): Page
    {
        if (! Schema::hasTable('pages')) {
            abort(404);
        }

        $page = Page::query()
            ->where('is_published', true)
            ->where("slug->{$locale}", $slug)
            ->first();

        abort_if($page === null, 404);

        return $page;
    }

    public function findByKey(string $key): ?Page
    {
        if (! Schema::hasTable('pages')) {
            return null;
        }

        return Page::query()
            ->where('key', $key)
            ->where('is_published', true)
            ->first();
    }

    public function urlForKey(string $key, ?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();
        $page = $this->findByKey($key);

        if ($page === null) {
            return null;
        }

        $slug = $page->getTranslation('slug', $locale, false);

        if ($slug === '' || $slug === null) {
            $slug = $page->getTranslation('slug', 'it', false);
        }

        abort_if($slug === '' || $slug === null, 404);

        return route('pages.show', ['locale' => $locale, 'pageSlug' => $slug]);
    }

    public function publicUrl(Page $page, ?string $locale = 'it'): ?string
    {
        if (! $page->is_published) {
            return null;
        }

        $slug = $page->getTranslation('slug', $locale, false);

        if ($slug === '' || $slug === null) {
            $slug = $page->getTranslation('slug', 'it', false);
        }

        if ($slug === '' || $slug === null) {
            return null;
        }

        return route('pages.show', ['locale' => $locale, 'pageSlug' => $slug]);
    }

    public function previewUrl(Page $page, string $locale, ?\DateTimeInterface $expiresAt = null): ?string
    {
        if (! $this->hasSlugForLocale($page, $locale)) {
            return null;
        }

        $expiresAt ??= now()->addHours(2);

        return URL::temporarySignedRoute(
            'pages.preview',
            $expiresAt,
            [
                'locale' => $locale,
                'page' => $page->getKey(),
            ]
        );
    }

    public function hasPreviewableSlug(Page $page, ?string $locale = null): bool
    {
        if ($locale !== null) {
            return $this->hasSlugForLocale($page, $locale);
        }

        foreach (config('locales.available', ['it']) as $available) {
            if ($this->hasSlugForLocale($page, $available)) {
                return true;
            }
        }

        return false;
    }

    public function hasSlugForLocale(Page $page, string $locale): bool
    {
        $slug = $page->getTranslation('slug', $locale, false);

        return is_string($slug) && $slug !== '';
    }

    /**
     * @return array<string, mixed>
     */
    public function viewData(Page $page, string $locale, bool $preview = false): array
    {
        $data = [
            'page' => $page,
            'locale' => $locale,
            'title' => $page->getTranslation('title', $locale),
            'body' => $page->getTranslation('body', $locale),
            'isPreview' => $preview,
        ];

        if ($page->template === 'news_index') {
            $data['recentArticles'] = Article::query()
                ->where('is_published', true)
                ->whereNotNull('published_at')
                ->orderByDesc('published_at')
                ->limit(3)
                ->get();
        }

        return $data;
    }

    public function templateView(Page $page): string
    {
        $template = $page->template ?: 'default';
        $configured = config("page_templates.{$template}.view");

        if (is_string($configured) && $configured !== '') {
            return $configured;
        }

        return (string) config('page_templates.default.view', 'pages.templates.default');
    }

    /**
     * @param  array<string, mixed>|null  $meta
     */
    public function localizedMeta(?array $meta, string $field, ?string $locale = null): ?string
    {
        if ($meta === null) {
            return null;
        }

        $locale ??= app()->getLocale();
        $value = Arr::get($meta, "{$field}.{$locale}");

        if (is_string($value) && $value !== '') {
            return $value;
        }

        $fallback = Arr::get($meta, "{$field}.it");

        return is_string($fallback) && $fallback !== '' ? $fallback : null;
    }

    /**
     * @param  array<string, mixed>|null  $meta
     * @return list<array<string, mixed>>
     */
    public function localizedServiceCards(?array $meta, ?string $locale = null): array
    {
        if ($meta === null) {
            return [];
        }

        $locale ??= app()->getLocale();
        $cards = $meta['services'] ?? [];

        if (! is_array($cards)) {
            return [];
        }

        $resolved = [];

        foreach ($cards as $card) {
            if (! is_array($card)) {
                continue;
            }

            $title = $this->pickLocalized($card['title'] ?? null, $locale);
            $body = $this->pickLocalized($card['body'] ?? null, $locale);
            $stats = $this->pickLocalized($card['stats'] ?? null, $locale);

            if ($title === null && $body === null) {
                continue;
            }

            $resolved[] = [
                'title' => $title ?? '',
                'body' => $body ?? '',
                'stats' => $stats,
            ];
        }

        return $resolved;
    }

    /**
     * @param  mixed  $value
     */
    private function pickLocalized(mixed $value, string $locale): ?string
    {
        if (is_string($value)) {
            return $value;
        }

        if (! is_array($value)) {
            return null;
        }

        $localized = $value[$locale] ?? $value['it'] ?? null;

        return is_string($localized) && $localized !== '' ? $localized : null;
    }
}
