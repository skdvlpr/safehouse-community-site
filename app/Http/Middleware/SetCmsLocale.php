<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetCmsLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = (string) config('cms.locale', 'it');

        if ($locale !== '') {
            App::setLocale($locale);
            Carbon::setLocale($locale);
        }

        return $next($request);
    }
}
