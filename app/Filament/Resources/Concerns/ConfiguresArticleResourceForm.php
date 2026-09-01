<?php

namespace App\Filament\Resources\Concerns;

use App\Enums\ArticleSection;
use App\Filament\Support\CarouselFormFields;
use App\Filament\Support\CmsLocaleTabs;
use App\Models\ArticleCategory;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

trait ConfiguresArticleResourceForm
{
    public static function articleFormSchema(Schema $schema, ArticleSection $section): Schema
    {
        $locales = config('locales.available', ['it', 'ru', 'en']);

        $tabs = [];

        foreach ($locales as $locale) {
            $tabs[] = Tab::make(CmsLocaleTabs::label($locale))
                ->schema([
                    TextInput::make("title.{$locale}")
                        ->label(__('cms.fields.title'))
                        ->required($locale === 'it')
                        ->maxLength(255),

                    Textarea::make("excerpt.{$locale}")
                        ->label(__('cms.fields.excerpt'))
                        ->rows(3)
                        ->columnSpanFull(),

                    RichEditor::make("body.{$locale}")
                        ->label(__('cms.fields.body'))
                        ->required($locale === 'it')
                        ->columnSpanFull(),
                ]);
        }

        $carouselAltFields = [];

        foreach ($locales as $locale) {
            $carouselAltFields[] = TextInput::make("alt.{$locale}")
                ->label(__('cms.fields.alt_text', ['locale' => strtoupper($locale)]))
                ->maxLength(255);
        }

        return $schema->schema([
            Select::make('article_category_id')
                ->label(__('cms.fields.category'))
                ->relationship(
                    'category',
                    'name',
                    fn ($query) => $query->where('section', $section),
                )
                ->getOptionLabelFromRecordUsing(
                    fn (ArticleCategory $record): string => (string) ($record->getTranslation('name', 'it') ?: $record->getTranslation('name', 'en') ?: '—')
                )
                ->searchable()
                ->preload()
                ->nullable(),

            Toggle::make('is_published')
                ->label(__('cms.fields.published'))
                ->default(false)
                ->live(),

            Toggle::make('show_author')
                ->label(__('cms.fields.show_author'))
                ->helperText(__('cms.helpers.show_author'))
                ->default(false)
                ->dehydrated(fn (Get $get): bool => (bool) $get('is_published'))
                ->visible(fn (Get $get): bool => (bool) $get('is_published')),

            DateTimePicker::make('published_at')
                ->label(__('cms.fields.published_at'))
                ->seconds(false)
                ->nullable()
                ->helperText(__('cms.helpers.published_at')),

            Section::make(__('cms.sections.photo_carousel'))
                ->description(__('cms.helpers.article_carousel'))
                ->schema(
                    CarouselFormFields::carouselSectionSchema(
                        (string) config('page_carousel.article_directory', 'article-carousels'),
                        $carouselAltFields,
                    ),
                )
                ->columnSpanFull()
                ->collapsed(),

            Tabs::make(__('cms.sections.translations'))
                ->tabs($tabs)
                ->columnSpanFull(),
        ]);
    }
}
