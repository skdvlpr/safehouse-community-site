<?php

namespace App\Filament\Resources\Concerns;

use App\Enums\ArticleSection;
use App\Filament\Support\CmsLocaleTabs;
use App\Models\ArticleCategory;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
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

            DateTimePicker::make('published_at')
                ->label(__('cms.fields.published_at'))
                ->seconds(false)
                ->nullable()
                ->helperText(__('cms.helpers.published_at')),

            Section::make(__('cms.sections.photo_carousel'))
                ->description(__('cms.helpers.article_carousel'))
                ->schema([
                    Repeater::make('meta.carousel')
                        ->label(__('cms.fields.slides'))
                        ->maxItems((int) config('page_carousel.max_slides', 12))
                        ->reorderable()
                        ->collapsible()
                        ->itemLabel(fn (array $state): string => is_string($state['path'] ?? null) && $state['path'] !== ''
                            ? basename($state['path'])
                            : __('cms.items.new_slide'))
                        ->schema([
                            FileUpload::make('path')
                                ->label(__('cms.fields.image'))
                                ->image()
                                ->imagePreviewHeight('150')
                                ->panelAspectRatio('16:9')
                                ->panelLayout('integrated')
                                ->disk((string) config('page_carousel.disk', 'public'))
                                ->directory((string) config('page_carousel.article_directory', 'article-carousels'))
                                ->required()
                                ->maxSize(5120)
                                ->columnSpanFull(),

                            ...$carouselAltFields,
                        ])
                        ->columnSpanFull(),
                ])
                ->columnSpanFull()
                ->collapsed(),

            Tabs::make(__('cms.sections.translations'))
                ->tabs($tabs)
                ->columnSpanFull(),
        ]);
    }
}
