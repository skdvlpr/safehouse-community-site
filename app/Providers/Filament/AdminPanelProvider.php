<?php

namespace App\Providers\Filament;

use App\Http\Controllers\Cms\CmsLocaleController;
use App\Http\Middleware\SetCmsLocale;
use App\Services\CmsUiLocale;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('cms-safehouse')
            ->path(env('FILAMENT_PATH', 'cms-safehouse'))
            ->brandName(fn (): string => __('cms.brand'))
            ->brandLogo(fn (): HtmlString => new HtmlString(
                view('filament.cms-brand')->render()
            ))
            ->brandLogoHeight('2.75rem')
            ->favicon(asset('favicon.svg'))
            ->login()
            ->colors([
                'primary' => Color::Red,
                'danger' => Color::Rose,
                'success' => Color::Lime,
                'warning' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                AccountWidget::class,
            ])
            ->authenticatedRoutes(function (): void {
                Route::post('/locale/{locale}', CmsLocaleController::class)
                    ->whereIn('locale', app(CmsUiLocale::class)->available())
                    ->name('locale.update');
            })
            ->renderHook(
                PanelsRenderHook::STYLES_AFTER,
                function (): string {
                    $path = public_path('css/cms-panel.css');
                    $version = is_file($path) ? (string) filemtime($path) : '1';

                    return '<link rel="stylesheet" href="'.e(asset('css/cms-panel.css')).'?v='.$version.'">';
                },
            )
            ->renderHook(
                PanelsRenderHook::GLOBAL_SEARCH_AFTER,
                fn (): string => view('filament.cms-topbar-actions')->render(),
            )
            ->renderHook(
                PanelsRenderHook::FOOTER,
                fn (): string => view('filament.cms-admin-footer')->render(),
            )
            ->renderHook(
                PanelsRenderHook::AUTH_LOGIN_FORM_AFTER,
                fn (): string => view('filament.cms-powered-by', ['compact' => false])->render(),
            )
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                SetCmsLocale::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
