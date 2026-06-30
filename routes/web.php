<?php

use App\Http\Controllers\DonationCampaignController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->to('/'.config('locales.default')));

Route::prefix('{locale}')
    ->where(['locale' => implode('|', config('locales.available'))])
    ->middleware('setlocale')
    ->group(function (): void {
        Route::get('/', fn () => view('welcome'))->name('home');

        Route::get('/donazioni', [DonationCampaignController::class, 'index'])->name('donations.index');
        Route::get('/donazioni/{donationCampaign}/privacy', [DonationCampaignController::class, 'privacy'])->name('donations.privacy');
        Route::get('/donazioni/{donationCampaign}/grazie', [DonationCampaignController::class, 'thankYou'])->name('donations.thank-you');
        Route::get('/donazioni/{donationCampaign}', [DonationCampaignController::class, 'show'])->name('donations.show');
    });
