<?php

namespace App\Filament\Pages;

use App\Services\ContactDeskSettings;
use App\Services\SiteSettingsService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use UnitEnum;

class ManageIntegrations extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedKey;

    protected static ?int $navigationSort = 99;

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
        return __('cms.nav.integrations');
    }

    public function getTitle(): string
    {
        return __('cms.nav.integrations');
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user !== null && $user->hasRole('super-admin');
    }

    public function mount(SiteSettingsService $settings, ContactDeskSettings $desks): void
    {
        $values = $settings->nestedFormValues();
        data_set($values, 'contact.desks', $desks->all());
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
            ->id('integrations-form')
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
            Tabs::make('IntegrationTabs')->tabs([
                Tab::make(__('cms.integrations.stripe'))->schema([
                    Section::make(__('cms.sections.stripe_payments'))->schema([
                        \Filament\Forms\Components\TextInput::make('stripe.key')
                            ->label(__('cms.fields.publishable_key'))
                            ->helperText(__('cms.helpers.stripe_publishable'))
                            ->maxLength(255),
                        \Filament\Forms\Components\TextInput::make('stripe.secret')
                            ->label(__('cms.fields.secret_key'))
                            ->password()
                            ->revealable()
                            ->helperText(__('cms.helpers.stripe_secret'))
                            ->dehydrated(fn (?string $state): bool => filled($state)),
                        \Filament\Forms\Components\TextInput::make('stripe.webhook_secret')
                            ->label(__('cms.fields.webhook_secret'))
                            ->password()
                            ->revealable()
                            ->helperText(__('cms.helpers.stripe_webhook_live'))
                            ->dehydrated(fn (?string $state): bool => filled($state)),
                        \Filament\Forms\Components\TextInput::make('stripe.currency')
                            ->label(__('cms.fields.default_currency'))
                            ->default('EUR')
                            ->maxLength(3),
                        \Filament\Forms\Components\TextInput::make('stripe.statement_descriptor')
                            ->label(__('cms.fields.statement_descriptor'))
                            ->helperText(__('cms.helpers.stripe_descriptor'))
                            ->maxLength(22),
                        \Filament\Forms\Components\TextInput::make('stripe.account_id')
                            ->label(__('cms.fields.account_id'))
                            ->placeholder('acct_…')
                            ->helperText(__('cms.helpers.stripe_account_id'))
                            ->maxLength(255),
                        \Filament\Forms\Components\TextInput::make('stripe.account_name')
                            ->label(__('cms.fields.account_name'))
                            ->placeholder('Safe House Donorbox')
                            ->helperText(__('cms.helpers.stripe_account_name'))
                            ->maxLength(255),
                    ]),
                ]),
                Tab::make(__('cms.integrations.espocrm'))->schema([
                    Section::make(__('cms.sections.crm_api'))->schema([
                        \Filament\Forms\Components\TextInput::make('espocrm.base_url')
                            ->label(__('cms.fields.base_url'))
                            ->url()
                            ->placeholder('https://crm.safehouse.community'),
                        \Filament\Forms\Components\TextInput::make('espocrm.api_key')
                            ->label(__('cms.fields.api_key'))
                            ->password()
                            ->revealable()
                            ->dehydrated(fn (?string $state): bool => filled($state)),
                        \Filament\Forms\Components\TextInput::make('espocrm.assigned_user_id')
                            ->label(__('cms.fields.assigned_user_id'))
                            ->helperText(__('cms.helpers.espocrm_assigned_user')),
                    ]),
                    Section::make(__('cms.sections.prima_nota_defaults'))->schema([
                        \Filament\Forms\Components\TextInput::make('espocrm.prima_nota.default_beneficiary_name')
                            ->label(__('cms.fields.beneficiary_name'))
                            ->helperText(__('cms.helpers.beneficiary_name'))
                            ->maxLength(255),
                        \Filament\Forms\Components\TextInput::make('espocrm.prima_nota.default_subject_name')
                            ->label(__('cms.fields.default_payer_name'))
                            ->helperText(__('cms.helpers.default_payer_name'))
                            ->maxLength(255),
                    ]),
                ]),
                Tab::make(__('cms.integrations.mail'))->schema([
                    Section::make(__('cms.sections.smtp'))->schema([
                        \Filament\Forms\Components\Select::make('mail.provider_preset')
                            ->label(__('cms.fields.smtp_provider_preset'))
                            ->options($this->mailProviderOptions())
                            ->placeholder(__('cms.fields.smtp_provider_custom'))
                            ->helperText(__('cms.helpers.smtp_provider_preset'))
                            ->dehydrated(false),
                        Actions::make([
                            Action::make('applyMailProviderPreset')
                                ->label(__('cms.actions.apply_smtp_preset'))
                                ->action('applyMailProviderPreset'),
                        ]),
                        \Filament\Forms\Components\TextInput::make('mail.host')
                            ->label(__('cms.fields.smtp_host'))
                            ->placeholder('smtp.example.com')
                            ->helperText(__('cms.helpers.smtp_host'))
                            ->maxLength(255),
                        \Filament\Forms\Components\TextInput::make('mail.port')
                            ->label(__('cms.fields.smtp_port'))
                            ->numeric()
                            ->default(587)
                            ->helperText(__('cms.helpers.smtp_port')),
                        \Filament\Forms\Components\Select::make('mail.encryption')
                            ->label(__('cms.fields.smtp_encryption'))
                            ->options([
                                'tls' => 'TLS (STARTTLS, porta 587)',
                                'ssl' => 'SSL (SMTPS, porta 465)',
                                'none' => __('cms.fields.smtp_encryption_none'),
                            ])
                            ->default('tls'),
                        \Filament\Forms\Components\TextInput::make('mail.username')
                            ->label(__('cms.fields.smtp_username'))
                            ->maxLength(255),
                        \Filament\Forms\Components\TextInput::make('mail.password')
                            ->label(__('cms.fields.smtp_password'))
                            ->password()
                            ->revealable()
                            ->helperText(__('cms.helpers.smtp_password'))
                            ->dehydrated(fn (?string $state): bool => filled($state)),
                    ]),
                    Section::make(__('cms.sections.mail_sender'))->schema([
                        \Filament\Forms\Components\TextInput::make('mail.from_address')
                            ->label(__('cms.fields.from_address'))
                            ->email()
                            ->placeholder('noreply@safehouse.community')
                            ->helperText(__('cms.helpers.from_address'))
                            ->maxLength(255),
                        \Filament\Forms\Components\TextInput::make('mail.from_name')
                            ->label(__('cms.fields.from_name'))
                            ->placeholder('Safe House')
                            ->maxLength(255),
                    ]),
                    Section::make(__('cms.sections.contact_notifications'))->schema([
                        \Filament\Forms\Components\TextInput::make('contact.website_from_address')
                            ->label(__('cms.fields.contact_website_from_address'))
                            ->email()
                            ->placeholder('website@safehouse.community')
                            ->helperText(__('cms.helpers.contact_website_from_address'))
                            ->maxLength(255),
                        \Filament\Forms\Components\TextInput::make('contact.website_from_name')
                            ->label(__('cms.fields.contact_website_from_name'))
                            ->placeholder('Safe House — sito web')
                            ->maxLength(255),
                    ]),
                    Section::make(__('cms.sections.contact_desks'))->schema([
                        \Filament\Forms\Components\Repeater::make('contact.desks')
                            ->label(__('cms.fields.contact_desks'))
                            ->helperText(__('cms.helpers.contact_desks'))
                            ->schema([
                                \Filament\Forms\Components\TextInput::make('key')
                                    ->label(__('cms.fields.contact_desk_key'))
                                    ->helperText(__('cms.helpers.contact_desk_key'))
                                    ->required()
                                    ->maxLength(64)
                                    ->alphaDash(),
                                \Filament\Forms\Components\TextInput::make('label')
                                    ->label(__('cms.fields.contact_desk_label'))
                                    ->required()
                                    ->maxLength(255),
                                \Filament\Forms\Components\TextInput::make('inbox')
                                    ->label(__('cms.fields.contact_desk_inbox'))
                                    ->email()
                                    ->required()
                                    ->placeholder('sportello.digitale@safehouse.community')
                                    ->maxLength(255),
                                \Filament\Forms\Components\TextInput::make('case_type')
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
            ]),
        ]);
    }

    public function save(SiteSettingsService $settings, ContactDeskSettings $deskSettings): void
    {
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

        if (isset($state['contact']) && is_array($state['contact'])) {
            unset($state['contact']['desks']);
        }

        $settings->updateFromFormState($state);

        $values = $settings->nestedFormValues();
        data_set($values, 'contact.desks', $deskSettings->all());
        $this->form->fill($values);

        Notification::make()
            ->title(__('cms.notifications.integrations_saved'))
            ->success()
            ->send();
    }

    public function applyMailProviderPreset(): void
    {
        $presetKey = (string) data_get($this->data, 'mail.provider_preset', '');
        $preset = config("mail_providers.providers.{$presetKey}");

        if (! is_array($preset)) {
            Notification::make()
                ->title(__('cms.notifications.smtp_preset_missing'))
                ->warning()
                ->send();

            return;
        }

        data_set($this->data, 'mail.host', (string) ($preset['host'] ?? ''));
        data_set($this->data, 'mail.port', (string) ($preset['port'] ?? ''));
        data_set($this->data, 'mail.encryption', (string) ($preset['encryption'] ?? 'tls'));

        $this->form->fill($this->data);

        $detail = (string) ($preset['hint'] ?? '');
        Notification::make()
            ->title(__('cms.notifications.smtp_preset_applied', ['provider' => (string) ($preset['label'] ?? $presetKey)]))
            ->body($detail !== '' ? $detail : null)
            ->success()
            ->send();
    }

    /**
     * @return array<string, string>
     */
    private function mailProviderOptions(): array
    {
        $options = [];

        foreach ((array) config('mail_providers.providers', []) as $key => $preset) {
            if (! is_array($preset)) {
                continue;
            }

            $options[$key] = (string) ($preset['label'] ?? $key);
        }

        return $options;
    }

    public function getSubheading(): string|Htmlable|null
    {
        return __('cms.helpers.integrations_subheading');
    }
}
