<?php

namespace App\Filament\Resources\EditorialArticleResource\Pages;

use App\Filament\Resources\EditorialArticleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEditorialArticles extends ListRecords
{
    protected static string $resource = EditorialArticleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
