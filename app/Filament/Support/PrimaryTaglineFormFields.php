<?php

namespace App\Filament\Support;

use App\Filament\Support\CmsLocaleTabs;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;

class PrimaryTaglineFormFields
{
    /**
     * @return array<int, Section>
     */
    public static function section(): array
    {
        $locales = config('locales.available', ['it', 'ru', 'en']);

        $tabs = [];

        foreach ($locales as $locale) {
            $tabs[] = Tab::make(CmsLocaleTabs::label($locale))
                ->schema([
                    Textarea::make("content.primary_tagline.{$locale}")
                        ->label(__('cms.fields.primary_tagline'))
                        ->helperText(__('cms.helpers.primary_tagline'))
                        ->rows(2)
                        ->maxLength(500),
                ]);
        }

        return [
            Section::make(__('cms.sections.site_tagline'))
                ->description(__('cms.helpers.site_tagline'))
                ->schema([
                    Tabs::make('TaglineLocales')->tabs($tabs),
                ]),
        ];
    }
}
