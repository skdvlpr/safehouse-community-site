<?php

namespace App\Support;

use Illuminate\Http\Request;

class LocalizedUrl
{
    public static function forLocale(string $targetLocale, ?Request $request = null): string
    {
        $request ??= request();

        $segments = $request->segments();
        if ($segments === []) {
            return url('/'.$targetLocale);
        }

        $segments[0] = $targetLocale;

        $path = '/'.implode('/', $segments);
        $query = $request->getQueryString();

        return $query !== null && $query !== ''
            ? url($path.'?'.$query)
            : url($path);
    }
}
