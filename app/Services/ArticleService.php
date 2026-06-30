<?php

namespace App\Services;

use App\Models\Article;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;

class ArticleService
{
    public function publicUrl(Article $article, ?string $locale = 'it'): ?string
    {
        if (! $article->is_published || $article->published_at === null) {
            return null;
        }

        $slug = $this->resolveSlug($article, $locale);

        if ($slug === null) {
            return null;
        }

        return route('articles.show', ['locale' => $locale, 'articleSlug' => $slug]);
    }

    public function previewUrl(Article $article, string $locale, ?\DateTimeInterface $expiresAt = null): ?string
    {
        if (! $this->hasSlugForLocale($article, $locale)) {
            return null;
        }

        $expiresAt ??= now()->addHours(2);

        return URL::temporarySignedRoute(
            'articles.preview',
            $expiresAt,
            [
                'locale' => $locale,
                'article' => $article->getKey(),
            ]
        );
    }

    public function hasPreviewableSlug(Article $article, ?string $locale = null): bool
    {
        if ($locale !== null) {
            return $this->hasSlugForLocale($article, $locale);
        }

        foreach (config('locales.available', ['it']) as $available) {
            if ($this->hasSlugForLocale($article, $available)) {
                return true;
            }
        }

        return false;
    }

    public function hasSlugForLocale(Article $article, string $locale): bool
    {
        return $this->resolveSlug($article, $locale) !== null;
    }

    private function resolveSlug(Article $article, string $locale): ?string
    {
        $slug = $article->getTranslation('slug', $locale, false);

        if (! is_string($slug) || $slug === '') {
            $slug = $article->getTranslation('slug', 'it', false);
        }

        return is_string($slug) && $slug !== '' ? $slug : null;
    }

    public function findPublishedBySlug(string $locale, string $slug): Article
    {
        if (! Schema::hasTable('articles')) {
            abort(404);
        }

        $article = Article::query()
            ->where('is_published', true)
            ->whereNotNull('published_at')
            ->where("slug->{$locale}", $slug)
            ->first();

        abort_if($article === null, 404);

        return $article;
    }
}
