<?php

namespace App\Filament\Resources\EditorialArticleCategoryResource\Pages;

use App\Filament\Resources\EditorialArticleCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEditorialArticleCategories extends ListRecords
{
    protected static string $resource = EditorialArticleCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
