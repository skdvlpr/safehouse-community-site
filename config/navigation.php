<?php

return [

    'header' => [
        ['label' => 'site.nav.about', 'page_key' => 'about'],
        ['label' => 'site.nav.services', 'page_key' => 'services'],
        ['label' => 'site.home.cta_member', 'page_key' => 'diventa-socio'],
        ['label' => 'site.volunteer.title', 'route' => 'volunteers.show'],
        ['label' => 'site.nav.contact_us', 'page_key' => 'contact'],
        ['label' => 'site.nav.other_pages', 'type' => 'pages_dropdown'],
    ],

    'footer' => [
        ['label' => 'site.nav.privacy', 'page_key' => 'privacy'],
        ['label' => 'site.nav.cookie', 'page_key' => 'cookie'],
        ['label' => 'site.nav.contact', 'page_key' => 'contact'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Page keys linked directly in header/footer (excluded from "Altre Pagine")
    |--------------------------------------------------------------------------
    */
    'standard_page_keys' => [
        'home',
        'about',
        'services',
        'privacy',
        'contact',
        'cookie',
        'diventa-socio',
    ],

];
