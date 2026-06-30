<?php

namespace App\Http\Controllers;

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

        return view($this->pages->templateView($page), [
            'page' => $page,
        ]);
    }
}
