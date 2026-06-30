<?php

return [

    'key' => env('STRIPE_KEY'),

    'secret' => env('STRIPE_SECRET'),

    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),

    /*
     * Standard Stripe account (not Connect): funds settle to the association's
     * connected bank account configured in the Stripe Dashboard.
     */
    'currency' => env('STRIPE_DEFAULT_CURRENCY', 'EUR'),

    /*
     * Shown on donor card/bank statements (max 22 characters, Stripe rules).
     * Example: "SAFE HOUSE DON"
     */
    'statement_descriptor' => env('STRIPE_STATEMENT_DESCRIPTOR', 'SAFE HOUSE'),

    /*
     * Public webhook URL for production registration in Stripe Dashboard.
     * Local dev: use `stripe listen --forward-to …` instead.
     */
    'webhook_url' => env('STRIPE_WEBHOOK_URL'),

    /*
     * Local dev without Stripe keys: mock PaymentIntents + complete endpoint → EspoCRM.
     * Auto-enabled when APP_ENV=local and STRIPE_SECRET is empty. Override with STRIPE_MOCK=true|false.
     */
    'mock' => env('STRIPE_MOCK'),

    'mock_publishable_key' => env('STRIPE_MOCK_PUBLISHABLE_KEY', 'pk_test_mock'),

];
