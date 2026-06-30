<?php

return [

    /*
    | CMS page templates. Editors pick one when creating a page in Filament;
    | the public site renders the matching Blade under resources/views/pages/templates/.
    */
    'default' => [
        'label' => 'Simple page',
        'description' => 'Title, lead area, and rich-text body.',
        'view' => 'pages.templates.default',
    ],

    'about' => [
        'label' => 'About',
        'description' => 'Mission intro, highlighted values block, closing statement.',
        'view' => 'pages.templates.about',
    ],

    'services' => [
        'label' => 'Services grid',
        'description' => 'Intro plus card grid from structured meta (services[].title/body).',
        'view' => 'pages.templates.services',
    ],

    'article' => [
        'label' => 'Article / long read',
        'description' => 'Editorial layout for essays and in-depth content.',
        'view' => 'pages.templates.article',
    ],

    'news_index' => [
        'label' => 'News listing shell',
        'description' => 'Static intro pointing to /notizie (dynamic list).',
        'view' => 'pages.templates.news-index',
    ],

    'landing' => [
        'label' => 'Landing',
        'description' => 'Wide hero and optional call-to-action emphasis.',
        'view' => 'pages.templates.landing',
    ],

    'legal' => [
        'label' => 'Legal',
        'description' => 'Privacy, cookie, and policy pages.',
        'view' => 'pages.templates.legal',
    ],

    'contact' => [
        'label' => 'Contact',
        'description' => 'Contact details and form placeholder (form wired in P4).',
        'view' => 'pages.templates.contact',
    ],

];
