<?php

namespace App\Filament\Resources\ArticleResource\Actions;

use App\Models\Article;
use App\Services\ArticleService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Livewire\Component;

class PreviewArticleAction
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
            ->modalDescription(__('cms.preview.article_description'))
            ->action(function (array $data, Article $record, Action $action): void {
                $url = app(ArticleService::class)->previewUrl($record, $data['locale']);

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
            ->visible(fn (?Article $record): bool => $record instanceof Article
                && app(ArticleService::class)->hasPreviewableSlug($record));
    }
}
