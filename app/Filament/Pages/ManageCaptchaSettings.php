<?php

namespace App\Filament\Pages;

use App\Services\SiteSettingsService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use UnitEnum;

class ManageCaptchaSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static ?int $navigationSort = 98;

    protected static ?string $slug = 'captcha';

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
        return __('cms.nav.captcha');
    }

    public function getTitle(): string|Htmlable
    {
        return __('cms.nav.captcha');
    }

    public function getSubheading(): string|Htmlable|null
    {
        return __('cms.helpers.captcha_subheading');
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user !== null && $user->hasRole('super-admin');
    }

    public function mount(SiteSettingsService $settings): void
    {
        $this->form->fill([
            'turnstile' => [
                'enabled' => $settings->has('turnstile.enabled')
                    ? $settings->isTruthy('turnstile.enabled')
                    : false,
                'site_key' => (string) ($settings->getRaw('turnstile.site_key') ?? ''),
                'secret_key' => '',
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
            ->id('captcha-settings-form')
            ->livewireSubmitHandler('save')
            ->footer([
                Actions::make([
                    Action::make('save')
                        ->label(__('cms.actions.save_settings'))
                        ->submit('save')
                        ->keyBindings(['mod+s']),
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
            Section::make(__('cms.sections.contact_captcha'))->schema([
                Toggle::make('turnstile.enabled')
                    ->label(__('cms.fields.turnstile_enabled'))
                    ->helperText(__('cms.helpers.turnstile_enabled'))
                    ->default(false)
                    ->inline(false)
                    ->live(),
                TextInput::make('turnstile.site_key')
                    ->label(__('cms.fields.turnstile_site_key'))
                    ->helperText(__('cms.helpers.turnstile_site_key'))
                    ->maxLength(255)
                    ->visible(fn (Get $get): bool => (bool) $get('turnstile.enabled')),
                TextInput::make('turnstile.secret_key')
                    ->label(__('cms.fields.turnstile_secret_key'))
                    ->password()
                    ->revealable()
                    ->helperText(__('cms.helpers.turnstile_secret_key'))
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->visible(fn (Get $get): bool => (bool) $get('turnstile.enabled')),
            ]),
        ]);
    }

    public function save(SiteSettingsService $settings): void
    {
        $flat = $settings->flattenFormState($this->form->getState());
        $payload = array_intersect_key($flat, array_flip([
            'turnstile.enabled',
            'turnstile.site_key',
            'turnstile.secret_key',
        ]));

        $settings->updateMany($payload);

        $this->form->fill([
            'turnstile' => [
                'enabled' => $settings->has('turnstile.enabled')
                    ? $settings->isTruthy('turnstile.enabled')
                    : false,
                'site_key' => (string) ($settings->getRaw('turnstile.site_key') ?? ''),
                'secret_key' => '',
            ],
        ]);

        Notification::make()
            ->title(__('cms.notifications.captcha_saved'))
            ->success()
            ->send();
    }
}
