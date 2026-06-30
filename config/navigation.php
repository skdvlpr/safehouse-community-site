<?php

return [

    /*
    | Header primary navigation. Routes must exist under {locale} prefix.
    | Add entries here as public pages ship (About, Services, etc.).
    */
    'header' => [
        ['label' => 'site.nav.home', 'route' => 'home'],
        ['label' => 'site.nav.donations', 'route' => 'donations.index'],
    ],

    'footer' => [
        ['label' => 'site.nav.home', 'route' => 'home'],
        ['label' => 'site.nav.donations', 'route' => 'donations.index'],
    ],

];
