<?php

namespace App\Filament\Support;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
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
        $locales = config('locales.available', ['it', 'en']);

        $taglineTabs = [];
        $independenceTabs = [];

        foreach ($locales as $locale) {
            $taglineTabs[] = Tab::make(CmsLocaleTabs::label($locale))
                ->schema([
                    Textarea::make("content.primary_tagline.{$locale}")
                        ->label(__('cms.fields.primary_tagline'))
                        ->helperText(__('cms.helpers.primary_tagline'))
                        ->rows(2)
                        ->maxLength(500),
                ]);

            $independenceTabs[] = Tab::make(CmsLocaleTabs::label($locale))
                ->schema([
                    TextInput::make("content.home_independence_title.{$locale}")
                        ->label(__('cms.fields.home_independence_title'))
                        ->helperText(__('cms.helpers.home_independence_title'))
                        ->maxLength(120),
                    Textarea::make("content.home_independence_body.{$locale}")
                        ->label(__('cms.fields.home_independence_body'))
                        ->helperText(__('cms.helpers.home_independence_body'))
                        ->rows(4)
                        ->maxLength(1200),
                ]);
        }

        return [
            Section::make(__('cms.sections.site_tagline'))
                ->description(__('cms.helpers.site_tagline'))
                ->schema([
                    Tabs::make('TaglineLocales')->tabs($taglineTabs),
                ]),
            Section::make(__('cms.sections.home_independence'))
                ->description(__('cms.helpers.home_independence'))
                ->schema([
                    Tabs::make('IndependenceLocales')->tabs($independenceTabs),
                ]),
        ];
    }
}
