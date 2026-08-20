<?php

namespace App\Filament\Pages;

use App\Services\SocialLinksSettings;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
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

class ManageSocialLinks extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShare;

    protected static ?int $navigationSort = 97;

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
        return __('cms.nav.social');
    }

    public function getTitle(): string|Htmlable
    {
        return __('cms.nav.social');
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user !== null && $user->hasRole('super-admin');
    }

    public function mount(SocialLinksSettings $social): void
    {
        $this->form->fill($social->nestedFormValues());
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
            ->id('social-links-form')
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
        $fields = [];

        foreach (config('social_links.networks', []) as $key => $definition) {
            if (! is_string($key) || ! is_array($definition)) {
                continue;
            }

            $type = (string) ($definition['type'] ?? 'url');

            $fields[] = TextInput::make("social.{$key}")
                ->label(__('cms.fields.social_'.$key))
                ->helperText(__('cms.helpers.social_'.$key))
                ->placeholder((string) ($definition['placeholder'] ?? ''))
                ->url($type === 'url')
                ->email($type === 'email')
                ->maxLength(255);
        }

        return $schema->components([
            Section::make(__('cms.sections.social_links'))
                ->description(__('cms.helpers.social_links'))
                ->schema($fields),
        ]);
    }

    public function save(SocialLinksSettings $social): void
    {
        $social->saveFromFormState($this->form->getState());
        $this->form->fill($social->nestedFormValues());

        Notification::make()
            ->title(__('cms.notifications.social_links_saved'))
            ->success()
            ->send();
    }
}
