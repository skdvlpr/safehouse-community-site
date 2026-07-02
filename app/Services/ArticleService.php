<?php

namespace App\Services;

use App\DataTransferObjects\ArticleListingFilters;
use App\Models\Article;
use App\Models\ArticleCategory;
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
    public function categoriesForListing(string $locale): Collection
    {
        if (! Schema::hasTable('article_categories')) {
            return new Collection;
        }

        return ArticleCategory::query()
            ->whereHas('articles', fn (Builder $query): Builder => $this->publishedArticlesQuery($query))
            ->orderBy('id')
            ->get()
            ->filter(fn (ArticleCategory $category): bool => $this->categorySlug($category, $locale) !== null)
            ->values();
    }

    public function paginatedListing(ArticleListingFilters $filters, string $locale): LengthAwarePaginator
    {
        if (! Schema::hasTable('articles')) {
            return Article::query()->whereRaw('1 = 0')->paginate(12);
        }

        $query = $this->publishedArticlesQuery(Article::query()->with('category'));

        $categoryIds = $this->resolveCategoryIds($filters->categorySlugs, $locale);
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
        $slug = $category->getTranslation('slug', $locale, false);

        if (! is_string($slug) || $slug === '') {
            $slug = $category->getTranslation('slug', 'it', false);
        }

        return is_string($slug) && $slug !== '' ? $slug : null;
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
    private function resolveCategoryIds(array $slugs, string $locale): array
    {
        if ($slugs === [] || ! Schema::hasTable('article_categories')) {
            return [];
        }

        return ArticleCategory::query()
            ->where(function (Builder $query) use ($slugs, $locale): void {
                foreach ($slugs as $slug) {
                    $query->orWhere("slug->{$locale}", $slug)
                        ->orWhere('slug->it', $slug);
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
    private function publishedArticlesQuery(Builder $query): Builder
    {
        return $query
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
