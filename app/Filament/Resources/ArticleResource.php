<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ArticleResource\Actions\PreviewArticleAction;
use App\Filament\Resources\ArticleResource\Pages;
use App\Filament\Support\CmsLocaleTabs;
use App\Models\Article;
use App\Models\ArticleCategory;
use BackedEnum;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class ArticleResource extends Resource
{
    protected static ?string $model = Article::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-newspaper';

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('cms.nav.groups.content');
    }

    public static function getNavigationLabel(): string
    {
        return __('cms.nav.news');
    }

    public static function getModelLabel(): string
    {
        return __('cms.models.article');
    }

    public static function getPluralModelLabel(): string
    {
        return __('cms.models.articles');
    }

    public static function form(Schema $schema): Schema
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

                    TextInput::make("slug.{$locale}")
                        ->label(__('cms.fields.slug'))
                        ->required($locale === 'it')
                        ->maxLength(255)
                        ->alphaDash(),

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
                ->relationship('category', 'name')
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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label(__('cms.fields.title'))
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(function ($state) {
                        if (is_array($state)) {
                            return $state['it'] ?? array_values($state)[0] ?? '';
                        }

                        return $state;
                    }),

                Tables\Columns\TextColumn::make('category.name')
                    ->label(__('cms.fields.category'))
                    ->formatStateUsing(function ($state, Article $record) {
                        $category = $record->category;

                        if ($category === null) {
                            return '—';
                        }

                        return $category->getTranslation('name', 'it') ?? '—';
                    }),

                Tables\Columns\IconColumn::make('is_published')
                    ->label(__('cms.fields.published'))
                    ->boolean(),

                Tables\Columns\TextColumn::make('published_at')
                    ->label(__('cms.fields.published_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('published_at', 'desc')
            ->actions([
                PreviewArticleAction::make(),
                \Filament\Actions\EditAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListArticles::route('/'),
            'create' => Pages\CreateArticle::route('/create'),
            'edit' => Pages\EditArticle::route('/{record}/edit'),
        ];
    }
}
