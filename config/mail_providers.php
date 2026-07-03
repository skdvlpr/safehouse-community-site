<?php

return [

    /*
    |--------------------------------------------------------------------------
    | SMTP provider presets (CMS Integrations → Email)
    |--------------------------------------------------------------------------
    |
    | Host/port/encryption only — username and password are entered per mailbox.
    |
    */

    'providers' => [
        'gmail' => [
            'label' => 'Gmail / Google Workspace',
            'host' => 'smtp.gmail.com',
            'port' => 587,
            'encryption' => 'tls',
            'hint' => 'Usa una password per app se l\'account ha 2FA.',
        ],
        'microsoft365' => [
            'label' => 'Microsoft 365 / Outlook',
            'host' => 'smtp.office365.com',
            'port' => 587,
            'encryption' => 'tls',
            'hint' => 'SMTP AUTH deve essere abilitato per la casella in Microsoft 365.',
        ],
        'aruba' => [
            'label' => 'Aruba',
            'host' => 'smtps.aruba.it',
            'port' => 465,
            'encryption' => 'ssl',
            'hint' => 'Alternativa: smtp.aruba.it porta 587 con TLS.',
        ],
        'yahoo' => [
            'label' => 'Yahoo Mail',
            'host' => 'smtp.mail.yahoo.com',
            'port' => 465,
            'encryption' => 'ssl',
            'hint' => 'Genera una password per app nelle impostazioni sicurezza Yahoo.',
        ],
        'sendgrid' => [
            'label' => 'SendGrid',
            'host' => 'smtp.sendgrid.net',
            'port' => 587,
            'encryption' => 'tls',
            'hint' => 'Utente SMTP: apikey — password: la tua API key SendGrid.',
        ],
    ],

];
