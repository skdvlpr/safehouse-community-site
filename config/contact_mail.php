<?php

return [

    'correlation_domain' => env('CONTACT_CORRELATION_DOMAIN', 'safehouse.community'),

    'website_from_address' => env('CONTACT_WEBSITE_FROM_ADDRESS', 'website@safehouse.community'),

    'website_from_name' => env('CONTACT_WEBSITE_FROM_NAME', 'Safe House — sito web'),

    /*
    |--------------------------------------------------------------------------
    | Default sportelli (used until configured in CMS → Integrations → Email)
    |--------------------------------------------------------------------------
    |
    | Each desk:
    |   key        — internal id submitted by the contact form
    |   label      — shown in the dropdown
    |   inbox      — group email address in CRM
    |   case_type  — EspoCRM Case.type enum value (must exist in CRM metadata)
    |
    */
    'default_desks' => [
        [
            'key' => 'digital_desk',
            'label' => 'Sportello digitale',
            'inbox' => env('CONTACT_DESK_DIGITAL_INBOX', 'sportello.digitale@safehouse.community'),
            'case_type' => 'SportelloDigitale',
        ],
        [
            'key' => 'legal_desk',
            'label' => 'Sportello legale',
            'inbox' => env('CONTACT_DESK_LEGAL_INBOX', 'sportello.legale@safehouse.community'),
            'case_type' => 'SportelloLegale',
        ],
        [
            'key' => 'generic_desk',
            'label' => 'Richiesta generica',
            'inbox' => env('CONTACT_DESK_GENERIC_INBOX', 'info@safehouse.community'),
            'case_type' => 'RichiestaGenerica',
        ],
    ],

];
