<?php

return [

    'key' => env('STRIPE_KEY'),

    'secret' => env('STRIPE_SECRET'),

    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),

    /*
     * Standard Stripe accounts support PaymentIntents, Payment Element, cards,
     * and webhooks at no extra cost. Minimum charge for EUR is €0.50.
     */
    'currency' => env('STRIPE_DEFAULT_CURRENCY', 'EUR'),

];
