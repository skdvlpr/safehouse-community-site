<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PageResource\Actions\PreviewPageAction;
use App\Filament\Resources\PageResource\Pages;
use App\Filament\Resources\PageResource\Support\PageTemplateFormFields;
use App\Models\Page;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
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

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static string|UnitEnum|null $navigationGroup = 'Content';

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
                ->schema(PageTemplateFormFields::fieldsForLocale($locale, $locale === 'it'));
        }

        $carouselAltFields = [];

        foreach ($locales as $locale) {
            $carouselAltFields[] = TextInput::make("alt.{$locale}")
                ->label('Alt text ('.strtoupper($locale).')')
                ->maxLength(255);
        }

        return $schema
            ->schema([
                TextInput::make('key')
                    ->label('Stable key')
                    ->helperText('Used for direct navigation links (about, services, contact). Pages without these keys appear automatically under “Altre Pagine” in the site menu when published.')
                    ->maxLength(64)
                    ->alphaDash()
                    ->unique(ignoreRecord: true),

                Select::make('template')
                    ->label('Page template')
                    ->options(collect(config('page_templates', []))->mapWithKeys(
                        fn (array $template, string $key): array => [$key => $template['label'] ?? $key]
                    )->all())
                    ->default('default')
                    ->required()
                    ->live()
                    ->helperText(fn (?string $state): string => static::templateHelperText($state)),

                Toggle::make('is_published')
                    ->label('Published')
                    ->default(true),

                Section::make('Hero carousel')
                    ->description('Optional photo gallery at the top of any page template (WordPress-style featured gallery). Shown only when at least one image is uploaded.')
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
                                    ->disk((string) config('page_carousel.disk', 'public'))
                                    ->directory((string) config('page_carousel.directory', 'page-carousels'))
                                    ->required()
                                    ->maxSize(5120)
                                    ->columnSpanFull(),

                                ...$carouselAltFields,
                            ])
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull()
                    ->collapsed(),

                PageTemplateFormFields::serviceCardsSection(),

                Tabs::make('Translations')
                    ->tabs($tabs)
                    ->columnSpanFull()
                    ->persistTabInQueryString(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')
                    ->label('Key')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('template')
                    ->label('Template')
                    ->badge(),

                Tables\Columns\IconColumn::make('is_published')
                    ->label('Published')
                    ->boolean(),

                Tables\Columns\TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(function ($state) {
                        if (is_array($state)) {
                            return $state[app()->getLocale()] ?? $state['it'] ?? array_values($state)[0] ?? '';
                        }

                        return $state;
                    }),

                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->formatStateUsing(function ($state) {
                        if (is_array($state)) {
                            return $state['it'] ?? array_values($state)[0] ?? '';
                        }

                        return $state;
                    }),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                PreviewPageAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
        ];
    }

    public static function templateHelperText(?string $template): string
    {
        if ($template === null || $template === '') {
            return 'Each template has a distinct public layout — save the page, then use Preview (IT/RU/EN).';
        }

        $description = (string) config("page_templates.{$template}.description", '');
        $layout = match ($template) {
            'about' => 'Layout: 2 columns + values panel + quote.',
            'services' => 'Layout: red banner + numbered cards.',
            'article' => 'Layout: narrow column + drop cap.',
            'landing' => 'Layout: full hero + CTA buttons.',
            'legal' => 'Layout: monospace legal document.',
            'contact' => 'Layout: info + form mockup.',
            'news_index' => 'Layout: intro + news CTA card.',
            default => 'Layout: title + single panel.',
        };

        $exampleKey = config("page_templates.{$template}.example_key");
        $example = is_string($exampleKey) && $exampleKey !== ''
            ? " Live example: page key «{$exampleKey}»."
            : '';

        return trim("{$description} {$layout}{$example}");
    }
}
