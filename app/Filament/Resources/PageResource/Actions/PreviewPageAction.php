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
            ->label('Preview')
            ->icon('heroicon-o-eye')
            ->color('gray')
            ->form([
                Select::make('locale')
                    ->label('Language')
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
            ->modalSubmitActionLabel('Open preview')
            ->modalDescription('Opens the saved page in a new tab. Unsaved edits are not included — save first.')
            ->action(function (array $data, Page $record, Action $action): void {
                $url = app(PageService::class)->previewUrl($record, $data['locale']);

                if ($url === null) {
                    Notification::make()
                        ->title('Preview unavailable')
                        ->body('Add a slug for the selected language first.')
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
