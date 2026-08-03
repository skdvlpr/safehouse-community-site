<?php

use App\Http\Controllers\Api\DonationCheckoutController;
use App\Http\Controllers\Api\MockDonationCompleteController;
use App\Http\Controllers\Api\PrimaNotaRefreshController;
use App\Http\Controllers\StripeWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/webhooks/stripe', StripeWebhookController::class)->name('webhooks.stripe');

Route::middleware(['crm.sync', 'throttle:60,1'])->group(function (): void {
    Route::post('/internal/prima-nota/refresh-from-stripe', PrimaNotaRefreshController::class)
        ->name('api.internal.prima-nota.refresh-from-stripe');
});

Route::middleware('throttle:donations')->group(function (): void {
    Route::post('/donations/intents/{donationCampaign:slug}', [DonationCheckoutController::class, 'store'])
        ->name('api.donations.intents.store');

    Route::post('/donations/mock/{paymentIntent}/complete', MockDonationCompleteController::class)
        ->name('api.donations.mock.complete');
});
