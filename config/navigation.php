<?php

return [

    'header' => [
        ['label' => 'site.nav.home', 'route' => 'home'],
        ['label' => 'site.nav.about', 'page_key' => 'about'],
        ['label' => 'site.nav.services', 'page_key' => 'services'],
        ['label' => 'site.nav.news', 'route' => 'articles.index'],
        ['label' => 'site.nav.donations', 'route' => 'donations.index'],
    ],

    'footer' => [
        ['label' => 'site.nav.privacy', 'page_key' => 'privacy'],
        ['label' => 'site.nav.contact', 'page_key' => 'contact'],
        ['label' => 'site.nav.cookie', 'page_key' => 'cookie'],
    ],

];
