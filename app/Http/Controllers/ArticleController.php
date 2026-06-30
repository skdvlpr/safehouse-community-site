<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\View\View;

class ArticleController extends Controller
{
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
        $article = Article::query()
            ->where('is_published', true)
            ->where("slug->{$locale}", $articleSlug)
            ->firstOrFail();

        return view('pages.articles.show', [
            'article' => $article,
        ]);
    }
}
