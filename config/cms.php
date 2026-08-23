<?php

return [

    /*
    |--------------------------------------------------------------------------
    | CMS UI locale
    |--------------------------------------------------------------------------
    |
    | Default Filament admin language. Editors can switch between available
    | locales (same set as the public site). Preference is stored in session.
    |
    */

    'locale' => env('CMS_LOCALE', 'it'),

    'available_locales' => ['it', 'en'],

];
