<?php

namespace App\Http\Controllers;

use App\Services\PageService;
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
                $this->pages->viewData($home, $locale),
            );
        }

        return view('pages.home', [
            'stats' => config('home.stats'),
        ]);
    }
}
