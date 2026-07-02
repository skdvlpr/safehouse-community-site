<?php

namespace App\Filament\Pages;

use App\Services\SiteSettingsService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Group;
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

    protected static ?string $navigationLabel = 'Integrations';

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 99;

    protected static ?string $title = 'Integrations';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user !== null && $user->hasRole('super-admin');
    }

    public function mount(SiteSettingsService $settings): void
    {
        $this->form->fill($settings->nestedFormValues());
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
                        ->label('Save settings')
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
                Tab::make('Stripe')->schema([
                    Section::make('Stripe payments')->schema([
                        \Filament\Forms\Components\TextInput::make('stripe.key')
                            ->label('Publishable key')
                            ->helperText('pk_test_… for testing, pk_live_… for production.')
                            ->maxLength(255),
                        \Filament\Forms\Components\TextInput::make('stripe.secret')
                            ->label('Secret key')
                            ->password()
                            ->revealable()
                            ->helperText('Stored encrypted. Leave blank to keep the current value unchanged on save.')
                            ->dehydrated(fn (?string $state): bool => filled($state)),
                        \Filament\Forms\Components\TextInput::make('stripe.webhook_secret')
                            ->label('Webhook signing secret')
                            ->password()
                            ->revealable()
                            ->helperText('whsec_… from Stripe Dashboard or stripe listen.')
                            ->dehydrated(fn (?string $state): bool => filled($state)),
                        \Filament\Forms\Components\TextInput::make('stripe.currency')
                            ->label('Default currency')
                            ->default('EUR')
                            ->maxLength(3),
                        \Filament\Forms\Components\TextInput::make('stripe.statement_descriptor')
                            ->label('Statement descriptor suffix')
                            ->helperText('Appended on donor card statements (max 22 chars). Stripe uses statement_descriptor_suffix on PaymentIntents.')
                            ->maxLength(22),
                        \Filament\Forms\Components\TextInput::make('stripe.account_id')
                            ->label('Account id (reference)')
                            ->placeholder('acct_…')
                            ->helperText('Optional. Shown in Dashboard URL; verified by php artisan stripe:verify.')
                            ->maxLength(255),
                        \Filament\Forms\Components\TextInput::make('stripe.account_name')
                            ->label('Account name (reference)')
                            ->placeholder('Safe House Donorbox')
                            ->helperText('Optional label for your team — not sent to Stripe API.')
                            ->maxLength(255),
                    ]),
                ]),
                Tab::make('EspoCRM')->schema([
                    Section::make('CRM API')->schema([
                        \Filament\Forms\Components\TextInput::make('espocrm.base_url')
                            ->label('Base URL')
                            ->url()
                            ->placeholder('https://crm.safehouse.community'),
                        \Filament\Forms\Components\TextInput::make('espocrm.api_key')
                            ->label('API key')
                            ->password()
                            ->revealable()
                            ->dehydrated(fn (?string $state): bool => filled($state)),
                        \Filament\Forms\Components\TextInput::make('espocrm.assigned_user_id')
                            ->label('Assigned user id')
                            ->helperText('CRM user id for Prima Nota ownership. Can differ from the API key user. Verify with php artisan espo:verify.'),
                    ]),
                    Section::make('Prima Nota defaults')->schema([
                        \Filament\Forms\Components\TextInput::make('espocrm.prima_nota.default_beneficiary_name')
                            ->label('Beneficiary name')
                            ->helperText('Account name lookup in CRM, e.g. Safe House.')
                            ->maxLength(255),
                        \Filament\Forms\Components\TextInput::make('espocrm.prima_nota.default_subject_name')
                            ->label('Default payer name')
                            ->helperText('Fallback when donor name is empty.')
                            ->maxLength(255),
                    ]),
                ]),
            ]),
        ]);
    }

    public function save(SiteSettingsService $settings): void
    {
        $settings->updateFromFormState($this->form->getState());
        $this->form->fill($settings->nestedFormValues());

        Notification::make()
            ->title('Integration settings saved')
            ->success()
            ->send();
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Values saved here override .env on the server. Secrets are encrypted in the database.';
    }
}
