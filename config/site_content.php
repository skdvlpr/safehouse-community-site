<?php

return [

    /*
    |--------------------------------------------------------------------------
    | CMS-editable global site copy (site_settings table)
    |--------------------------------------------------------------------------
    */

    'keys' => [
        'content.primary_tagline' => [
            'label' => 'Primary tagline',
            'description' => 'Shown in the footer and on the home page under the main title.',
            'translatable' => true,
            'fallback_lang' => 'site.footer.tagline',
        ],
        'content.home_independence_title' => [
            'label' => 'Home independence title',
            'description' => 'Bold label on the home independence banner (between quote and counters).',
            'translatable' => true,
            'fallback_lang' => 'site.home.independence.title',
        ],
        'content.home_independence_body' => [
            'label' => 'Home independence body',
            'description' => 'Supporting text on the home independence banner.',
            'translatable' => true,
            'fallback_lang' => 'site.home.independence.body',
        ],
    ],

];
