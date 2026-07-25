<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DonationCampaignResource\Pages;
use App\Filament\Support\CmsLocaleTabs;
use App\Models\DonationCampaign;
use App\Models\User;
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

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('cms.nav.groups.fundraising');
    }

    public static function getNavigationLabel(): string
    {
        return __('cms.nav.campaigns');
    }

    public static function getModelLabel(): string
    {
        return __('cms.models.donation_campaign');
    }

    public static function getPluralModelLabel(): string
    {
        return __('cms.models.donation_campaigns');
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
                    RichEditor::make("description.{$locale}")
                        ->label(__('cms.fields.description'))
                        ->columnSpanFull(),
                    Textarea::make("form_notice.{$locale}")
                        ->label(__('cms.fields.form_notice'))
                        ->rows(3)
                        ->columnSpanFull(),
                    RichEditor::make("privacy_notice.{$locale}")
                        ->label(__('cms.fields.privacy_notice'))
                        ->columnSpanFull(),
                    Textarea::make("thank_you_message.{$locale}")
                        ->label(__('cms.fields.thank_you_message'))
                        ->helperText(__('cms.helpers.thank_you_message'))
                        ->rows(4)
                        ->columnSpanFull(),
                ]);
        }

        return $schema->schema([
            TextInput::make('slug')
                ->label(__('cms.fields.slug'))
                ->required()
                ->unique(ignoreRecord: true)
                ->alphaDash()
                ->maxLength(255),
            Tabs::make(__('cms.sections.translations'))->tabs($tabs)->columnSpanFull(),
            TagsInput::make('preset_amounts')
                ->label(__('cms.fields.preset_amounts'))
                ->placeholder('5, 10, 25, 50')
                ->helperText(__('cms.helpers.preset_amounts'))
                ->formatStateUsing(function (?array $state): array {
                    if ($state === null || $state === []) {
                        return [];
                    }

                    return array_values(array_map(
                        fn ($cents) => DonationCampaign::formatEuroTag((int) $cents),
                        $state,
                    ));
                })
                ->dehydrateStateUsing(function (?array $state): array {
                    if ($state === null || $state === []) {
                        return [];
                    }

                    $cents = array_values(array_unique(array_filter(array_map(
                        fn ($value) => DonationCampaign::parseEuroTagToCents((string) $value),
                        $state,
                    ), static fn (int $value): bool => $value > 0)));

                    sort($cents);

                    return $cents;
                }),
            Toggle::make('allow_custom_amount')
                ->label(__('cms.fields.allow_custom_amount'))
                ->default(true),
            Toggle::make('allows_recurring')
                ->label(__('cms.fields.allows_recurring'))
                ->helperText(__('cms.helpers.allows_recurring'))
                ->default(false)
                ->live(),
            TextInput::make('min_amount_cents')
                ->label(__('cms.fields.min_amount_cents'))
                ->numeric()
                ->default(50)
                ->required(),
            TextInput::make('currency')
                ->label(__('cms.fields.currency'))
                ->default('EUR')
                ->maxLength(3)
                ->required(),
            TextInput::make('fundraising_goal_eur')
                ->label(__('cms.fields.fundraising_goal'))
                ->helperText(__('cms.helpers.fundraising_goal'))
                ->placeholder('700')
                ->visible(fn ($get): bool => ! (bool) $get('allows_recurring'))
                ->formatStateUsing(function ($state, ?DonationCampaign $record): ?string {
                    $cents = $record?->fundraising_goal_cents;

                    if ($cents === null || (int) $cents <= 0) {
                        return null;
                    }

                    return DonationCampaign::formatEuroTag((int) $cents);
                })
                ->dehydrateStateUsing(function (?string $state): ?int {
                    if ($state === null || trim($state) === '') {
                        return null;
                    }

                    $cents = DonationCampaign::parseEuroTagToCents($state);

                    return $cents > 0 ? $cents : null;
                }),
            TextInput::make('espocrm_finanziamento_name')
                ->label(__('cms.fields.espocrm_finanziamento_name'))
                ->helperText(__('cms.helpers.espocrm_finanziamento'))
                ->maxLength(255),
            Toggle::make('is_active')
                ->label(__('cms.fields.is_active'))
                ->default(true),
            TextInput::make('sort_order')
                ->label(__('cms.fields.sort_order'))
                ->numeric()
                ->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label(__('cms.fields.title'))
                    ->formatStateUsing(fn ($state) => is_array($state) ? ($state['it'] ?? array_values($state)[0] ?? '') : $state)
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')
                    ->label(__('cms.fields.slug')),
                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('cms.fields.is_active'))
                    ->boolean(),
                Tables\Columns\IconColumn::make('allows_recurring')
                    ->label(__('cms.fields.allows_recurring'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('currency')
                    ->label(__('cms.fields.currency')),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('cms.fields.updated'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
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

    public static function canViewAny(): bool
    {
        $user = auth()->user();

        return $user instanceof User && $user->isAdminLike();
    }
}
