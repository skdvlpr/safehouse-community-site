<?php

namespace App\Filament\Resources\PageResource\Support;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
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
            ->label(__('cms.fields.title'))
            ->required($isPrimaryLocale)
            ->maxLength(255);

        $fields[] = RichEditor::make("body.{$locale}")
            ->label(fn (Get $get): string => static::bodyLabel($get('template')))
            ->helperText(fn (Get $get): ?string => static::bodyHelper($get('template')))
            ->required(fn (Get $get): bool => $isPrimaryLocale && $get('template') !== 'home')
            ->visible(fn (Get $get): bool => $get('template') !== 'home')
            ->columnSpanFull();

        $fields[] = TextInput::make("meta.section_label.{$locale}")
            ->label(__('cms.fields.section_label'))
            ->helperText(__('cms.helpers.section_label'))
            ->maxLength(80)
            ->columnSpanFull();

        $fields[] = TextInput::make("meta.eyebrow.{$locale}")
            ->label(__('cms.fields.hero_eyebrow'))
            ->helperText(__('cms.helpers.hero_eyebrow'))
            ->maxLength(255)
            ->visible(fn (Get $get): bool => in_array($get('template'), ['home', 'landing'], true))
            ->columnSpanFull();

        $fields[] = TextInput::make("meta.tagline.{$locale}")
            ->label(__('cms.fields.hero_tagline'))
            ->helperText(__('cms.helpers.hero_tagline'))
            ->maxLength(255)
            ->visible(fn (Get $get): bool => $get('template') === 'about')
            ->columnSpanFull();

        $fields[] = RichEditor::make("meta.values.{$locale}")
            ->label(__('cms.fields.our_values'))
            ->helperText(__('cms.helpers.our_values'))
            ->visible(fn (Get $get): bool => $get('template') === 'about')
            ->columnSpanFull();

        $fields[] = RichEditor::make("meta.closing.{$locale}")
            ->label(__('cms.fields.closing_statement'))
            ->helperText(__('cms.helpers.closing_statement'))
            ->visible(fn (Get $get): bool => $get('template') === 'about')
            ->columnSpanFull();

        $fields[] = TextInput::make("meta.cta_donate.{$locale}")
            ->label(__('cms.fields.donate_button'))
            ->maxLength(120)
            ->visible(fn (Get $get): bool => $get('template') === 'home')
            ->columnSpanFull();

        $fields[] = TextInput::make("meta.cta_volunteer.{$locale}")
            ->label(__('cms.fields.volunteer_button'))
            ->maxLength(120)
            ->visible(fn (Get $get): bool => $get('template') === 'home')
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
                ->label(__('cms.fields.title_locale', ['locale' => $label]))
                ->maxLength(255);

            $cardFields[] = RichEditor::make("body.{$locale}")
                ->label(__('cms.fields.body_locale', ['locale' => $label]));

            $cardFields[] = TextInput::make("stats.{$locale}")
                ->label(__('cms.fields.stats_locale', ['locale' => $label]))
                ->helperText(__('cms.helpers.stats_line'))
                ->maxLength(255);
        }

        return Section::make(__('cms.sections.service_cards'))
            ->description(__('cms.helpers.service_cards'))
            ->visible(fn (Get $get): bool => $get('template') === 'services')
            ->schema([
                Repeater::make('meta.services')
                    ->label(__('cms.fields.cards'))
                    ->reorderable()
                    ->collapsible()
                    ->itemLabel(function (array $state): string {
                        foreach (config('locales.available', ['it', 'ru', 'en']) as $locale) {
                            $title = $state['title'][$locale] ?? null;

                            if (is_string($title) && $title !== '') {
                                return $title;
                            }
                        }

                        return __('cms.items.new_service_card');
                    })
                    ->schema($cardFields)
                    ->columnSpanFull(),
            ])
            ->columnSpanFull();
    }

    private static function bodyLabel(?string $template): string
    {
        $key = match ($template) {
            'about' => 'about',
            'services' => 'services',
            default => 'default',
        };

        return __('cms.body_labels.'.$key);
    }

    private static function bodyHelper(?string $template): ?string
    {
        return match ($template) {
            'about' => __('cms.helpers.about_body'),
            'services' => __('cms.helpers.services_body'),
            default => null,
        };
    }
}
