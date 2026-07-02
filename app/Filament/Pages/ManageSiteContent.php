<?php

namespace App\Filament\Pages;

use App\Services\SiteContentService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
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

class ManageSiteContent extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAlt;

    protected static ?string $navigationLabel = 'Site content';

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 10;

    protected static ?string $title = 'Site content';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user !== null && $user->hasRole('super-admin');
    }

    public function mount(SiteContentService $content): void
    {
        $this->form->fill($content->formValues());
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
            ->id('site-content-form')
            ->livewireSubmitHandler('save')
            ->footer([
                Actions::make([
                    Action::make('save')
                        ->label('Save content')
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
        $localeLabels = [
            'it' => '🇮🇹 Italiano',
            'ru' => '🇷🇺 Русский',
            'en' => '🇬🇧 English',
        ];

        $tabs = [];

        foreach ($locales as $locale) {
            $tabs[] = Tab::make($localeLabels[$locale] ?? strtoupper($locale))
                ->schema([
                    Textarea::make("content.primary_tagline.{$locale}")
                        ->label('Primary tagline')
                        ->helperText('Footer tagline and subtitle on the home page under the main title.')
                        ->rows(2)
                        ->maxLength(500),
                ]);
        }

        return $schema->components([
            Section::make('Primary tagline')
                ->description('One shared slogan for the footer and the home page hero.')
                ->schema([
                    Tabs::make('TaglineLocales')->tabs($tabs),
                ]),
        ]);
    }

    public function save(SiteContentService $content): void
    {
        $content->updateMany($this->form->getState());
        $this->form->fill($content->formValues());

        Notification::make()
            ->title('Site content saved')
            ->success()
            ->send();
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Global copy used across the public site. Page-specific banners and labels are edited under Content → Pages.';
    }
}
