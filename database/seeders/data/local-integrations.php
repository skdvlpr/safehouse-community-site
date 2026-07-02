<?php

/**
 * Local DDEV integration defaults (APP_ENV=local only).
 *
 * CRM API key is not stored here — set ESPOCRM_API_KEY in .env or CMS → Integrations.
 * Webhook secret is not stored here — use `stripe listen` or thank-you local sync fallback.
 */
return [
    'stripe.key' => 'pk_test_51TfltZ1XvMWH96Ksxj2asWf6N6l9oEonWNXiK5GF9fbiaSbaYqmL3cBc6RnNj2TYUZwNtrHsfwfGM0MpN0NwlOYY00IqgDCSis',
    'stripe.secret' => 'sk_test_51TfltZ1XvMWH96KsEth3pgnpTz0J12Zb0domDLHm4jdT4ACq1kDLncswvynxIXlxMfbnfrQgVu6FXfiazMppqPQb00EceOnNn6',
    'stripe.currency' => 'EUR',
    'stripe.statement_descriptor' => 'SAFE HOUSE',
    'stripe.account_id' => 'acct_1TfltZ1XvMWH96Ks',
    'stripe.account_name' => 'Safe House',
    'espocrm.base_url' => 'https://nonprofit-espocrm.ddev.site',
    'espocrm.assigned_user_id' => '6a0469ae129e80329',
    'espocrm.prima_nota.default_beneficiary_name' => 'Safe House',
    'espocrm.prima_nota.default_subject_name' => 'Donatore',
];
