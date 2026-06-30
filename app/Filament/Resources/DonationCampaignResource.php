<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DonationCampaignResource\Pages;
use App\Models\DonationCampaign;
use BackedEnum;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class DonationCampaignResource extends Resource
{
    protected static ?string $model = DonationCampaign::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-heart';

    protected static string|UnitEnum|null $navigationGroup = 'Fundraising';

    protected static ?string $navigationLabel = 'Campaigns';

    protected static ?int $navigationSort = 1;

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
                    RichEditor::make("description.{$locale}")
                        ->label('Description')
                        ->columnSpanFull(),
                    Textarea::make("form_notice.{$locale}")
                        ->label('Form notice (card data)')
                        ->rows(3)
                        ->columnSpanFull(),
                    RichEditor::make("privacy_notice.{$locale}")
                        ->label('Privacy page content')
                        ->columnSpanFull(),
                ]);
        }

        return $schema->schema([
            TextInput::make('slug')
                ->required()
                ->unique(ignoreRecord: true)
                ->alphaDash()
                ->maxLength(255),
            Tabs::make('Translations')->tabs($tabs)->columnSpanFull(),
            TagsInput::make('preset_amounts')
                ->label('Preset amounts (cents)')
                ->placeholder('500, 1000, 2500')
                ->helperText('Values in cents, e.g. 500 = €5.00'),
            Toggle::make('allow_custom_amount')->default(true),
            TextInput::make('min_amount_cents')->numeric()->default(50)->required(),
            TextInput::make('currency')->default('EUR')->maxLength(3)->required(),
            TextInput::make('espocrm_finanziamento_name')
                ->label('EspoCRM Finanziamento name')
                ->helperText('Filled automatically when the campaign is saved. Override only if the CRM name must differ from the campaign title.')
                ->maxLength(255),
            Toggle::make('is_active')->default(true),
            TextInput::make('sort_order')->numeric()->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->formatStateUsing(fn ($state) => is_array($state) ? ($state['it'] ?? array_values($state)[0] ?? '') : $state)
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug'),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('currency'),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable(),
            ])
            ->defaultSort('sort_order')
            ->actions([\Filament\Actions\EditAction::make()])
            ->bulkActions([\Filament\Actions\BulkActionGroup::make([\Filament\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDonationCampaigns::route('/'),
            'create' => Pages\CreateDonationCampaign::route('/create'),
            'edit' => Pages\EditDonationCampaign::route('/{record}/edit'),
        ];
    }
}
