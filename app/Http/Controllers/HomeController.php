<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $pages = app(\App\Services\PageService::class);
        $locale = app()->getLocale();
        $home = $pages->findByKey('home');

        if ($home !== null) {
            return view(
                $pages->templateView($home),
                $pages->viewData($home, $locale),
            );
        }

        return view('pages.home', [
            'stats' => config('home.stats'),
        ]);
    }
}
