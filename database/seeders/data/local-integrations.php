<?php

/**
 * Local DDEV integration defaults (APP_ENV=local only).
 *
 * Stripe publishable/secret keys and webhook secret are NOT stored here.
 * Set them in `.env` (`STRIPE_KEY`, `STRIPE_SECRET`) or CMS → Integrations
 * (secrets are encrypted at rest: https://laravel.com/docs/13.x/encryption).
 *
 * CRM API key is not stored here — set ESPOCRM_API_KEY in .env or CMS → Integrations.
 */
return [
    'stripe.currency' => 'EUR',
    'stripe.statement_descriptor' => 'SAFE HOUSE',
    'stripe.account_name' => 'Safe House',
    'espocrm.base_url' => 'https://nonprofit-espocrm.ddev.site',
    'espocrm.assigned_user_id' => '6a0469ae129e80329',
    'espocrm.prima_nota.default_beneficiary_name' => 'Safe House',
    'espocrm.prima_nota.default_subject_name' => 'Donatore',
];
