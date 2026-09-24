<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

/**
 * @see https://laravel.com/docs/13.x/routing
 */
class RobotsController extends Controller
{
    public function __invoke(): Response
    {
        $body = "User-agent: *\nAllow: /\n\nSitemap: ".url('/sitemap.xml')."\n";

        return response($body, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
