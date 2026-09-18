<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Public audience measurement (GA4 via GTM)
    |--------------------------------------------------------------------------
    |
    | Kill-switch. Default off. A container id is required and must match
    | GTM-[A-Z0-9]+ or the site treats measurement as disabled (no exception).
    | Never put a real id in git or phpunit.xml.
    | https://laravel.com/docs/13.x/configuration
    |
    */

    'enabled' => filter_var(env('MEASUREMENT_ENABLED', false), FILTER_VALIDATE_BOOLEAN),

    'container_id' => (string) env('GTM_CONTAINER_ID', ''),

    // Bootable = enabled AND container matches ^GTM-[A-Z0-9]+$ AND not a preview route.
    // Computed in App\Services\MeasurementBootService, not here (preview needs the request).

];
