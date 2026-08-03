<?php

return [

    /*
    |--------------------------------------------------------------------------
    | CMS-editable integration keys (DB overrides .env when set)
    |--------------------------------------------------------------------------
    |
    | Filament → Settings → Integrations. Secrets are encrypted at rest.
    |
    */

    'keys' => [
        'stripe.key' => [
            'label' => 'Stripe publishable key',
            'group' => 'stripe',
            'encrypted' => false,
            'config' => 'stripe.key',
        ],
        'stripe.secret' => [
            'label' => 'Stripe secret key',
            'group' => 'stripe',
            'encrypted' => true,
            'config' => 'stripe.secret',
        ],
        'stripe.webhook_secret' => [
            'label' => 'Stripe webhook signing secret',
            'group' => 'stripe',
            'encrypted' => true,
            'config' => 'stripe.webhook_secret',
        ],
        'stripe.currency' => [
            'label' => 'Default currency',
            'group' => 'stripe',
            'encrypted' => false,
            'config' => 'stripe.currency',
        ],
        'stripe.statement_descriptor' => [
            'label' => 'Card statement descriptor',
            'group' => 'stripe',
            'encrypted' => false,
            'config' => 'stripe.statement_descriptor',
        ],
        'stripe.account_id' => [
            'label' => 'Stripe account id (optional)',
            'group' => 'stripe',
            'encrypted' => false,
            'config' => 'stripe.account_id',
        ],
        'stripe.account_name' => [
            'label' => 'Stripe account label (optional)',
            'group' => 'stripe',
            'encrypted' => false,
            'config' => 'stripe.account_name',
        ],
        'stripe.customer_portal_login_url' => [
            'label' => 'Stripe Customer Portal login URL',
            'group' => 'stripe',
            'encrypted' => false,
            'config' => 'stripe.customer_portal_login_url',
        ],
        'espocrm.base_url' => [
            'label' => 'CRM base URL',
            'group' => 'espocrm',
            'encrypted' => false,
            'config' => 'espocrm.base_url',
        ],
        'espocrm.api_key' => [
            'label' => 'CRM API key',
            'group' => 'espocrm',
            'encrypted' => true,
            'config' => 'espocrm.api_key',
        ],
        'crm.sync_token' => [
            'label' => 'CRM sync token (PrimaNota refresh)',
            'group' => 'espocrm',
            'encrypted' => true,
            'config' => 'crm.sync_token',
        ],
        'espocrm.assigned_user_id' => [
            'label' => 'CRM assigned user id',
            'group' => 'espocrm',
            'encrypted' => false,
            'config' => 'espocrm.assigned_user_id',
        ],
        'espocrm.prima_nota.default_beneficiary_name' => [
            'label' => 'Prima Nota beneficiary name',
            'group' => 'espocrm',
            'encrypted' => false,
            'config' => 'espocrm.prima_nota.default_beneficiary_name',
        ],
        'espocrm.prima_nota.default_subject_name' => [
            'label' => 'Prima Nota default payer name',
            'group' => 'espocrm',
            'encrypted' => false,
            'config' => 'espocrm.prima_nota.default_subject_name',
        ],
        'mail.host' => [
            'label' => 'SMTP host',
            'group' => 'mail',
            'encrypted' => false,
            'config' => 'mail_settings.host',
        ],
        'mail.port' => [
            'label' => 'SMTP port',
            'group' => 'mail',
            'encrypted' => false,
            'config' => 'mail_settings.port',
        ],
        'mail.encryption' => [
            'label' => 'SMTP encryption',
            'group' => 'mail',
            'encrypted' => false,
            'config' => 'mail_settings.encryption',
        ],
        'mail.username' => [
            'label' => 'SMTP username',
            'group' => 'mail',
            'encrypted' => false,
            'config' => 'mail_settings.username',
        ],
        'mail.password' => [
            'label' => 'SMTP password',
            'group' => 'mail',
            'encrypted' => true,
            'config' => 'mail_settings.password',
        ],
        'mail.from_address' => [
            'label' => 'From email address',
            'group' => 'mail',
            'encrypted' => false,
            'config' => 'mail_settings.from_address',
        ],
        'mail.from_name' => [
            'label' => 'From name',
            'group' => 'mail',
            'encrypted' => false,
            'config' => 'mail_settings.from_name',
        ],
        'contact.website_from_address' => [
            'label' => 'Contact form website sender',
            'group' => 'mail',
            'encrypted' => false,
            'config' => 'contact_mail.website_from_address',
        ],
        'contact.website_from_name' => [
            'label' => 'Contact form website sender name',
            'group' => 'mail',
            'encrypted' => false,
            'config' => 'contact_mail.website_from_name',
        ],
        'turnstile.enabled' => [
            'label' => 'Turnstile captcha enabled',
            'group' => 'turnstile',
            'encrypted' => false,
            'config' => 'turnstile.enabled',
        ],
        'turnstile.site_key' => [
            'label' => 'Turnstile site key',
            'group' => 'turnstile',
            'encrypted' => false,
            'config' => 'turnstile.site_key',
        ],
        'turnstile.secret_key' => [
            'label' => 'Turnstile secret key',
            'group' => 'turnstile',
            'encrypted' => true,
            'config' => 'turnstile.secret_key',
        ],
        'developer.no_cache' => [
            'label' => 'Disable HTTP browser cache',
            'group' => 'developer',
            'encrypted' => false,
            'config' => 'developer.no_cache',
        ],
    ],

];
