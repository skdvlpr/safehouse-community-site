<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Site appearance (CMS → Impostazioni → Aspetto)
    |--------------------------------------------------------------------------
    |
    | Default (no CMS row / stock): public photo background (also mirrored into
    | the upload library so Filament can preview it).
    | Aurora restore: Safehouse Aurora SVGs per theme.
    | Custom upload: stored on the public disk under directory.
    |
    */

    'storage_key' => 'appearance.background',

    'disk' => 'public',

    'directory' => 'site-appearance',

    'stock_library_filename' => 'stock-bg-photo.jpg',

    'max_size_kb' => 8192,

    'accepted_mimetypes' => [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/gif',
        'image/svg+xml',
    ],

    /*
    | Selected out-of-the-box background (public asset).
    */
    'stock' => '/images/bg-photo.jpg',

    /*
    | Aurora polygon backgrounds when admin chooses "Ripristina Aurora".
    */
    'aurora' => [
        'dark' => '/images/bg.svg',
        'light' => '/images/bg-light.svg',
    ],

];
