<?php

use App\Http\Controllers\DonationCampaignController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->to('/'.config('locales.default')));

Route::prefix('{locale}')
    ->where(['locale' => implode('|', config('locales.available'))])
    ->middleware('setlocale')
    ->group(function (): void {
        Route::get('/', [HomeController::class, 'index'])->name('home');

        Route::get('/donazioni', [DonationCampaignController::class, 'index'])->name('donations.index');
        Route::get('/donazioni/{campaignSlug}/privacy', [DonationCampaignController::class, 'privacy'])->name('donations.privacy');
        Route::get('/donazioni/{campaignSlug}/grazie', [DonationCampaignController::class, 'thankYou'])->name('donations.thank-you');
        Route::get('/donazioni/{campaignSlug}', [DonationCampaignController::class, 'show'])->name('donations.show');
    });
