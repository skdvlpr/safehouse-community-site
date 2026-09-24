<?php

namespace App\Filament\Support;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

/**
 * Optional search title and description.
 *
 * @see https://filamentphp.com/docs/4.x/forms/text-input
 */
final class SeoFields
{
    public static function section(string $prefix): Section
    {
        $locales = config('locales.available');
        $fields = [];

        foreach ($locales as $locale) {
            $fields[] = TextInput::make("{$prefix}.title.{$locale}")
                ->label(__('cms.fields.seo_title', ['locale' => strtoupper($locale)]))
                ->maxLength(70);

            $fields[] = TextInput::make("{$prefix}.description.{$locale}")
                ->label(__('cms.fields.seo_description', ['locale' => strtoupper($locale)]))
                ->maxLength(160)
                ->columnSpanFull();
        }

        return Section::make(__('cms.sections.search'))
            ->schema($fields)
            ->columnSpanFull()
            ->collapsed();
    }

    public static function pageSection(): Section
    {
        $locales = config('locales.available');
        $fields = [];

        foreach ($locales as $locale) {
            $fields[] = TextInput::make("meta.seo_title.{$locale}")
                ->label(__('cms.fields.seo_title', ['locale' => strtoupper($locale)]))
                ->maxLength(70);

            $fields[] = TextInput::make("meta.seo_description.{$locale}")
                ->label(__('cms.fields.seo_description', ['locale' => strtoupper($locale)]))
                ->maxLength(160)
                ->columnSpanFull();
        }

        return Section::make(__('cms.sections.search'))
            ->schema($fields)
            ->columnSpanFull()
            ->collapsed();
    }
}
