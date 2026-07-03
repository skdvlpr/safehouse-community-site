<?php

return [

    'header' => [
        ['label' => 'site.nav.home', 'route' => 'home'],
        ['label' => 'site.nav.about', 'page_key' => 'about'],
        ['label' => 'site.nav.services', 'page_key' => 'services'],
        ['label' => 'site.nav.news', 'route' => 'articles.index'],
        ['label' => 'site.nav.five_per_mille', 'route' => 'donations.five-per-mille', 'highlight' => true],
        ['label' => 'site.nav.donations', 'route' => 'donations.index'],
        ['label' => 'site.nav.contact_us', 'page_key' => 'contact'],
        ['label' => 'site.nav.other_pages', 'type' => 'pages_dropdown'],
    ],

    'footer' => [
        ['label' => 'site.nav.privacy', 'page_key' => 'privacy'],
        ['label' => 'site.nav.contact', 'page_key' => 'contact'],
        ['label' => 'site.nav.cookie', 'page_key' => 'cookie'],
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
    ],

];
