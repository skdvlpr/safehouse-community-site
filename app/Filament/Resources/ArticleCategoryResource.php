<?php

namespace App\Filament\Resources;

use App\Enums\ArticleSection;
use App\Filament\Resources\ArticleCategoryResource\Pages;
use App\Filament\Support\CmsLocaleTabs;
use App\Models\ArticleCategory;
use App\Models\User;
use BackedEnum;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class ArticleCategoryResource extends Resource
{
    protected static ?string $model = ArticleCategory::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-tag';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('cms.nav.groups.content');
    }

    public static function getNavigationLabel(): string
    {
        return __('cms.nav.news_categories');
    }

    public static function getModelLabel(): string
    {
        return __('cms.models.article_category');
    }

    public static function getPluralModelLabel(): string
    {
        return __('cms.models.article_categories');
    }

    public static function form(Schema $schema): Schema
    {
        $locales = config('locales.available', ['it', 'ru', 'en']);

        $tabs = [];

        foreach ($locales as $locale) {
            $tabs[] = Tab::make(CmsLocaleTabs::label($locale))
                ->schema([
                    TextInput::make("name.{$locale}")
                        ->label(__('cms.fields.name'))
                        ->required($locale === 'it')
                        ->maxLength(255),

                    TextInput::make("slug.{$locale}")
                        ->label(__('cms.fields.slug'))
                        ->required($locale === 'it')
                        ->maxLength(255)
                        ->alphaDash(),

                    Textarea::make("description.{$locale}")
                        ->label(__('cms.fields.description'))
                        ->rows(3)
                        ->columnSpanFull(),
                ]);
        }

        return $schema->schema([
            Tabs::make(__('cms.sections.translations'))->tabs($tabs)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('cms.fields.name'))
                    ->searchable()
                    ->formatStateUsing(function ($state) {
                        if (is_array($state)) {
                            return $state['it'] ?? array_values($state)[0] ?? '';
                        }

                        return $state;
                    }),

                Tables\Columns\TextColumn::make('slug')
                    ->label(__('cms.fields.slug'))
                    ->formatStateUsing(function ($state) {
                        if (is_array($state)) {
                            return $state['it'] ?? array_values($state)[0] ?? '';
                        }

                        return $state;
                    }),

                Tables\Columns\TextColumn::make('articles_count')
                    ->counts('articles')
                    ->label(__('cms.models.articles')),
            ])
            ->actions([
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
            'index' => Pages\ListArticleCategories::route('/'),
            'create' => Pages\CreateArticleCategory::route('/create'),
            'edit' => Pages\EditArticleCategory::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('section', ArticleSection::News);
    }

    public static function canViewAny(): bool
    {
        $user = auth()->user();

        return $user instanceof User && $user->isAdminLike();
    }

    public static function canCreate(): bool
    {
        return static::canViewAny();
    }

    public static function canEdit(Model $record): bool
    {
        return static::canViewAny();
    }

    public static function canDelete(Model $record): bool
    {
        return static::canViewAny();
    }
}
