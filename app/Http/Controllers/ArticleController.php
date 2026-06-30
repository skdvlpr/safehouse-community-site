<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Services\ArticleService;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function __construct(private readonly ArticleService $articles) {}

    public function index(): View
    {
        $articles = Article::query()
            ->where('is_published', true)
            ->whereNotNull('published_at')
            ->orderByDesc('published_at')
            ->paginate(12);

        return view('pages.articles.index', [
            'articles' => $articles,
        ]);
    }

    public function show(string $locale, string $articleSlug): View
    {
        $article = $this->articles->findPublishedBySlug($locale, $articleSlug);

        return view('pages.articles.show', [
            'article' => $article,
        ]);
    }

    public function preview(string $locale, Article $article): Response
    {
        abort_unless($this->articles->hasSlugForLocale($article, $locale), 404);

        return response()
            ->view('pages.articles.show', [
                'article' => $article,
                'isPreview' => true,
            ])
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }
}
