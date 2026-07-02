<?php

namespace App\Filament\Support;

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
        $localeLabels = [
            'it' => '🇮🇹 Italiano',
            'ru' => '🇷🇺 Русский',
            'en' => '🇬🇧 English',
        ];

        $tabs = [];

        foreach ($locales as $locale) {
            $tabs[] = Tab::make($localeLabels[$locale] ?? strtoupper($locale))
                ->schema([
                    Textarea::make("content.primary_tagline.{$locale}")
                        ->label('Slogan principale')
                        ->helperText('Slogan nel footer e sottotitolo nella home page.')
                        ->rows(2)
                        ->maxLength(500),
                ]);
        }

        return [
            Section::make('Slogan del sito')
                ->description('Uno slogan condiviso per footer e hero della home page.')
                ->schema([
                    Tabs::make('TaglineLocales')->tabs($tabs),
                ]),
        ];
    }
}
