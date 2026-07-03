<?php

namespace App\Filament\Pages;

use App\Filament\Support\CmsLocaleTabs;
use App\Services\DonationSettingsService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
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
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use UnitEnum;

class ManageDonationsConfig extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?int $navigationSort = 2;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('cms.nav.groups.fundraising');
    }

    public static function getNavigationLabel(): string
    {
        return __('cms.nav.donations_config');
    }

    public function getTitle(): string|Htmlable
    {
        return __('cms.nav.donations_config');
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user !== null && $user->hasRole('super-admin');
    }

    public function mount(DonationSettingsService $donations): void
    {
        $this->form->fill($donations->nestedFormValues());
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
            ->id('donations-config-form')
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

        return $schema->components([
            Tabs::make('DonationsConfigTabs')->tabs([
                Tab::make(__('cms.sections.five_per_mille'))->schema([
                    Section::make(__('cms.sections.five_per_mille'))->schema(array_merge([
                        Toggle::make('donations.five_per_mille.enabled')
                            ->label(__('cms.fields.five_per_mille_enabled'))
                            ->helperText(__('cms.helpers.five_per_mille_enabled'))
                            ->default(true)
                            ->inline(false),
                        TextInput::make('donations.five_per_mille.codice_fiscale')
                            ->label(__('cms.fields.codice_fiscale'))
                            ->helperText(__('cms.helpers.codice_fiscale'))
                            ->maxLength(16)
                            ->extraInputAttributes(['class' => 'font-mono uppercase'])
                            ->columnSpanFull(),
                    ], $this->fivePerMilleLocaleTabs($locales))),
                ]),
                Tab::make(__('cms.sections.bank_transfer'))->schema([
                    Section::make(__('cms.sections.bank_transfer'))->schema(array_merge([
                        Toggle::make('donations.bank_transfer.enabled')
                            ->label(__('cms.fields.bank_transfer_enabled'))
                            ->helperText(__('cms.helpers.bank_transfer_enabled'))
                            ->default(true)
                            ->inline(false),
                        TextInput::make('donations.bank_transfer.iban')
                            ->label(__('cms.fields.donation_iban'))
                            ->helperText(__('cms.helpers.donation_iban'))
                            ->maxLength(34)
                            ->extraInputAttributes(['class' => 'font-mono uppercase'])
                            ->columnSpanFull(),
                        TextInput::make('donations.bank_transfer.beneficiary')
                            ->label(__('cms.fields.donation_beneficiary'))
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ], $this->bankTransferLocaleTabs($locales))),
                ]),
            ]),
        ]);
    }

    /**
     * @param  list<string>  $locales
     * @return list<\Filament\Schemas\Components\Component>
     */
    private function fivePerMilleLocaleTabs(array $locales): array
    {
        $components = [];

        foreach ($locales as $locale) {
            $label = CmsLocaleTabs::label($locale);

            $components[] = Tabs::make('FivePerMille_'.$locale)->tabs([
                Tab::make($label)->schema([
                    TextInput::make("donations.five_per_mille.menu_label.{$locale}")
                        ->label(__('cms.fields.five_per_mille_menu_label'))
                        ->maxLength(80),
                    TextInput::make("donations.five_per_mille.heading.{$locale}")
                        ->label(__('cms.fields.five_per_mille_heading'))
                        ->maxLength(120),
                    Textarea::make("donations.five_per_mille.lead.{$locale}")
                        ->label(__('cms.fields.five_per_mille_lead'))
                        ->rows(3)
                        ->maxLength(500)
                        ->columnSpanFull(),
                    RichEditor::make("donations.five_per_mille.body.{$locale}")
                        ->label(__('cms.fields.five_per_mille_body'))
                        ->columnSpanFull(),
                    RichEditor::make("donations.five_per_mille.instructions.{$locale}")
                        ->label(__('cms.fields.five_per_mille_instructions'))
                        ->columnSpanFull(),
                    TextInput::make("donations.five_per_mille.codice_label.{$locale}")
                        ->label(__('cms.fields.five_per_mille_codice_label'))
                        ->maxLength(120),
                ]),
            ]);
        }

        return $components;
    }

    /**
     * @param  list<string>  $locales
     * @return list<\Filament\Schemas\Components\Component>
     */
    private function bankTransferLocaleTabs(array $locales): array
    {
        $components = [];

        foreach ($locales as $locale) {
            $label = CmsLocaleTabs::label($locale);

            $components[] = Tabs::make('BankTransfer_'.$locale)->tabs([
                Tab::make($label)->schema([
                    TextInput::make("donations.bank_transfer.heading.{$locale}")
                        ->label(__('cms.fields.bank_transfer_heading'))
                        ->maxLength(120),
                    RichEditor::make("donations.bank_transfer.body.{$locale}")
                        ->label(__('cms.fields.bank_transfer_body'))
                        ->columnSpanFull(),
                    TextInput::make("donations.bank_transfer.iban_label.{$locale}")
                        ->label(__('cms.fields.bank_transfer_iban_label'))
                        ->maxLength(40),
                    TextInput::make("donations.bank_transfer.beneficiary_label.{$locale}")
                        ->label(__('cms.fields.bank_transfer_beneficiary_label'))
                        ->maxLength(80),
                ]),
            ]);
        }

        return $components;
    }

    public function save(DonationSettingsService $donations): void
    {
        $state = $this->form->getState();
        $donations->saveFromFormState(is_array($state) ? $state : []);

        $this->form->fill($donations->nestedFormValues());

        Notification::make()
            ->title(__('cms.notifications.donations_config_saved'))
            ->success()
            ->send();
    }
}
