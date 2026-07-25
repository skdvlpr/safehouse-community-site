<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Developer tools (Filament → Settings → Developer tools)
    |--------------------------------------------------------------------------
    |
    | When no_cache is true (DB override via SiteSettings or this default),
    | HTTP responses get Cache-Control: no-store so browsers do not keep
    | stale HTML/CSS after CMS or Vite changes.
    |
    */

    'no_cache' => (bool) env('DEVELOPER_NO_CACHE', false),

];
