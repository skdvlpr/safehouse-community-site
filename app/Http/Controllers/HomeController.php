<?php

namespace App\Http\Controllers;

use App\Enums\ArticleSection;
use App\Models\Article;
use App\Services\PageService;
use App\Support\PageCarousel;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(
        private readonly PageService $pages,
    ) {}

    public function index(): View
    {
        $locale = app()->getLocale();
        $home = $this->pages->findByKey('home');

        if ($home !== null) {
            return view(
                $this->pages->templateView($home),
                array_merge($this->pages->viewData($home, $locale), [
                    'latestStories' => $this->latestStories($locale),
                ]),
            );
        }

        return view('pages.home', [
            'stats' => config('home.stats'),
            'latestStories' => $this->latestStories($locale),
        ]);
    }

    /**
     * @return list<array{title: string, url: string, kind: string, image: ?string}>
     */
    private function latestStories(string $locale): array
    {
        if (! Schema::hasTable('articles')) {
            return [];
        }

        return Article::query()
            ->with('category')
            ->where('is_published', true)
            ->orderByRaw('COALESCE(published_at, updated_at) DESC')
            ->get()
            ->filter(function (Article $article) use ($locale): bool {
                $title = $article->getTranslation('title', $locale, false);
                $slug = $article->getTranslation('slug', $locale, false);

                return is_string($title) && $title !== '' && is_string($slug) && $slug !== '';
            })
            ->take(8)
            ->map(function (Article $article) use ($locale): array {
                $slug = (string) $article->getTranslation('slug', $locale, false);
                $editorial = $article->section === ArticleSection::Editorial;
                $slide = PageCarousel::firstSlide($article->meta, $locale);
                $excerpt = $article->getTranslation('excerpt', $locale, false);
                if (! is_string($excerpt) || trim($excerpt) === '') {
                    $excerpt = strip_tags((string) $article->getTranslation('body', $locale, false));
                }
                $category = $article->category;
                $categoryName = '';
                if ($category !== null) {
                    $categoryName = (string) ($category->getTranslation('name', $locale, false) ?: '');
                }

                return [
                    'title' => (string) $article->getTranslation('title', $locale, false),
                    'url' => route($editorial ? 'editorial-articles.show' : 'articles.show', [
                        'locale' => $locale,
                        'articleSlug' => $slug,
                    ]),
                    'kind' => __($editorial ? 'site.nav.editorial' : 'site.nav.news', [], $locale),
                    'excerpt' => Str::limit(trim($excerpt), 140),
                    'category' => $categoryName,
                    'image' => $slide['url'] ?? null,
                ];
            })
            ->values()
            ->all();
    }
}
