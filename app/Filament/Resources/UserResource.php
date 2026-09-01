<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use UnitEnum;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('cms.nav.groups.settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('cms.nav.users');
    }

    public static function getModelLabel(): string
    {
        return __('cms.models.user');
    }

    public static function getPluralModelLabel(): string
    {
        return __('cms.models.users');
    }

    public static function form(Schema $schema): Schema
    {
        $roleOptions = [
            'admin' => __('cms.roles.admin'),
            'journalist' => __('cms.roles.journalist'),
        ];

        if (auth()->user()?->hasRole('super-admin')) {
            $roleOptions = ['super-admin' => __('cms.roles.super_admin')] + $roleOptions;
        }

        return $schema->schema([
            TextInput::make('first_name')
                ->label(__('cms.fields.first_name'))
                ->required()
                ->maxLength(255),

            TextInput::make('last_name')
                ->label(__('cms.fields.last_name'))
                ->required()
                ->maxLength(255),

            TextInput::make('job_title')
                ->label(__('cms.fields.job_title'))
                ->maxLength(255),

            TextInput::make('email')
                ->label(__('cms.fields.email'))
                ->email()
                ->required()
                ->maxLength(255)
                ->unique(ignoreRecord: true),

            TextInput::make('password')
                ->label(__('cms.fields.password'))
                ->password()
                ->revealable()
                ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? Hash::make($state) : null)
                ->dehydrated(fn (?string $state): bool => filled($state))
                ->required(fn (?Model $record): bool => $record === null)
                ->minLength(12),

            Select::make('role')
                ->label(__('cms.fields.role'))
                ->options($roleOptions)
                ->required()
                ->default(fn (?User $record): ?string => $record?->roles->first()?->name),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('cms.fields.full_name'))
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(['first_name', 'last_name']),

                Tables\Columns\TextColumn::make('email')
                    ->label(__('cms.fields.email'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('roles.name')
                    ->label(__('cms.fields.role'))
                    ->badge(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function canViewAny(): bool
    {
        $user = auth()->user();

        return $user instanceof User && $user->canManageUsers();
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
