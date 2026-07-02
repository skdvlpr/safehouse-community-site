<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ArticleResource\Actions\PreviewArticleAction;
use App\Filament\Resources\ArticleResource\Pages;
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

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?string $navigationLabel = 'News';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
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
                    TextInput::make("title.{$locale}")
                        ->label('Title')
                        ->required($locale === 'it')
                        ->maxLength(255),

                    TextInput::make("slug.{$locale}")
                        ->label('Slug')
                        ->required($locale === 'it')
                        ->maxLength(255)
                        ->alphaDash(),

                    Textarea::make("excerpt.{$locale}")
                        ->label('Excerpt')
                        ->rows(3)
                        ->columnSpanFull(),

                    RichEditor::make("body.{$locale}")
                        ->label('Body')
                        ->required($locale === 'it')
                        ->columnSpanFull(),
                ]);
        }

        $carouselAltFields = [];

        foreach ($locales as $locale) {
            $carouselAltFields[] = TextInput::make("alt.{$locale}")
                ->label('Alt text ('.strtoupper($locale).')')
                ->maxLength(255);
        }

        return $schema->schema([
            Select::make('article_category_id')
                ->label('Category')
                ->relationship('category', 'name')
                ->getOptionLabelFromRecordUsing(
                    fn (ArticleCategory $record): string => (string) ($record->getTranslation('name', 'it') ?: $record->getTranslation('name', 'en') ?: '—')
                )
                ->searchable()
                ->preload()
                ->nullable(),

            Toggle::make('is_published')
                ->label('Published')
                ->default(false)
                ->live(),

            DateTimePicker::make('published_at')
                ->label('Published at')
                ->seconds(false)
                ->nullable()
                ->helperText('Required for the article to appear on /notizie.'),

            Section::make('Photo carousel')
                ->description('Optional gallery shown on the article page and in the news feed.')
                ->schema([
                    Repeater::make('meta.carousel')
                        ->label('Slides')
                        ->maxItems((int) config('page_carousel.max_slides', 12))
                        ->reorderable()
                        ->collapsible()
                        ->itemLabel(fn (array $state): string => is_string($state['path'] ?? null) && $state['path'] !== ''
                            ? basename($state['path'])
                            : 'New slide')
                        ->schema([
                            FileUpload::make('path')
                                ->label('Image')
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

            Tabs::make('Translations')
                ->tabs($tabs)
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(function ($state) {
                        if (is_array($state)) {
                            return $state['it'] ?? array_values($state)[0] ?? '';
                        }

                        return $state;
                    }),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->formatStateUsing(function ($state, Article $record) {
                        $category = $record->category;

                        if ($category === null) {
                            return '—';
                        }

                        return $category->getTranslation('name', 'it') ?? '—';
                    }),

                Tables\Columns\IconColumn::make('is_published')
                    ->label('Published')
                    ->boolean(),

                Tables\Columns\TextColumn::make('published_at')
                    ->label('Published at')
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
