<?php

namespace App\Filament\Resources;

use App\Enums\ArticleSection;
use App\Filament\Resources\Concerns\ConfiguresArticleResourceForm;
use App\Filament\Resources\EditorialArticleResource\Actions\PreviewEditorialArticleAction;
use App\Filament\Resources\EditorialArticleResource\Pages;
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

class EditorialArticleResource extends Resource
{
    use ConfiguresArticleResourceForm;

    protected static ?string $model = Article::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?int $navigationSort = 4;

    protected static ?string $slug = 'editorial-articles';

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('cms.nav.groups.content');
    }

    public static function getNavigationLabel(): string
    {
        return __('cms.nav.editorial');
    }

    public static function getModelLabel(): string
    {
        return __('cms.models.editorial_article');
    }

    public static function getPluralModelLabel(): string
    {
        return __('cms.models.editorial_articles');
    }

    public static function form(Schema $schema): Schema
    {
        return self::articleFormSchema($schema, ArticleSection::Editorial);
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

                Tables\Columns\TextColumn::make('author.name')
                    ->label(__('cms.fields.author'))
                    ->visible(fn (): bool => auth()->user()?->isAdminLike() ?? false),

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
                PreviewEditorialArticleAction::make(),
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
        $query = parent::getEloquentQuery()->where('section', ArticleSection::Editorial);

        $user = auth()->user();

        if ($user instanceof User && $user->isJournalist()) {
            $query->where('author_id', $user->id);
        }

        return $query;
    }

    public static function canViewAny(): bool
    {
        $user = auth()->user();

        return $user instanceof User && ($user->isAdminLike() || $user->isJournalist());
    }

    public static function canCreate(): bool
    {
        return static::canViewAny();
    }

    public static function canEdit(Model $record): bool
    {
        $user = auth()->user();

        return $user instanceof User && $user->can('update', $record);
    }

    public static function canDelete(Model $record): bool
    {
        $user = auth()->user();

        return $user instanceof User && $user->can('delete', $record);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEditorialArticles::route('/'),
            'create' => Pages\CreateEditorialArticle::route('/create'),
            'edit' => Pages\EditEditorialArticle::route('/{record}/edit'),
        ];
    }
}
