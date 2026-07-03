<?php

namespace App\Http\Controllers;

use App\DataTransferObjects\ArticleListingFilters;
use App\Enums\ArticleSection;
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
        $section = ArticleSection::News;

        return view('pages.articles.index', [
            'articles' => $this->articles->paginatedListing($filters, $locale, $section),
            'categories' => $this->articles->categoriesForListing($locale, $section),
            'filters' => $filters,
            'locale' => $locale,
            'indexRoute' => $this->articles->indexRouteName($section),
            'showRoute' => $this->articles->showRouteName($section),
        ]);
    }

    public function show(string $locale, string $articleSlug): View
    {
        $article = $this->articles->findPublishedBySlug($locale, $articleSlug, ArticleSection::News);

        return view('pages.articles.show', [
            'article' => $article,
            'indexRoute' => $this->articles->indexRouteName(ArticleSection::News),
        ]);
    }

    public function preview(string $locale, Article $article): Response
    {
        abort_unless($article->section === ArticleSection::News, 404);
        abort_unless($this->articles->hasSlugForLocale($article, $locale), 404);

        return response()
            ->view('pages.articles.show', [
                'article' => $article,
                'isPreview' => true,
                'indexRoute' => $this->articles->indexRouteName(ArticleSection::News),
            ])
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }
}
