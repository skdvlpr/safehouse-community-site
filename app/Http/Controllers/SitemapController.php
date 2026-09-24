<?php

namespace App\Http\Controllers;

use App\Enums\ArticleSection;
use App\Models\Article;
use App\Models\DonationCampaign;
use App\Models\Page;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

/**
 * Published URL index. Generated on request, not stored.
 *
 * @see https://laravel.com/docs/13.x/routing
 * @see https://laravel.com/docs/13.x/responses
 */
class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $locales = config('locales.available');
        $rows = [];

        foreach ($locales as $locale) {
            $rows[] = $this->row(url('/'.$locale), null);

            foreach ([
                'donations',
                'donations/5-per-thousand',
                'volunteers',
                'news',
                'articles',
            ] as $path) {
                $rows[] = $this->row(url('/'.$locale.'/'.$path), null);
            }

            Page::query()->where('is_published', true)->orderBy('id')->each(function (Page $page) use (&$rows, $locale): void {
                if ($page->key === 'home') {
                    return;
                }

                $slug = $page->getTranslation('slug', $locale, false);
                $title = $page->getTranslation('title', $locale, false);
                if (! is_string($slug) || $slug === '' || ! is_string($title) || $title === '') {
                    return;
                }

                $rows[] = $this->row(url('/'.$locale.'/'.$slug), $page->updated_at);
            });

            Article::query()->where('is_published', true)->orderBy('id')->each(function (Article $article) use (&$rows, $locale): void {
                $slug = $article->getTranslation('slug', $locale, false);
                $title = $article->getTranslation('title', $locale, false);
                if (! is_string($slug) || $slug === '' || ! is_string($title) || $title === '') {
                    return;
                }

                $prefix = $article->section === ArticleSection::Editorial ? 'articles' : 'news';
                $rows[] = $this->row(url('/'.$locale.'/'.$prefix.'/'.$slug), $article->updated_at);
            });

            DonationCampaign::query()->active()->orderBy('sort_order')->each(function (DonationCampaign $campaign) use (&$rows, $locale): void {
                $title = $campaign->getTranslation('title', $locale, false);
                if (! is_string($title) || $title === '' || $campaign->slug === '') {
                    return;
                }

                $rows[] = $this->row(url('/'.$locale.'/donations/'.$campaign->slug), $campaign->updated_at);
                $rows[] = $this->row(url('/'.$locale.'/donations/'.$campaign->slug.'/privacy'), $campaign->updated_at);
            });
        }

        $xml = view('sitemap', ['rows' => $rows])->render();

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    private function row(string $loc, ?Carbon $updated): array
    {
        return [
            'loc' => $loc,
            'lastmod' => $updated?->format('Y-m-d'),
        ];
    }
}
