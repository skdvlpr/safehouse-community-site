<?php

namespace App\Support;

use App\Models\Article;
use App\Models\DonationCampaign;
use App\Models\Page;

/**
 * Search and share text for the current public URL.
 * Reads only the requested locale. Does not call PageService::localizedMeta.
 *
 * @see https://spatie.be/docs/laravel-translatable/v6/introduction
 */
final class DiscoveryText
{
    /**
     * @param  array<string, mixed>  $view
     * @return array{
     *     title: string,
     *     description: string,
     *     noindex: bool,
     *     canonical: string,
     *     image: string,
     *     ogLocale: string,
     *     alternates: array<string, string>
     * }
     */
    public function present(array $view, string $locale): array
    {
        $noindex = (bool) ($view['isPreview'] ?? false)
            || request()->routeIs('donations.thank-you', 'pages.preview', 'articles.preview', 'editorial-articles.preview');

        return [
            'title' => $this->title($view, $locale),
            'description' => $this->description($view, $locale),
            'noindex' => $noindex,
            'canonical' => url()->current(),
            'image' => $this->image($view, $locale),
            'ogLocale' => $locale === 'en' ? 'en_US' : 'it_IT',
            'alternates' => $noindex ? [] : $this->alternates($view, $locale),
        ];
    }

    /**
     * @param  array<string, mixed>  $view
     */
    public function title(array $view, string $locale): string
    {
        $override = $this->override($view, $locale, 'title');
        if ($override !== '') {
            return $override;
        }

        $fixed = $this->fixedTitle($locale);
        if ($fixed !== '') {
            return $fixed;
        }

        $visible = $this->visibleTitle($view, $locale);
        if ($visible !== '') {
            return $visible;
        }

        return $this->routeTitle($locale);
    }

    /**
     * @param  array<string, mixed>  $view
     */
    public function description(array $view, string $locale): string
    {
        $override = $this->override($view, $locale, 'description');
        if ($override !== '') {
            return $this->clip($override);
        }

        $fixed = $this->fixedDescription($locale);
        if ($fixed !== '') {
            return $this->clip($fixed);
        }

        $summary = $this->summary($view, $locale);
        if ($summary !== '') {
            return $this->clip($summary);
        }

        return $this->clip($this->routeLead($locale));
    }

    /**
     * @param  array<string, mixed>  $view
     */
    public function hasOwnTitle(array $view, string $locale): bool
    {
        if ($this->override($view, $locale, 'title') !== '' || $this->visibleTitle($view, $locale) !== '') {
            return true;
        }

        $subject = $view['page'] ?? $view['article'] ?? $view['campaign'] ?? null;

        return $subject === null && $this->routeTitle($locale) !== '';
    }

    /**
     * @param  array<string, mixed>  $view
     */
    private function override(array $view, string $locale, string $field): string
    {
        $page = $view['page'] ?? null;
        if ($page instanceof Page) {
            $meta = is_array($page->meta) ? $page->meta : [];
            $key = $field === 'title' ? 'seo_title' : 'seo_description';
            $value = $meta[$key][$locale] ?? '';

            return is_string($value) ? trim($value) : '';
        }

        $model = $view['article'] ?? $view['campaign'] ?? null;
        if ($model instanceof Article || $model instanceof DonationCampaign) {
            $seo = is_array($model->seo) ? $model->seo : [];
            $bag = $seo[$field] ?? [];
            $value = is_array($bag) ? ($bag[$locale] ?? '') : '';

            return is_string($value) ? trim($value) : '';
        }

        return '';
    }

    /**
     * @param  array<string, mixed>  $view
     */
    private function visibleTitle(array $view, string $locale): string
    {
        $page = $view['page'] ?? null;
        if ($page instanceof Page) {
            return $this->translation($page, 'title', $locale);
        }

        $article = $view['article'] ?? null;
        if ($article instanceof Article) {
            return $this->translation($article, 'title', $locale);
        }

        $campaign = $view['campaign'] ?? null;
        if ($campaign instanceof DonationCampaign) {
            return $this->translation($campaign, 'title', $locale);
        }

        if (request()->routeIs('donations.five-per-mille') && isset($view['donationSettings'])) {
            $heading = $view['donationSettings']->localized($view['donationSettings']->fivePerMille(), 'heading', $locale);

            return is_string($heading) ? trim($heading) : '';
        }

        return '';
    }

    /**
     * @param  array<string, mixed>  $view
     */
    private function summary(array $view, string $locale): string
    {
        $article = $view['article'] ?? null;
        if ($article instanceof Article) {
            $excerpt = $this->translation($article, 'excerpt', $locale);
            if ($excerpt !== '') {
                return $excerpt;
            }

            return $this->firstParagraph($this->translation($article, 'body', $locale));
        }

        $campaign = $view['campaign'] ?? null;
        if ($campaign instanceof DonationCampaign) {
            if ($campaign->allowsRecurring()) {
                return trim((string) __('site.donations.recurring_tagline', [], $locale));
            }

            return $this->firstParagraph($this->translation($campaign, 'description', $locale));
        }

        $page = $view['page'] ?? null;
        if ($page instanceof Page) {
            return $this->firstParagraph($this->visiblePageHtml($page, $view, $locale));
        }

        return '';
    }

    private function translation(object $model, string $attribute, string $locale): string
    {
        if (! method_exists($model, 'getTranslation')) {
            return '';
        }

        $value = $model->getTranslation($attribute, $locale, false);

        return is_string($value) ? trim(html_entity_decode(strip_tags($value))) : '';
    }

    /**
     * @param  array<string, mixed>  $view
     */
    private function visiblePageHtml(Page $page, array $view, string $locale): string
    {
        $body = is_string($view['body'] ?? null)
            ? $view['body']
            : $this->translation($page, 'body', $locale);

        if (($page->template ?: 'default') === 'landing') {
            return LandingContent::fromPage($page, $locale, $body)->intro;
        }

        if (($page->template ?: 'default') === 'contact') {
            $body = preg_replace(
                '#<a\b[^>]*href=(["\'])[^"\']*(?:domande-frequenti|frequently-asked)[^"\']*\1[^>]*>.*?</a>#is',
                '',
                $body,
            ) ?? $body;
            $body = preg_replace(
                '#https?://\S*(?:domande-frequenti|frequently-asked)\S*#i',
                '',
                $body,
            ) ?? $body;
        }

        return $body;
    }

    private function firstParagraph(string $html): string
    {
        if (preg_match('/<p\b[^>]*>(.*?)<\/p>/is', $html, $match) === 1) {
            $html = $match[1];
        }

        $text = trim(html_entity_decode(strip_tags(str_replace(['</p>', '<br>', '<br/>', '<br />'], "\n", $html))));
        $line = trim(strtok($text, "\n") ?: '');

        return $line;
    }

    private function clip(string $text): string
    {
        $text = trim(preg_replace('/\s+/', ' ', $text) ?? '');
        if ($text === '' || mb_strlen($text) <= 160) {
            return $text;
        }

        $cut = mb_substr($text, 0, 160);
        $space = mb_strrpos($cut, ' ');
        if ($space !== false && $space > 40) {
            $cut = mb_substr($cut, 0, $space);
        }

        return rtrim($cut, '.,;: ').'…';
    }

    private function fixedTitle(string $locale): string
    {
        $key = match (true) {
            request()->routeIs('donations.index') => 'site.discovery.donations_title',
            request()->routeIs('donations.five-per-mille') => 'site.discovery.five_per_mille_title',
            request()->routeIs('volunteers.show') => 'site.discovery.volunteer_title',
            default => null,
        };

        return $key === null ? '' : trim((string) __($key, [], $locale));
    }

    private function fixedDescription(string $locale): string
    {
        $key = match (true) {
            request()->routeIs('donations.index') => 'site.discovery.donations_description',
            request()->routeIs('donations.five-per-mille') => 'site.discovery.five_per_mille_description',
            request()->routeIs('volunteers.show') => 'site.discovery.volunteer_description',
            default => null,
        };

        return $key === null ? '' : trim((string) __($key, [], $locale));
    }

    private function routeTitle(string $locale): string
    {
        $key = match (true) {
            request()->routeIs('home') => 'site.home.title',
            request()->routeIs('donations.index') => 'site.donations.index_title',
            request()->routeIs('donations.five-per-mille') => 'site.nav.five_per_mille',
            request()->routeIs('donations.privacy') => 'site.donations.privacy_title',
            request()->routeIs('donations.thank-you') => 'site.donations.thank_you_title',
            request()->routeIs('volunteers.show') => 'site.volunteer.title',
            request()->routeIs('articles.index') => 'site.pages.news_title',
            request()->routeIs('editorial-articles.index') => 'site.pages.editorial_title',
            default => null,
        };

        return $key === null ? '' : trim((string) __($key, [], $locale));
    }

    private function routeLead(string $locale): string
    {
        $key = match (true) {
            request()->routeIs('home') => 'site.home.lead',
            request()->routeIs('donations.index') => 'site.donations.index_lead',
            request()->routeIs('volunteers.show') => 'site.volunteer.lead',
            request()->routeIs('articles.index') => 'site.pages.news_lead',
            request()->routeIs('editorial-articles.index') => 'site.pages.editorial_lead',
            default => null,
        };

        return $key === null ? '' : trim((string) __($key, [], $locale));
    }

    /**
     * @param  array<string, mixed>  $view
     * @return array<string, string>
     */
    private function alternates(array $view, string $locale): array
    {
        $links = [];
        foreach (['it', 'en'] as $target) {
            if (! $this->hasOwnTitle($view, $target)) {
                continue;
            }

            $links[$target] = $target === $locale
                ? url()->current()
                : LocalizedUrl::forLocale($target);
        }

        return $links;
    }

    /**
     * @param  array<string, mixed>  $view
     */
    private function image(array $view, string $locale): string
    {
        $slides = $view['carouselSlides'] ?? null;
        if (is_array($slides) && isset($slides[0]['url']) && is_string($slides[0]['url']) && $slides[0]['url'] !== '') {
            return $slides[0]['url'];
        }

        $page = $view['page'] ?? null;
        if ($page instanceof Page) {
            $slides = PageCarousel::slides($page->meta, $locale);
            if (isset($slides[0]['url']) && is_string($slides[0]['url']) && $slides[0]['url'] !== '') {
                return $slides[0]['url'];
            }
        }

        $article = $view['article'] ?? null;
        if ($article instanceof Article) {
            $slides = PageCarousel::slides($article->meta, $locale);
            if (isset($slides[0]['url']) && is_string($slides[0]['url']) && $slides[0]['url'] !== '') {
                return $slides[0]['url'];
            }
        }

        return asset('images/logo.png');
    }
}
