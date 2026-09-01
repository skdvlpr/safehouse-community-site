<?php

namespace App\Services;

use App\DataTransferObjects\ArticleListingFilters;
use App\Enums\ArticleSection;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Support\CanonicalSlug;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;

class ArticleService
{
    /**
     * @return Collection<int, ArticleCategory>
     */
    public function categoriesForListing(string $locale, ArticleSection $section = ArticleSection::News): Collection
    {
        if (! Schema::hasTable('article_categories')) {
            return new Collection;
        }

        return ArticleCategory::query()
            ->where('section', $section)
            ->whereHas('articles', fn (Builder $query): Builder => $this->publishedArticlesQuery($query, $section))
            ->orderBy('id')
            ->get()
            ->filter(fn (ArticleCategory $category): bool => $this->categorySlug($category, $locale) !== null)
            ->values();
    }

    public function paginatedListing(
        ArticleListingFilters $filters,
        string $locale,
        ArticleSection $section = ArticleSection::News,
    ): LengthAwarePaginator {
        if (! Schema::hasTable('articles')) {
            return Article::query()->whereRaw('1 = 0')->paginate(12);
        }

        $query = $this->publishedArticlesQuery(Article::query()->with('category'), $section);

        $categoryIds = $this->resolveCategoryIds($filters->categorySlugs, $locale, $section);
        if ($categoryIds !== []) {
            $query->whereIn('article_category_id', $categoryIds);
        }

        if ($filters->publishedFrom !== null) {
            $query->whereDate('published_at', '>=', $filters->publishedFrom);
        }

        if ($filters->publishedTo !== null) {
            $query->whereDate('published_at', '<=', $filters->publishedTo);
        }

        return $query
            ->orderByDesc('published_at')
            ->paginate(12)
            ->withQueryString();
    }

    public function categorySlug(ArticleCategory $category, string $locale): ?string
    {
        return CanonicalSlug::resolveFromModel($category, 'slug');
    }

    public function categoryName(ArticleCategory $category, string $locale): string
    {
        $name = $category->getTranslation('name', $locale, false);

        if (is_string($name) && $name !== '') {
            return $name;
        }

        $fallback = $category->getTranslation('name', 'it', false);

        return is_string($fallback) && $fallback !== '' ? $fallback : '';
    }

    /**
     * @param  list<string>  $slugs
     * @return list<int>
     */
    private function resolveCategoryIds(array $slugs, string $locale, ArticleSection $section): array
    {
        if ($slugs === [] || ! Schema::hasTable('article_categories')) {
            return [];
        }

        return ArticleCategory::query()
            ->where('section', $section)
            ->where(function (Builder $query) use ($slugs): void {
                foreach ($slugs as $slug) {
                    $query->orWhere(function (Builder $inner) use ($slug): void {
                        foreach (CanonicalSlug::locales() as $locale) {
                            $inner->orWhere("slug->{$locale}", $slug);
                        }
                    });
                }
            })
            ->pluck('id')
            ->map(static fn (mixed $id): int => (int) $id)
            ->all();
    }

    /**
     * @param  Builder<Article>  $query
     * @return Builder<Article>
     */
    private function publishedArticlesQuery(Builder $query, ArticleSection $section): Builder
    {
        return $query
            ->where('section', $section)
            ->where('is_published', true)
            ->whereNotNull('published_at');
    }

    public function publicUrl(Article $article, ?string $locale = 'it'): ?string
    {
        if (! $article->is_published || $article->published_at === null) {
            return null;
        }

        $slug = $this->resolveSlug($article, $locale);

        if ($slug === null) {
            return null;
        }

        return route($this->showRouteName($article->section), [
            'locale' => $locale,
            'articleSlug' => $slug,
        ]);
    }

    public function previewUrl(Article $article, string $locale, ?\DateTimeInterface $expiresAt = null): ?string
    {
        if (! $this->hasSlugForLocale($article, $locale)) {
            return null;
        }

        $expiresAt ??= now()->addHours(2);

        return URL::temporarySignedRoute(
            $this->previewRouteName($article->section),
            $expiresAt,
            [
                'locale' => $locale,
                'article' => $article->getKey(),
            ]
        );
    }

    public function indexRouteName(ArticleSection $section): string
    {
        return $section === ArticleSection::Editorial
            ? 'editorial-articles.index'
            : 'articles.index';
    }

    public function showRouteName(ArticleSection $section): string
    {
        return $section === ArticleSection::Editorial
            ? 'editorial-articles.show'
            : 'articles.show';
    }

    public function previewRouteName(ArticleSection $section): string
    {
        return $section === ArticleSection::Editorial
            ? 'editorial-articles.preview'
            : 'articles.preview';
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
        $slug = $article->getTranslation('slug', $locale, false);

        return is_string($slug) && trim($slug) !== '';
    }

    private function resolveSlug(Article $article, string $locale): ?string
    {
        $slug = $article->getTranslation('slug', $locale, false);

        if (! is_string($slug) || trim($slug) === '') {
            return null;
        }

        return trim($slug);
    }

    public function findPublishedBySlug(
        string $locale,
        string $slug,
        ArticleSection $section = ArticleSection::News,
    ): Article {
        if (! Schema::hasTable('articles')) {
            abort(404);
        }

        $article = Article::query()
            ->with(['category', 'author'])
            ->where('section', $section)
            ->where('is_published', true)
            ->whereNotNull('published_at')
            ->where(function (Builder $query) use ($slug): void {
                foreach (CanonicalSlug::locales() as $availableLocale) {
                    $query->orWhere("slug->{$availableLocale}", $slug);
                }
            })
            ->first();

        abort_if($article === null, 404);

        return $article;
    }
}
