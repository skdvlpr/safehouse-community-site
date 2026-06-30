<?php

namespace App\Filament\Resources\PageResource\Support;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;

class PageTemplateFormFields
{
    /**
     * @return list<Component>
     */
    public static function fieldsForLocale(string $locale, bool $isPrimaryLocale): array
    {
        $fields = [];

        $fields[] = TextInput::make("title.{$locale}")
            ->label('Title')
            ->required($isPrimaryLocale)
            ->maxLength(255);

        $fields[] = TextInput::make("slug.{$locale}")
            ->label('Slug')
            ->required($isPrimaryLocale)
            ->maxLength(255)
            ->alphaDash();

        $fields[] = RichEditor::make("body.{$locale}")
            ->label(fn (Get $get): string => static::bodyLabel($get('template')))
            ->helperText(fn (Get $get): ?string => static::bodyHelper($get('template')))
            ->required($isPrimaryLocale)
            ->columnSpanFull();

        $fields[] = TextInput::make("meta.tagline.{$locale}")
            ->label('Hero tagline')
            ->helperText('Short line displayed beside the large title.')
            ->maxLength(255)
            ->visible(fn (Get $get): bool => $get('template') === 'about')
            ->columnSpanFull();

        $fields[] = Textarea::make("meta.values.{$locale}")
            ->label('Our values')
            ->helperText('Right column — highlighted values panel.')
            ->rows(8)
            ->visible(fn (Get $get): bool => $get('template') === 'about')
            ->columnSpanFull();

        $fields[] = Textarea::make("meta.closing.{$locale}")
            ->label('Closing statement')
            ->helperText('Full-width quote at the bottom of the page.')
            ->rows(4)
            ->visible(fn (Get $get): bool => $get('template') === 'about')
            ->columnSpanFull();

        return $fields;
    }

    public static function serviceCardsSection(): Section
    {
        $locales = config('locales.available', ['it', 'ru', 'en']);
        $cardFields = [];

        foreach ($locales as $locale) {
            $label = strtoupper($locale);

            $cardFields[] = TextInput::make("title.{$locale}")
                ->label("Title ({$label})")
                ->maxLength(255);

            $cardFields[] = Textarea::make("body.{$locale}")
                ->label("Body ({$label})")
                ->rows(4);

            $cardFields[] = TextInput::make("stats.{$locale}")
                ->label("Stats line ({$label})")
                ->helperText('Optional footer line, e.g. "1,000+ hot meals · volunteers".')
                ->maxLength(255);
        }

        return Section::make('Service cards')
            ->description('Numbered cards in the grid below the red intro banner.')
            ->visible(fn (Get $get): bool => $get('template') === 'services')
            ->schema([
                Repeater::make('meta.services')
                    ->label('Cards')
                    ->reorderable()
                    ->collapsible()
                    ->itemLabel(function (array $state): string {
                        foreach (config('locales.available', ['it', 'ru', 'en']) as $locale) {
                            $title = $state['title'][$locale] ?? null;

                            if (is_string($title) && $title !== '') {
                                return $title;
                            }
                        }

                        return 'New service card';
                    })
                    ->schema($cardFields)
                    ->columnSpanFull(),
            ])
            ->columnSpanFull();
    }

    private static function bodyLabel(?string $template): string
    {
        return match ($template) {
            'about' => 'Mission intro',
            'services' => 'Services intro',
            default => 'Body',
        };
    }

    private static function bodyHelper(?string $template): ?string
    {
        return match ($template) {
            'about' => 'Left column — main mission text.',
            'services' => 'Text in the red banner above the cards.',
            default => null,
        };
    }
}
