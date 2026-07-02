<?php

namespace App\Http\Controllers;

use App\DataTransferObjects\ArticleListingFilters;
use App\Models\Article;
use App\Services\ArticleService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function __construct(private readonly ArticleService $articles) {}

    public function index(Request $request): View
    {
        $locale = app()->getLocale();
        $filters = ArticleListingFilters::fromRequest($request);

        return view('pages.articles.index', [
            'articles' => $this->articles->paginatedListing($filters, $locale),
            'categories' => $this->articles->categoriesForListing($locale),
            'filters' => $filters,
            'locale' => $locale,
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
