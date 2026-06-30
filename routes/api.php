<?php

use App\Http\Controllers\Api\DonationCheckoutController;
use App\Http\Controllers\StripeWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/webhooks/stripe', StripeWebhookController::class)->name('webhooks.stripe');

Route::middleware('throttle:donations')->group(function (): void {
    Route::post('/donations/intents/{donationCampaign}', [DonationCheckoutController::class, 'store'])
        ->name('api.donations.intents.store');
});
