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
    ],

];
