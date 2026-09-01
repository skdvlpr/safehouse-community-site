<?php

namespace Tests\Unit;

use App\Filament\Support\CarouselBatchUpload;
use App\Filament\Support\CarouselFormFields;
use Filament\Forms\Components\FileUpload;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CarouselBatchUploadTest extends TestCase
{
    #[DataProvider('mergePathsProvider')]
    public function test_merge_paths_respects_max_slides(array $paths, int $existing, int $expectedCount): void
    {
        $existingSlides = [];

        for ($i = 0; $i < $existing; $i++) {
            $existingSlides['existing-'.$i] = [
                'path' => 'article-carousels/existing.jpg',
                'alt' => ['it' => '', 'ru' => '', 'en' => ''],
            ];
        }

        $merged = CarouselBatchUpload::mergePaths($paths, $existingSlides);

        $this->assertCount($expectedCount, $merged);
    }

    public function test_stored_batch_paths_skips_temporary_livewire_identifiers(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('article-carousels/a.jpg', 'fake');

        $paths = CarouselFormFields::storedBatchPaths([
            'livewire-file:abc123',
            'article-carousels/a.jpg',
            '',
            null,
        ]);

        $this->assertSame(['article-carousels/a.jpg'], $paths);
    }

    public function test_merge_batch_into_carousel_adds_stored_paths(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('article-carousels/a.jpg', 'fake');
        Storage::disk('public')->put('article-carousels/b.jpg', 'fake');

        $result = CarouselFormFields::mergeBatchIntoCarousel(
            ['article-carousels/a.jpg', 'article-carousels/b.jpg'],
            [],
        );

        $this->assertSame(2, $result['added']);
        $this->assertCount(2, $result['slides']);
        $paths = array_map(
            fn (array $slide): ?string => CarouselBatchUpload::slidePath($slide),
            array_values($result['slides']),
        );
        $this->assertSame(['article-carousels/a.jpg', 'article-carousels/b.jpg'], $paths);
    }

    public function test_merge_batch_into_carousel_skips_temporary_paths(): void
    {
        Storage::fake('public');

        $result = CarouselFormFields::mergeBatchIntoCarousel(
            ['livewire-file:temp-123'],
            [],
        );

        $this->assertSame(0, $result['added']);
        $this->assertSame([], $result['slides']);
    }

    public function test_normalize_slides_for_form_converts_string_paths_to_filament_shape(): void
    {
        $normalized = CarouselFormFields::normalizeSlidesForForm([
            'slide-1' => [
                'path' => 'article-carousels/a.jpg',
                'alt' => ['it' => 'A', 'ru' => '', 'en' => ''],
            ],
        ]);

        $this->assertCount(1, $normalized);
        $slide = $normalized['slide-1'];
        $this->assertIsArray($slide['path']);
        $this->assertSame('article-carousels/a.jpg', CarouselFormFields::extractStoredPath($slide['path']));
    }

    public function test_merge_batch_into_carousel_uses_filament_file_upload_state(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('article-carousels/a.jpg', 'fake');

        $result = CarouselFormFields::mergeBatchIntoCarousel(['article-carousels/a.jpg'], []);

        $slide = array_values($result['slides'])[0];
        $this->assertIsArray($slide['path']);
        $this->assertSame('article-carousels/a.jpg', CarouselFormFields::extractStoredPath($slide['path']));
    }

    public function test_clear_processed_batch_uploads_keeps_only_temporary_files(): void
    {
        $temp = \Mockery::mock(TemporaryUploadedFile::class);

        $component = \Mockery::mock(FileUpload::class);
        $component->shouldReceive('getRawState')->once()->andReturn([
            'done-key' => 'article-carousels/a.jpg',
            'pending-key' => $temp,
        ]);
        $component->shouldReceive('rawState')->once()->with(['pending-key' => $temp])->andReturnSelf();

        CarouselFormFields::clearProcessedBatchUploads($component);
    }

    /**
     * @return array<string, array{0: list<string>, 1: int, 2: int}>
     */
    public static function mergePathsProvider(): array
    {
        return [
            'adds three new slides' => [
                ['article-carousels/a.jpg', 'article-carousels/b.jpg', 'article-carousels/c.jpg'],
                0,
                3,
            ],
            'respects max slides' => [
                array_map(fn (int $i): string => "article-carousels/{$i}.jpg", range(1, 5)),
                10,
                12,
            ],
            'skips empty paths' => [
                ['', 'article-carousels/a.jpg'],
                0,
                1,
            ],
        ];
    }
}
