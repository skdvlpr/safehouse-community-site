<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Outbound mail (CMS Integrations overrides .env when set)
    |--------------------------------------------------------------------------
    */

    'host' => env('MAIL_HOST'),

    'port' => env('MAIL_PORT', 587),

    'encryption' => env('MAIL_ENCRYPTION', 'tls'),

    'username' => env('MAIL_USERNAME'),

    'password' => env('MAIL_PASSWORD'),

    'from_address' => env('MAIL_FROM_ADDRESS'),

    'from_name' => env('MAIL_FROM_NAME', env('APP_NAME', 'Safe House')),

    'contact_to' => env('MAIL_CONTACT_TO'),

];
