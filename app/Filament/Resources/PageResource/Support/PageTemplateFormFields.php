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
            ->required(fn (Get $get): bool => $isPrimaryLocale && $get('template') !== 'home')
            ->maxLength(255)
            ->alphaDash()
            ->visible(fn (Get $get): bool => $get('template') !== 'home');

        $fields[] = RichEditor::make("body.{$locale}")
            ->label(fn (Get $get): string => static::bodyLabel($get('template')))
            ->helperText(fn (Get $get): ?string => static::bodyHelper($get('template')))
            ->required(fn (Get $get): bool => $isPrimaryLocale && $get('template') !== 'home')
            ->visible(fn (Get $get): bool => $get('template') !== 'home')
            ->columnSpanFull();

        $fields[] = TextInput::make("meta.section_label.{$locale}")
            ->label('Section label (red)')
            ->helperText('Small red label or banner text for this page. Leave empty for the default.')
            ->maxLength(80)
            ->columnSpanFull();

        $fields[] = TextInput::make("meta.eyebrow.{$locale}")
            ->label('Hero eyebrow')
            ->helperText('Small red line above the main title.')
            ->maxLength(255)
            ->visible(fn (Get $get): bool => in_array($get('template'), ['home', 'landing'], true))
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

        $fields[] = TextInput::make("meta.stats_heading.{$locale}")
            ->label('Stats section title')
            ->helperText('Meal counters are loaded from EspoCRM. Customize the section title here.')
            ->maxLength(255)
            ->visible(fn (Get $get): bool => $get('template') === 'home')
            ->columnSpanFull();

        $fields[] = TextInput::make("meta.stats_lead.{$locale}")
            ->label('Stats section intro')
            ->helperText('Short line below the stats heading.')
            ->visible(fn (Get $get): bool => $get('template') === 'home')
            ->columnSpanFull();

        $fields[] = TextInput::make("meta.cta_donate.{$locale}")
            ->label('Donate button label')
            ->maxLength(120)
            ->visible(fn (Get $get): bool => $get('template') === 'home')
            ->columnSpanFull();

        $fields[] = TextInput::make("meta.cta_volunteer.{$locale}")
            ->label('Volunteer button label')
            ->maxLength(120)
            ->visible(fn (Get $get): bool => $get('template') === 'home')
            ->columnSpanFull();

        return $fields;
    }

    public static function homeStatsSection(): Section
    {
        $locales = config('locales.available', ['it', 'ru', 'en']);
        $statFields = [];

        foreach ($locales as $locale) {
            $label = strtoupper($locale);

            $statFields[] = TextInput::make("label.{$locale}")
                ->label("Label ({$label})")
                ->maxLength(255);
        }

        $statFields[] = TextInput::make('value')
            ->label('Value')
            ->helperText('Number or placeholder, e.g. 1.000+ or —')
            ->maxLength(32);

        return Section::make('Home stats')
            ->description('Impact cards below the hero.')
            ->visible(fn (Get $get): bool => $get('template') === 'home')
            ->schema([
                Repeater::make('meta.stats')
                    ->label('Stats')
                    ->reorderable()
                    ->collapsible()
                    ->itemLabel(function (array $state): string {
                        foreach (config('locales.available', ['it', 'ru', 'en']) as $locale) {
                            $label = $state['label'][$locale] ?? null;

                            if (is_string($label) && $label !== '') {
                                return $label;
                            }
                        }

                        return (string) ($state['value'] ?? 'New stat');
                    })
                    ->schema($statFields)
                    ->columnSpanFull(),
            ])
            ->columnSpanFull();
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
