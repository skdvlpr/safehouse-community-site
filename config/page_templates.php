<?php

return [

    /*
    | CMS page templates. Editors pick one when creating a page in Filament;
    | the public site renders the matching Blade under resources/views/pages/templates/.
    */
    'default' => [
        'label' => 'Simple page',
        'description' => 'Title, lead area, and rich-text body in one glass panel.',
        'view' => 'pages.templates.default',
        'example_key' => null,
    ],

    'about' => [
        'label' => 'About',
        'description' => 'Mission intro, highlighted values block, closing statement.',
        'view' => 'pages.templates.about',
        'example_key' => 'about',
    ],

    'services' => [
        'label' => 'Services grid',
        'description' => 'Intro plus card grid from structured meta (services[].title/body).',
        'view' => 'pages.templates.services',
        'example_key' => 'services',
    ],

    'article' => [
        'label' => 'Article / long read',
        'description' => 'Editorial layout for essays and in-depth content.',
        'view' => 'pages.templates.article',
        'example_key' => 'demo-article',
    ],

    'landing' => [
        'label' => 'Landing',
        'description' => 'Wide hero and optional call-to-action emphasis.',
        'view' => 'pages.templates.landing',
        'example_key' => 'demo-landing',
    ],

    'legal' => [
        'label' => 'Legal',
        'description' => 'Privacy, cookie, and policy pages.',
        'view' => 'pages.templates.legal',
        'example_key' => 'privacy',
    ],

    'contact' => [
        'label' => 'Contact',
        'description' => 'Contact details and form placeholder (form wired in P4).',
        'view' => 'pages.templates.contact',
        'example_key' => 'contact',
    ],

    'home' => [
        'label' => 'Home page',
        'description' => 'Public homepage hero, CTAs, and impact stats.',
        'view' => 'pages.templates.home',
        'example_key' => 'home',
    ],

];
