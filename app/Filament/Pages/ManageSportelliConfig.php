<?php

namespace App\Filament\Pages;

use App\Filament\Support\CmsLocaleTabs;
use App\Services\ContactDeskSettings;
use App\Services\ContactSportelloMailSettings;
use App\Services\SiteSettingsService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use UnitEnum;

class ManageSportelliConfig extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInboxStack;

    protected static ?int $navigationSort = 96;

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
        return __('cms.nav.sportelli_config');
    }

    public function getTitle(): string|Htmlable
    {
        return __('cms.nav.sportelli_config');
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user !== null && $user->hasRole('super-admin');
    }

    public function mount(
        ContactDeskSettings $desks,
        ContactSportelloMailSettings $mailSettings,
        SiteSettingsService $settings,
    ): void {
        $values = array_merge(
            $mailSettings->nestedFormValues(),
            $settings->nestedFormValues(),
        );

        data_set($values, 'contact.desks', $desks->all());

        data_set(
            $values,
            'turnstile.enabled',
            $settings->has('turnstile.enabled') ? $settings->isTruthy('turnstile.enabled') : false,
        );

        $this->form->fill($values);
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
            ->id('sportelli-config-form')
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
        $locales = config('locales.available', ['it', 'ru', 'en']);
        $mailTabs = [];

        foreach ($locales as $locale) {
            $mailTabs[] = Tab::make(CmsLocaleTabs::label($locale))
                ->schema([
                    TextInput::make("sportello_mail.subject.{$locale}")
                        ->label(__('cms.fields.sportello_mail_subject'))
                        ->helperText(__('cms.helpers.sportello_mail_subject'))
                        ->maxLength(255)
                        ->columnSpanFull(),
                    RichEditor::make("sportello_mail.body.{$locale}")
                        ->label(__('cms.fields.sportello_mail_body'))
                        ->helperText(__('cms.helpers.sportello_mail_body'))
                        ->columnSpanFull(),
                ]);
        }

        $placeholderList = implode(', ', config('contact_sportello_mail.placeholders', []));

        return $schema->components([
            Tabs::make('SportelliConfigTabs')->tabs([
                Tab::make(__('cms.sections.contact_desks'))->schema([
                    Section::make(__('cms.sections.contact_desks'))->schema([
                        Repeater::make('contact.desks')
                            ->label(__('cms.fields.contact_desks'))
                            ->helperText(__('cms.helpers.contact_desks'))
                            ->schema([
                                TextInput::make('key')
                                    ->label(__('cms.fields.contact_desk_key'))
                                    ->helperText(__('cms.helpers.contact_desk_key'))
                                    ->required()
                                    ->maxLength(64)
                                    ->alphaDash(),
                                TextInput::make('label')
                                    ->label(__('cms.fields.contact_desk_label'))
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('inbox')
                                    ->label(__('cms.fields.contact_desk_inbox'))
                                    ->email()
                                    ->required()
                                    ->placeholder('sportello.digitale@safehouse.community')
                                    ->maxLength(255),
                                TextInput::make('case_type')
                                    ->label(__('cms.fields.contact_desk_case_type'))
                                    ->helperText(__('cms.helpers.contact_desk_case_type'))
                                    ->required()
                                    ->placeholder('SportelloDigitale')
                                    ->maxLength(64),
                            ])
                            ->minItems(1)
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['label'] ?? $state['key'] ?? null)
                            ->addActionLabel(__('cms.actions.add_contact_desk'))
                            ->columns(2),
                    ]),
                ]),
                Tab::make(__('cms.sections.sportello_mail'))->schema([
                    Section::make(__('cms.sections.sportello_mail'))->schema([
                        Placeholder::make('sportello_mail_placeholders')
                            ->label(__('cms.fields.sportello_mail_placeholders'))
                            ->content($placeholderList)
                            ->columnSpanFull(),
                        Tabs::make('SportelloMailLocales')->tabs($mailTabs),
                    ]),
                ]),
                Tab::make(__('cms.sections.contact_captcha'))->schema([
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
                ]),
            ]),
        ]);
    }

    public function save(
        SiteSettingsService $settings,
        ContactDeskSettings $deskSettings,
        ContactSportelloMailSettings $mailSettings,
    ): void {
        $state = $this->form->getState();
        $desks = data_get($state, 'contact.desks', []);

        try {
            $deskSettings->save(is_array($desks) ? $desks : []);
        } catch (\InvalidArgumentException $exception) {
            Notification::make()
                ->title(__('cms.notifications.contact_desks_invalid'))
                ->body($exception->getMessage())
                ->danger()
                ->send();

            return;
        }

        $mailSettings->saveFromFormState(is_array($state) ? $state : []);

        if (isset($state['contact']) && is_array($state['contact'])) {
            unset($state['contact']['desks']);
        }

        if (isset($state['sportello_mail'])) {
            unset($state['sportello_mail']);
        }

        $settings->updateFromFormState(is_array($state) ? $state : []);

        $values = array_merge(
            $mailSettings->nestedFormValues(),
            $settings->nestedFormValues(),
        );
        data_set($values, 'contact.desks', $deskSettings->all());
        data_set(
            $values,
            'turnstile.enabled',
            $settings->has('turnstile.enabled') ? $settings->isTruthy('turnstile.enabled') : false,
        );
        $this->form->fill($values);

        Notification::make()
            ->title(__('cms.notifications.sportelli_config_saved'))
            ->success()
            ->send();
    }
}
