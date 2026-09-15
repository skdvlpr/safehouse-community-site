<?php

namespace App\Support;

use Illuminate\Http\Request;

final class PublicRequestLocale
{
    public static function apply(Request $request): void
    {
        $locale = $request->header('X-App-Locale');

        if (! is_string($locale) || ! in_array($locale, config('locales.available', []), true)) {
            return;
        }

        app()->setLocale($locale);
    }
}
