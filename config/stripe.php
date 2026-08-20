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
     * Optional reference fields — CMS Integrations overrides .env when set.
     * account_id is verified by php artisan stripe:verify against the API.
     */
    'account_id' => env('STRIPE_ACCOUNT_ID'),

    'account_name' => env('STRIPE_ACCOUNT_NAME'),

    /*
     * Public Customer Portal login page (Dashboard → Settings → Billing → Customer portal).
     * Example: https://billing.stripe.com/p/login/test_… (test) or …/p/login/… (live).
     * Shown on recurring donation form + thank-you for self-serve cancel.
     */
    'customer_portal_login_url' => env('STRIPE_CUSTOMER_PORTAL_LOGIN_URL'),

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

    /*
     * After payment_intent.succeeded, BalanceTransaction can lag a few hundred ms.
     * retrieveSettledPaymentIntent retries expand until fee/net is available.
     */
    // Thank-you page often races Stripe BT readiness; keep retries long enough (~10s).
    'settlement_retries' => (int) env('STRIPE_SETTLEMENT_RETRIES', 20),

    'settlement_retry_ms' => (int) env('STRIPE_SETTLEMENT_RETRY_MS', 500),

];
