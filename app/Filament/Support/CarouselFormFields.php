<?php

namespace App\Filament\Support;

use Filament\Forms\Components\Component;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component as LivewireComponent;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Throwable;

class CarouselFormFields
{
    private static bool $isMergingBatch = false;

    public static function batchUpload(string $directory): FileUpload
    {
        return static::configureUpload(
            FileUpload::make('_carousel_batch')
                ->label(__('cms.fields.batch_upload'))
                ->helperText(__('cms.helpers.batch_upload'))
                ->multiple()
                ->maxParallelUploads(1)
                ->panelLayout('compact')
                ->dehydrated(false)
                ->deletable(false)
                ->live()
                ->deleteUploadedFileUsing(static function (): void {
                    // Batch paths are copied into the repeater; do not delete stored files when clearing the field.
                })
                ->afterStateUpdated(function (FileUpload $component, Get $get, Set $set, LivewireComponent $livewire) use ($directory): void {
                    static::handleBatchUploadStateChange($component, $directory, $get, $set);
                }),
            $directory,
        );
    }

    public static function handleBatchUploadStateChange(
        FileUpload $component,
        string $directory,
        Get $get,
        Set $set,
    ): void {
        if (static::$isMergingBatch) {
            return;
        }

        static::$isMergingBatch = true;

        try {
            static::persistPendingBatchUploads($component, $directory);

            $batchPaths = array_values(Arr::wrap($component->getState()));

            static::autoMergeBatchUpload($batchPaths, $get, $set, $component);
        } catch (Throwable $exception) {
            Log::error('Carousel batch upload failed', [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            Notification::make()
                ->title(__('cms.notifications.batch_failed'))
                ->body($exception->getMessage())
                ->danger()
                ->send();
        } finally {
            static::$isMergingBatch = false;
        }
    }

    public static function persistPendingBatchUploads(FileUpload $component, string $directory): void
    {
        $rawState = Arr::wrap($component->getRawState());
        $updated = [];
        $changed = false;

        foreach ($rawState as $key => $file) {
            if ($file instanceof TemporaryUploadedFile) {
                $path = app(CarouselImageStorage::class)->store($file, $directory);
                $file->delete();
                $updated[$key] = $path;
                $changed = true;

                continue;
            }

            if (is_string($file) && $file !== '') {
                $updated[$key] = $file;
            }
        }

        if ($changed) {
            $component->rawState($updated);
        }
    }

    /**
     * @param  list<mixed>|null  $batchPaths
     * @return array{slides: array<string|int, array<string, mixed>>, added: int}
     */
    public static function mergeBatchIntoCarousel(?array $batchPaths, array $currentSlides): array
    {
        $storedPaths = static::storedBatchPaths($batchPaths);

        if ($storedPaths === []) {
            return ['slides' => static::normalizeSlidesForForm($currentSlides), 'added' => 0];
        }

        $normalizedCurrent = static::normalizeSlidesForForm($currentSlides);
        $before = count($normalizedCurrent);
        $merged = CarouselBatchUpload::mergePaths($storedPaths, $normalizedCurrent);

        return [
            'slides' => $merged,
            'added' => count($merged) - $before,
        ];
    }

    /**
     * @param  list<mixed>|null  $batchPaths
     */
    public static function autoMergeBatchUpload(
        ?array $batchPaths,
        Get $get,
        Set $set,
        FileUpload $batchComponent,
    ): void {
        if (static::storedBatchPaths($batchPaths) === []) {
            return;
        }

        $current = $get('meta.carousel');
        $currentSlides = is_array($current) ? $current : [];
        $result = static::mergeBatchIntoCarousel($batchPaths, $currentSlides);

        $set('meta.carousel', $result['slides']);

        static::clearProcessedBatchUploads($batchComponent);

        if ($result['added'] > 0) {
            Notification::make()
                ->title(__('cms.notifications.batch_merged', ['count' => $result['added']]))
                ->success()
                ->send();
        }
    }

    /**
     * Drop stored paths from the batch UI; keep only files still uploading as Livewire temps.
     */
    public static function clearProcessedBatchUploads(FileUpload $batchComponent): void
    {
        $pending = [];

        foreach (Arr::wrap($batchComponent->getRawState()) as $key => $file) {
            if ($file instanceof TemporaryUploadedFile) {
                $pending[$key] = $file;
            }
        }

        $batchComponent->rawState($pending);
    }

    /**
     * @param  list<mixed>|null  $batchPaths
     * @return list<string>
     */
    public static function storedBatchPaths(?array $batchPaths): array
    {
        if (! is_array($batchPaths) || $batchPaths === []) {
            return [];
        }

        $disk = (string) config('page_carousel.disk', 'public');

        return array_values(array_filter(
            $batchPaths,
            static fn (mixed $path): bool => is_string($path)
                && $path !== ''
                && ! str_starts_with($path, 'livewire-file:')
                && Storage::disk($disk)->exists($path),
        ));
    }

    public static function slideUpload(string $directory): FileUpload
    {
        return static::configureUpload(
            FileUpload::make('path')
                ->label(__('cms.fields.image'))
                ->helperText(__('cms.helpers.slide_image'))
                ->imagePreviewHeight('150')
                ->panelAspectRatio('16:9')
                ->panelLayout('integrated')
                ->nullable(),
            $directory,
        );
    }

    /**
     * @param  list<Component>  $altFields
     */
    public static function repeater(string $directory, array $altFields): Repeater
    {
        return Repeater::make('meta.carousel')
            ->label(__('cms.fields.slides'))
            ->helperText(__('cms.helpers.carousel_repeater'))
            ->maxItems((int) config('page_carousel.max_slides', 12))
            ->reorderable()
            ->collapsible()
            ->addActionLabel(__('cms.actions.add_slide'))
            ->itemLabel(fn (array $state): string => ($path = CarouselBatchUpload::slidePath($state)) !== null
                ? basename($path)
                : __('cms.items.new_slide'))
            ->schema([
                static::slideUpload($directory),
                ...$altFields,
            ])
            ->columnSpanFull();
    }

    /**
     * @param  list<Component>  $altFields
     * @return list<FileUpload|Repeater>
     */
    public static function carouselSectionSchema(string $directory, array $altFields): array
    {
        return [
            static::batchUpload($directory),
            static::repeater($directory, $altFields),
        ];
    }

    /**
     * Filament single FileUpload raw state: one UUID key => stored path.
     *
     * @return array<string, string>
     */
    public static function fileUploadPathState(string $path): array
    {
        return [(string) Str::uuid() => $path];
    }

    public static function extractStoredPath(mixed $state): ?string
    {
        if (is_string($state) && $state !== '') {
            return $state;
        }

        if (! is_array($state)) {
            return null;
        }

        foreach ($state as $value) {
            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        return null;
    }

    /**
     * @param  array<string|int, array<string, mixed>>  $slides
     * @return array<string, array<string, mixed>>
     */
    public static function normalizeSlidesForForm(array $slides): array
    {
        $locales = config('locales.available', ['it', 'ru', 'en']);
        $normalized = [];

        foreach ($slides as $key => $slide) {
            if (! is_array($slide)) {
                continue;
            }

            $storedPath = static::extractStoredPath($slide['path'] ?? null);

            if ($storedPath === null) {
                continue;
            }

            $slideKey = is_string($key) && $key !== '' && ! is_numeric($key)
                ? $key
                : (string) Str::uuid();

            $alt = is_array($slide['alt'] ?? null) ? $slide['alt'] : [];
            $normalizedAlt = [];

            foreach ($locales as $locale) {
                $value = $alt[$locale] ?? null;
                $normalizedAlt[$locale] = is_string($value) ? $value : '';
            }

            $normalized[$slideKey] = [
                'path' => static::fileUploadPathState($storedPath),
                'alt' => $normalizedAlt,
            ];
        }

        return $normalized;
    }

    /**
     * @param  array<string, mixed>  $meta
     * @return array<string, mixed>
     */
    public static function normalizeCarouselMetaForForm(array $meta): array
    {
        $items = $meta['carousel'] ?? [];

        if (! is_array($items)) {
            $meta['carousel'] = [];

            return $meta;
        }

        $meta['carousel'] = static::normalizeSlidesForForm($items);

        return $meta;
    }

    private static function configureUpload(FileUpload $field, string $directory): FileUpload
    {
        return $field
            ->image()
            ->disk((string) config('page_carousel.disk', 'public'))
            ->directory($directory)
            ->visibility('public')
            ->maxSize((int) config('page_carousel.max_upload_kb', 25600))
            ->validationMessages([
                'max' => __('cms.validation.image_max'),
                'image' => __('cms.validation.image_type'),
            ])
            ->saveUploadedFileUsing(
                fn (TemporaryUploadedFile $file): string => app(CarouselImageStorage::class)->store($file, $directory),
            )
            ->columnSpanFull();
    }
}
