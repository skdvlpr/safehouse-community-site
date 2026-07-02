<?php

namespace App\Filament\Resources\PageResource\Actions;

use App\Models\Page;
use App\Services\PageService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Livewire\Component;

class PreviewPageAction
{
    public static function make(): Action
    {
        return Action::make('preview')
            ->label(__('cms.actions.preview'))
            ->icon('heroicon-o-eye')
            ->color('gray')
            ->form([
                Select::make('locale')
                    ->label(__('cms.fields.language'))
                    ->options(
                        collect(config('locales.available', ['it']))
                            ->mapWithKeys(fn (string $locale): array => [
                                $locale => strtoupper($locale),
                            ])
                            ->all()
                    )
                    ->default('it')
                    ->required(),
            ])
            ->modalSubmitActionLabel(__('cms.actions.open_preview'))
            ->modalDescription(__('cms.preview.description'))
            ->action(function (array $data, Page $record, Action $action): void {
                $url = app(PageService::class)->previewUrl($record, $data['locale']);

                if ($url === null) {
                    Notification::make()
                        ->title(__('cms.preview.unavailable'))
                        ->body(__('cms.preview.add_slug'))
                        ->danger()
                        ->send();

                    return;
                }

                /** @var Component $livewire */
                $livewire = $action->getLivewire();
                $livewire->js('window.open('.json_encode($url).', "_blank")');
            })
            ->visible(fn (?Page $record): bool => $record instanceof Page
                && app(PageService::class)->hasPreviewableSlug($record));
    }
}
