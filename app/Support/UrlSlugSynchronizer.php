<?php

namespace App\Support;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\DonationCampaign;
use App\Models\Page;
use Illuminate\Database\Eloquent\Model;

final class UrlSlugSynchronizer
{
    /**
     * Overwrite URL slug(s) from name/title. Returns true if attributes changed.
     */
    public function sync(Model $model, bool $force = false): bool
    {
        return match (true) {
            $model instanceof Article => $this->syncTranslatable($model, 'title', 'slug', fn (string $locale, string $candidate): bool => $this->articleSlugTaken($model, $locale, $candidate), $force),
            $model instanceof ArticleCategory => $this->syncTranslatable($model, 'name', 'slug', fn (string $locale, string $candidate): bool => $this->categorySlugTaken($model, $locale, $candidate), $force),
            $model instanceof Page => $this->syncPage($model, $force),
            $model instanceof DonationCampaign => $this->syncDonationCampaign($model, $force),
            default => false,
        };
    }

    /**
     * @param  callable(string, string): bool  $exists
     */
    private function syncTranslatable(Model $model, string $sourceAttribute, string $slugAttribute, callable $exists, bool $force): bool
    {
        /** @var array<string, string|null> $sources */
        $sources = $model->getTranslations($sourceAttribute);
        /** @var array<string, string|null> $currentSlugs */
        $currentSlugs = $model->getTranslations($slugAttribute);
        $locales = config('locales.available', ['it', 'ru', 'en']);
        $next = [];

        foreach ($locales as $locale) {
            $source = $sources[$locale] ?? null;
            if (! is_string($source) || trim($source) === '') {
                continue;
            }

            $existing = is_string($currentSlugs[$locale] ?? null) ? (string) $currentSlugs[$locale] : '';
            $sourceDirty = $model->isDirty($sourceAttribute);

            $shouldRegenerate = $force
                || $existing === ''
                || ! $this->isValidSlug($existing)
                || ($model->exists && $sourceDirty);

            if (! $shouldRegenerate) {
                $next[$locale] = $existing;

                continue;
            }

            $base = UrlSlug::from($source, $locale);
            if ($base === '') {
                continue;
            }

            $next[$locale] = UrlSlug::unique(
                $base,
                fn (string $candidate): bool => $exists($locale, $candidate),
            );
        }

        $before = $currentSlugs;
        $model->setTranslations($slugAttribute, $next);

        return $before !== $next;
    }

    private function syncPage(Page $page, bool $force): bool
    {
        if ($page->template === 'home') {
            $before = $page->getTranslations('slug');
            $page->setTranslations('slug', []);

            return $before !== [];
        }

        return $this->syncTranslatable(
            $page,
            'title',
            'slug',
            fn (string $locale, string $candidate): bool => $this->pageSlugTaken($page, $locale, $candidate),
            $force,
        );
    }

    private function syncDonationCampaign(DonationCampaign $campaign, bool $force): bool
    {
        $protected = (string) config('donations.recurring_campaign_slug', 'donazione-ricorrente');

        if ($campaign->allows_recurring || $campaign->slug === $protected) {
            return false;
        }

        $title = (string) ($campaign->getTranslation('title', 'it', false) ?: '');
        $titleDirty = $campaign->isDirty('title');
        $existing = (string) ($campaign->slug ?? '');

        $shouldRegenerate = $force
            || $existing === ''
            || ! $this->isValidSlug($existing)
            || ($campaign->exists && $titleDirty);

        if (! $shouldRegenerate) {
            return false;
        }

        $base = UrlSlug::from($title, 'it');
        if ($base === '') {
            return false;
        }

        $next = UrlSlug::unique(
            $base,
            fn (string $candidate): bool => DonationCampaign::query()
                ->where('slug', $candidate)
                ->when($campaign->exists, fn ($q) => $q->whereKeyNot($campaign->getKey()))
                ->exists(),
        );

        if ($campaign->slug === $next) {
            return false;
        }

        $campaign->slug = $next;

        return true;
    }

    private function isValidSlug(string $slug): bool
    {
        return (bool) preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug);
    }

    private function articleSlugTaken(Article $article, string $locale, string $candidate): bool
    {
        return Article::query()
            ->where('section', $article->section)
            ->where("slug->{$locale}", $candidate)
            ->when($article->exists, fn ($q) => $q->whereKeyNot($article->getKey()))
            ->exists();
    }

    private function categorySlugTaken(ArticleCategory $category, string $locale, string $candidate): bool
    {
        return ArticleCategory::query()
            ->where('section', $category->section)
            ->where("slug->{$locale}", $candidate)
            ->when($category->exists, fn ($q) => $q->whereKeyNot($category->getKey()))
            ->exists();
    }

    private function pageSlugTaken(Page $page, string $locale, string $candidate): bool
    {
        return Page::query()
            ->where("slug->{$locale}", $candidate)
            ->when($page->exists, fn ($q) => $q->whereKeyNot($page->getKey()))
            ->exists();
    }
}
