<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Services\PageService;
use Illuminate\View\View;

class PageController extends Controller
{
    public function __construct(private readonly PageService $pages) {}

    public function show(string $locale, string $pageSlug): View
    {
        if (in_array($pageSlug, config('pages.reserved_slugs', []), true)) {
            abort(404);
        }

        $page = $this->pages->findPublishedBySlug($locale, $pageSlug);

        $data = [
            'page' => $page,
            'locale' => $locale,
            'title' => $page->getTranslation('title', $locale),
            'body' => $page->getTranslation('body', $locale),
        ];

        if ($page->template === 'news_index') {
            $data['recentArticles'] = Article::query()
                ->where('is_published', true)
                ->whereNotNull('published_at')
                ->orderByDesc('published_at')
                ->limit(3)
                ->get();
        }

        return view($this->pages->templateView($page), $data);
    }
}
