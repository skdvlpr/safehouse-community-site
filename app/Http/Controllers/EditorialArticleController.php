<?php

namespace App\Http\Controllers;

use App\DataTransferObjects\ArticleListingFilters;
use App\Enums\ArticleSection;
use App\Models\Article;
use App\Services\ArticleService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class EditorialArticleController extends Controller
{
    public function __construct(private readonly ArticleService $articles) {}

    public function index(Request $request): View
    {
        $locale = app()->getLocale();
        $filters = ArticleListingFilters::fromRequest($request);
        $section = ArticleSection::Editorial;

        return view('pages.editorial-articles.index', [
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
        $section = ArticleSection::Editorial;
        $article = $this->articles->findPublishedBySlug($locale, $articleSlug, $section);

        return view('pages.editorial-articles.show', [
            'article' => $article,
            'indexRoute' => $this->articles->indexRouteName($section),
        ]);
    }

    public function preview(string $locale, Article $article): Response
    {
        abort_unless($article->section === ArticleSection::Editorial, 404);
        abort_unless($this->articles->hasSlugForLocale($article, $locale), 404);

        return response()
            ->view('pages.editorial-articles.show', [
                'article' => $article,
                'isPreview' => true,
                'indexRoute' => $this->articles->indexRouteName(ArticleSection::Editorial),
            ])
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }
}
