<?php

namespace App\Filament\Pages;

use App\Services\ApplicationCacheClearer;
use App\Services\SiteSettingsService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use UnitEnum;

class ManageDeveloperTools extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWrenchScrewdriver;

    protected static ?int $navigationSort = 100;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('cms.nav.groups.settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('cms.nav.developer_tools');
    }

    public function getTitle(): string|Htmlable
    {
        return __('cms.nav.developer_tools');
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user !== null && $user->hasRole('super-admin');
    }

    public function mount(SiteSettingsService $settings): void
    {
        $noCache = $settings->has('developer.no_cache')
            ? $settings->isTruthy('developer.no_cache')
            : (bool) config('developer.no_cache');

        $this->form->fill([
            'developer' => [
                'no_cache' => $noCache,
            ],
        ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            $this->getFormContentComponent(),
        ]);
    }

    public function getFormContentComponent(): Form
    {
        return Form::make([EmbeddedSchema::make('form')])
            ->id('developer-tools-form')
            ->livewireSubmitHandler('save')
            ->footer([
                Actions::make([
                    Action::make('save')
                        ->label(__('cms.actions.save_settings'))
                        ->submit('save')
                        ->keyBindings(['mod+s']),
                    Action::make('clearCache')
                        ->label(__('cms.actions.clear_cache'))
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action('clearCache'),
                ]),
            ]);
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('cms.sections.developer_cache'))
                ->description(__('cms.helpers.developer_cache'))
                ->schema([
                    Toggle::make('developer.no_cache')
                        ->label(__('cms.fields.no_cache'))
                        ->helperText(__('cms.helpers.no_cache')),
                ]),
        ]);
    }

    public function save(SiteSettingsService $settings): void
    {
        $settings->updateFromFormState($this->form->getState());

        Notification::make()
            ->title(__('cms.notifications.developer_tools_saved'))
            ->success()
            ->send();
    }

    public function clearCache(ApplicationCacheClearer $clearer): void
    {
        $clearer->clearAll();

        Notification::make()
            ->title(__('cms.notifications.cache_cleared'))
            ->success()
            ->send();
    }
}
