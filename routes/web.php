<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\ContactSubmissionController;
use App\Http\Controllers\DonationCampaignController;
use App\Http\Controllers\EditorialArticleController;
use App\Http\Controllers\GdprConsentController;
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

        Route::get('/donations', [DonationCampaignController::class, 'index'])->name('donations.index');
        Route::get('/donations/5-per-thousand', [DonationCampaignController::class, 'fivePerMille'])->name('donations.five-per-mille');
        Route::get('/donations/{campaignSlug}/privacy', [DonationCampaignController::class, 'privacy'])->name('donations.privacy');
        Route::get('/donations/{campaignSlug}/thank-you', [DonationCampaignController::class, 'thankYou'])->name('donations.thank-you');
        Route::get('/donations/{campaignSlug}', [DonationCampaignController::class, 'show'])->name('donations.show');

        Route::get('/news', [ArticleController::class, 'index'])->name('articles.index');
        Route::get('/news/{articleSlug}', [ArticleController::class, 'show'])->name('articles.show');

        Route::get('/articles', [EditorialArticleController::class, 'index'])->name('editorial-articles.index');
        Route::get('/articles/{articleSlug}', [EditorialArticleController::class, 'show'])->name('editorial-articles.show');

        Route::get('/_preview/articles/{article}', [ArticleController::class, 'preview'])
            ->middleware('signed')
            ->name('articles.preview');

        Route::get('/_preview/editorial-articles/{article}', [EditorialArticleController::class, 'preview'])
            ->middleware('signed')
            ->name('editorial-articles.preview');

        Route::get('/_preview/pages/{page}', [PageController::class, 'preview'])
            ->middleware('signed')
            ->name('pages.preview');

        Route::post('/cookie-consent', [GdprConsentController::class, 'store'])
            ->middleware('throttle:gdpr')
            ->name('cookie-consent.store');

        Route::post('/contact', [ContactSubmissionController::class, 'store'])
            ->name('contact.store');

        Route::get('/volunteers', [VolunteerController::class, 'show'])->name('volunteers.show');
        Route::post('/volunteers', [VolunteerController::class, 'store'])
            ->middleware('throttle:volunteers')
            ->name('volunteers.store');

        Route::get('/{pageSlug}', [PageController::class, 'show'])->name('pages.show');
    });
