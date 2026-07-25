<?php

namespace App\Filament\Pages;

use App\Filament\Support\CmsLocaleTabs;
use App\Services\RecurringDonationCampaignService;
use BackedEnum;
use Filament\Actions\Action;
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
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use UnitEnum;

/**
 * CMS singleton for the recurring donation campaign (no fundraising goal).
 * Mirrors ManageDonationsConfig: toggle + localized title/description.
 */
class ManageRecurringDonation extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowPath;

    protected static ?int $navigationSort = 1;

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
        return __('cms.nav.recurring_donations');
    }

    public function getTitle(): string|Htmlable
    {
        return __('cms.nav.recurring_donations');
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user !== null && $user->hasRole('super-admin');
    }

    public function mount(RecurringDonationCampaignService $recurring): void
    {
        $this->form->fill($recurring->formValues());
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
            ->id('recurring-donation-form')
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
            Section::make(__('cms.sections.recurring_donation'))->schema(array_merge([
                Toggle::make('enabled')
                    ->label(__('cms.fields.recurring_donation_enabled'))
                    ->helperText(__('cms.helpers.recurring_donation_enabled'))
                    ->default(true)
                    ->inline(false),
            ], $this->localeTabs($locales))),
        ]);
    }

    /**
     * @param  list<string>  $locales
     * @return list<\Filament\Schemas\Components\Component>
     */
    private function localeTabs(array $locales): array
    {
        $components = [];

        foreach ($locales as $locale) {
            $label = CmsLocaleTabs::label($locale);

            $components[] = Tabs::make('RecurringDonation_'.$locale)->tabs([
                Tab::make($label)->schema([
                    TextInput::make("title.{$locale}")
                        ->label(__('cms.fields.title'))
                        ->maxLength(255)
                        ->required($locale === 'it'),
                    RichEditor::make("description.{$locale}")
                        ->label(__('cms.fields.description'))
                        ->columnSpanFull(),
                ]),
            ]);
        }

        return $components;
    }

    public function save(RecurringDonationCampaignService $recurring): void
    {
        $state = $this->form->getState();
        $recurring->saveFromFormState(is_array($state) ? $state : []);

        $this->form->fill($recurring->formValues());

        Notification::make()
            ->title(__('cms.notifications.recurring_donation_saved'))
            ->success()
            ->send();
    }
}
