<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PageResource\Actions\PreviewPageAction;
use App\Filament\Resources\PageResource\Pages;
use App\Filament\Resources\PageResource\Support\PageTemplateFormFields;
use App\Filament\Support\CmsLocaleTabs;
use App\Models\Page;
use App\Models\User;
use App\Services\SiteAppearanceSettings;
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
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('cms.nav.groups.content');
    }

    public static function getNavigationLabel(): string
    {
        return __('cms.nav.pages');
    }

    public static function getModelLabel(): string
    {
        return __('cms.models.page');
    }

    public static function getPluralModelLabel(): string
    {
        return __('cms.models.pages');
    }

    public static function form(Schema $schema): Schema
    {
        $locales = config('locales.available', ['it', 'ru', 'en']);

        $tabs = [];

        foreach ($locales as $locale) {
            $tabs[] = Tab::make(CmsLocaleTabs::label($locale))
                ->schema(PageTemplateFormFields::fieldsForLocale($locale, $locale === 'it'));
        }

        $carouselAltFields = [];

        foreach ($locales as $locale) {
            $carouselAltFields[] = TextInput::make("alt.{$locale}")
                ->label(__('cms.fields.alt_text', ['locale' => strtoupper($locale)]))
                ->maxLength(255);
        }

        return $schema
            ->schema([
                TextInput::make('key')
                    ->label(__('cms.fields.key'))
                    ->helperText(__('cms.helpers.stable_key'))
                    ->maxLength(64)
                    ->alphaDash()
                    ->unique(ignoreRecord: true),

                Select::make('template')
                    ->label(__('cms.fields.template'))
                    ->options(collect(config('page_templates', []))->mapWithKeys(
                        fn (array $template, string $key): array => [$key => __("cms.templates.{$key}.label")]
                    )->all())
                    ->default('default')
                    ->required()
                    ->live()
                    ->helperText(fn (?string $state): string => static::templateHelperText($state)),

                Toggle::make('is_published')
                    ->label(__('cms.fields.published'))
                    ->default(true),

                Section::make(__('cms.sections.hero_carousel'))
                    ->description(__('cms.helpers.hero_carousel'))
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

                Section::make(__('cms.sections.page_background'))
                    ->description(__('cms.helpers.page_background'))
                    ->schema([
                        Toggle::make('meta.background.enabled')
                            ->label(__('cms.fields.custom_background'))
                            ->helperText(__('cms.helpers.custom_background'))
                            ->live()
                            ->default(false),

                        Select::make('meta.background.path')
                            ->label(__('cms.fields.background_from_library'))
                            ->helperText(__('cms.helpers.background_from_library'))
                            ->options(fn (): array => app(SiteAppearanceSettings::class)->libraryOptions())
                            ->searchable()
                            ->nullable()
                            ->visible(fn (Get $get): bool => (bool) $get('meta.background.enabled')),

                        FileUpload::make('meta.background.upload')
                            ->label(__('cms.fields.background_upload'))
                            ->helperText(__('cms.helpers.background_upload'))
                            ->acceptedFileTypes(app(SiteAppearanceSettings::class)->acceptedMimeTypes())
                            ->imagePreviewHeight('150')
                            ->panelAspectRatio('16:9')
                            ->panelLayout('integrated')
                            ->disk((string) config('site_appearance.disk', 'public'))
                            ->directory((string) config('site_appearance.directory', 'site-appearance'))
                            ->visibility('public')
                            ->nullable()
                            ->maxSize((int) config('site_appearance.max_size_kb', 8192))
                            ->visible(fn (Get $get): bool => (bool) $get('meta.background.enabled'))
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull()
                    ->collapsed(),

                PageTemplateFormFields::serviceCardsSection(),

                Tabs::make(__('cms.sections.translations'))
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
                    ->label(__('cms.fields.key'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('template')
                    ->label(__('cms.fields.template'))
                    ->badge(),

                Tables\Columns\IconColumn::make('is_published')
                    ->label(__('cms.fields.published'))
                    ->boolean(),

                Tables\Columns\TextColumn::make('title')
                    ->label(__('cms.fields.title'))
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(function ($state) {
                        if (is_array($state)) {
                            return $state[app()->getLocale()] ?? $state['it'] ?? array_values($state)[0] ?? '';
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

                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('cms.fields.updated'))
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
            return __('cms.templates.fallback');
        }

        $description = __('cms.templates.'.$template.'.description');
        $layout = __('cms.templates.'.$template.'.layout');

        $exampleKey = config("page_templates.{$template}.example_key");
        $example = is_string($exampleKey) && $exampleKey !== ''
            ? __('cms.templates.example', ['key' => $exampleKey])
            : '';

        return trim("{$description} {$layout}{$example}");
    }

    public static function canViewAny(): bool
    {
        $user = auth()->user();

        return $user instanceof User && $user->isAdminLike();
    }
}
