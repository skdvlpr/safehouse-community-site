<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\ContactSubmissionController;
use App\Http\Controllers\DonationCampaignController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\VolunteerController;
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

        Route::get('/notizie', [ArticleController::class, 'index'])->name('articles.index');
        Route::get('/notizie/{articleSlug}', [ArticleController::class, 'show'])->name('articles.show');

        Route::get('/_preview/articles/{article}', [ArticleController::class, 'preview'])
            ->middleware('signed')
            ->name('articles.preview');

        Route::get('/_preview/pages/{page}', [PageController::class, 'preview'])
            ->middleware('signed')
            ->name('pages.preview');

        Route::post('/contact', [ContactSubmissionController::class, 'store'])
            ->middleware('throttle:contact')
            ->name('contact.store');

        Route::get('/volontariato', [VolunteerController::class, 'show'])->name('volunteers.show');
        Route::post('/volontariato', [VolunteerController::class, 'store'])
            ->middleware('throttle:volunteers')
            ->name('volunteers.store');

        Route::get('/{pageSlug}', [PageController::class, 'show'])->name('pages.show');
    });
