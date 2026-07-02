<?php

namespace App\Filament\Resources\PageResource\Pages;

use App\Filament\Resources\PageResource;
use App\Filament\Support\PrimaryTaglineFormFields;
use App\Services\SiteContentService;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Actions as FormActions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\RenderHook;
use Filament\Schemas\Schema;
use Filament\View\PanelsRenderHook;

class ListPages extends ListRecords
{
    protected static string $resource = PageResource::class;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $taglineData = [];

    public function mount(): void
    {
        parent::mount();

        $this->siteTaglineForm->fill(app(SiteContentService::class)->nestedFormValues());
    }

    public function defaultSiteTaglineForm(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->statePath('taglineData');
    }

    public function siteTaglineForm(Schema $schema): Schema
    {
        return $schema->components(PrimaryTaglineFormFields::section());
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([EmbeddedSchema::make('siteTaglineForm')])
                    ->id('pages-primary-tagline-form')
                    ->livewireSubmitHandler('saveTagline')
                    ->footer([
                        FormActions::make([
                            Action::make('saveTagline')
                                ->label(__('cms.actions.save_tagline'))
                                ->submit('saveTagline')
                                ->keyBindings(['mod+shift+s']),
                        ]),
                    ]),
                $this->getTabsContentComponent(),
                RenderHook::make(PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_BEFORE),
                EmbeddedTable::make(),
                RenderHook::make(PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_AFTER),
            ]);
    }

    public function saveTagline(SiteContentService $content): void
    {
        $content->updateFromFormState($this->siteTaglineForm->getState());
        $this->siteTaglineForm->fill($content->nestedFormValues());

        Notification::make()
            ->title(__('cms.notifications.tagline_saved'))
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
