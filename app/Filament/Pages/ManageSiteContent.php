<?php

namespace App\Filament\Pages;

use App\Filament\Resources\PageResource;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * Legacy route kept so cached Filament routes and bookmarks do not 500 after
 * the primary tagline editor moved to Content → Pages.
 */
class ManageSiteContent extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAlt;

    protected static ?string $navigationLabel = 'Site content';

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $title = 'Site content';

    protected static ?string $slug = 'manage-site-content';

    public function mount(): void
    {
        $this->redirect(PageResource::getUrl('index'), navigate: true);
    }
}
