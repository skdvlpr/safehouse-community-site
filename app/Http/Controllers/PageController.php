<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Services\PageService;
use Illuminate\Http\Response;
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

        return view(
            $this->pages->templateView($page),
            $this->pages->viewData($page, $locale),
        );
    }

    public function preview(string $locale, Page $page): Response
    {
        abort_unless($this->pages->hasSlugForLocale($page, $locale), 404);

        return response()
            ->view(
                $this->pages->templateView($page),
                $this->pages->viewData($page, $locale, preview: true),
            )
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }
}
