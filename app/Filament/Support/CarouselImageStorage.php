<?php

namespace App\Filament\Support;

use App\Support\UploadedImageCompressor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class CarouselImageStorage
{
    public function store(UploadedFile|TemporaryUploadedFile $file, string $directory): string
    {
        $disk = (string) config('page_carousel.disk', 'public');
        $path = $file->getRealPath();

        if (! is_string($path) || $path === '') {
            throw new \RuntimeException('Uploaded carousel image is unreadable.');
        }

        $processed = app(UploadedImageCompressor::class)->process($path);
        $filename = Str::uuid()->toString().'.'.$processed['extension'];
        $storedPath = trim($directory, '/').'/'.$filename;

        Storage::disk($disk)->put($storedPath, $processed['contents'], 'public');

        return $storedPath;
    }
}
