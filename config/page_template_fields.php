<?php

return [

    /*
    | Editable content blocks per CMS page template.
    | Filament shows these fields when the editor picks a template.
    | Future: block composer (P3c) will generate layouts from this registry.
    */
    'about' => [
        'tagline' => [
            'type' => 'text',
            'label' => 'Hero tagline',
            'help' => 'Short line beside the large page title (reference: Dartmouth-style split hero).',
            'meta_key' => 'tagline',
            'translatable' => true,
        ],
        'body' => [
            'type' => 'rich_text',
            'label' => 'Mission intro',
            'help' => 'Main text in the left column.',
            'column' => 'body',
        ],
        'values' => [
            'type' => 'textarea',
            'label' => 'Our values',
            'help' => 'Highlighted panel on the right.',
            'meta_key' => 'values',
            'translatable' => true,
        ],
        'closing' => [
            'type' => 'textarea',
            'label' => 'Closing statement',
            'help' => 'Full-width quote at the bottom of the page.',
            'meta_key' => 'closing',
            'translatable' => true,
        ],
    ],

    'services' => [
        'body' => [
            'type' => 'rich_text',
            'label' => 'Intro',
            'help' => 'Banner text above the service cards.',
            'column' => 'body',
        ],
        'services' => [
            'type' => 'repeater',
            'label' => 'Service cards',
            'help' => 'Numbered cards in the grid.',
            'meta_key' => 'services',
        ],
    ],

    'default' => [
        'body' => [
            'type' => 'rich_text',
            'label' => 'Body',
            'column' => 'body',
        ],
    ],

];
