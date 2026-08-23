<?php

namespace App\Filament\Pages;

use App\Services\SiteAppearanceSettings;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
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

class ManageSiteAppearance extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static ?int $navigationSort = 95;

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
        return __('cms.nav.appearance');
    }

    public function getTitle(): string|Htmlable
    {
        return __('cms.nav.appearance');
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user !== null && $user->hasRole('super-admin');
    }

    public function mount(SiteAppearanceSettings $appearance): void
    {
        $appearance->ensureStockInLibrary();
        $this->form->fill($appearance->nestedFormValues());
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
            ->id('site-appearance-form')
            ->livewireSubmitHandler('save')
            ->footer([
                Actions::make([
                    Action::make('save')
                        ->label(__('cms.actions.save_settings'))
                        ->submit('save')
                        ->keyBindings(['mod+s']),
                    Action::make('restoreDefault')
                        ->label(__('cms.actions.restore_aurora_background'))
                        ->color('gray')
                        ->requiresConfirmation()
                        ->modalHeading(__('cms.actions.restore_aurora_background'))
                        ->modalDescription(__('cms.helpers.restore_aurora_background'))
                        ->action('restoreDefault'),
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
        $appearance = app(SiteAppearanceSettings::class);

        return $schema->components([
            Section::make(__('cms.sections.site_background'))
                ->description(__('cms.helpers.site_background'))
                ->schema([
                    FileUpload::make('appearance.background')
                        ->label(__('cms.fields.site_background'))
                        ->helperText(__('cms.helpers.site_background_upload'))
                        ->acceptedFileTypes($appearance->acceptedMimeTypes())
                        ->imagePreviewHeight('180')
                        ->panelAspectRatio('16:9')
                        ->panelLayout('integrated')
                        ->disk((string) config('site_appearance.disk', 'public'))
                        ->directory((string) config('site_appearance.directory', 'site-appearance'))
                        ->visibility('public')
                        ->nullable()
                        ->maxSize((int) config('site_appearance.max_size_kb', 8192))
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public function save(SiteAppearanceSettings $appearance): void
    {
        $appearance->saveFromFormState($this->form->getState());
        $this->form->fill($appearance->nestedFormValues());

        Notification::make()
            ->title(__('cms.notifications.site_appearance_saved'))
            ->success()
            ->send();
    }

    public function restoreDefault(SiteAppearanceSettings $appearance): void
    {
        $appearance->clearBackground();
        $this->form->fill($appearance->nestedFormValues());

        Notification::make()
            ->title(__('cms.notifications.site_appearance_restored'))
            ->success()
            ->send();
    }
}
