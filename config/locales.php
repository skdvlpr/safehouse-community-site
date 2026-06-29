<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Site Locale
    |--------------------------------------------------------------------------
    |
    | Primary public language for safehouse.community. Used for root redirect
    | and fallback when no locale segment is present in the URL.
    |
    */

    'default' => env('APP_DEFAULT_LOCALE', 'it'),

    /*
    |--------------------------------------------------------------------------
    | Available Locales
    |--------------------------------------------------------------------------
    |
    | All locale codes exposed via /{locale}/... routes. Adding a locale
    | requires lang files + DB seeds only — no routing code changes.
    |
    */

    'available' => [
        'it',
        'ru',
        'en',
    ],

];
