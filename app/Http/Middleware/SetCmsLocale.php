<?php

namespace App\Http\Middleware;

use App\Services\CmsUiLocale;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetCmsLocale
{
    public function __construct(
        private readonly CmsUiLocale $cmsLocale,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->cmsLocale->current();

        App::setLocale($locale);
        Carbon::setLocale($locale);

        return $next($request);
    }
}
