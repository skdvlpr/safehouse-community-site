<?php

namespace App\Filament\Resources;

use App\Enums\ArticleSection;
use App\Filament\Resources\ArticleResource\Actions\PreviewArticleAction;
use App\Filament\Resources\ArticleResource\Pages;
use App\Filament\Resources\Concerns\ConfiguresArticleResourceForm;
use App\Models\Article;
use App\Models\User;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class ArticleResource extends Resource
{
    use ConfiguresArticleResourceForm;

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
        return __('cms.models.news_article');
    }

    public static function getPluralModelLabel(): string
    {
        return __('cms.models.news_articles');
    }

    public static function form(Schema $schema): Schema
    {
        return self::articleFormSchema($schema, ArticleSection::News);
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
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListArticles::route('/'),
            'create' => Pages\CreateArticle::route('/create'),
            'edit' => Pages\EditArticle::route('/{record}/edit'),
        ];
    }
}
