<?php

namespace App\Providers\Filament;

use App\Http\Controllers\Cms\CmsLocaleController;
use App\Http\Middleware\SetCmsLocale;
use App\Services\CmsUiLocale;
use Filament\Actions\Action;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
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
            ->userMenuItems([
                Action::make('backToSite')
                    ->label(fn (): string => __('cms.actions.back_to_site'))
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->url(fn (): string => app(CmsUiLocale::class)->publicSiteUrl())
                    ->openUrlInNewTab()
                    ->sort(5),
                ...$this->cmsLocaleMenuActions(),
            ])
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

    /**
     * @return list<Action>
     */
    private function cmsLocaleMenuActions(): array
    {
        $actions = [];
        $sort = 10;

        foreach (app(CmsUiLocale::class)->available() as $locale) {
            $actions[] = Action::make('cmsLocale_'.$locale)
                ->label(fn () => __('cms.locale.switch_to', ['locale' => __('cms.locale.names.'.$locale)]))
                ->icon(Heroicon::OutlinedLanguage)
                ->url(fn (): string => filament()->getPanel('cms-safehouse')->route('locale.update', ['locale' => $locale]))
                ->postToUrl()
                ->visible(fn (): bool => app()->getLocale() !== $locale)
                ->sort($sort++);
        }

        return $actions;
    }
}
