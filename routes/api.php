<?php

use App\Http\Controllers\Api\DonationCheckoutController;
use App\Http\Controllers\Api\MockDonationCompleteController;
use App\Http\Controllers\StripeWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/webhooks/stripe', StripeWebhookController::class)->name('webhooks.stripe');

Route::middleware('throttle:donations')->group(function (): void {
    Route::post('/donations/intents/{donationCampaign:slug}', [DonationCheckoutController::class, 'store'])
        ->name('api.donations.intents.store');

    Route::post('/donations/mock/{paymentIntent}/complete', MockDonationCompleteController::class)
        ->name('api.donations.mock.complete');
});
