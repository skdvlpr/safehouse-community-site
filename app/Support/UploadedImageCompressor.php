<?php

namespace App\Support;

use GdImage;
use RuntimeException;

class UploadedImageCompressor
{
    public function __construct(
        private int $maxStoredBytes = 0,
        private int $maxDimension = 0,
        private int $minQuality = 50,
        private int $startQuality = 85,
    ) {
        $this->maxStoredBytes = $this->maxStoredBytes ?: ((int) config('page_carousel.max_stored_kb', 8192) * 1024);
        $this->maxDimension = $this->maxDimension ?: (int) config('page_carousel.max_dimension', 2560);
    }

    /**
     * @return array{contents: string, extension: string}
     */
    public function process(string $sourcePath): array
    {
        if (! is_file($sourcePath)) {
            throw new RuntimeException('Uploaded image file is missing.');
        }

        $originalSize = filesize($sourcePath);

        if ($originalSize === false) {
            throw new RuntimeException('Unable to read uploaded image size.');
        }

        $info = @getimagesize($sourcePath);

        if ($info === false) {
            return $this->passthrough($sourcePath);
        }

        [$width, $height] = $info;

        if (
            $originalSize <= $this->maxStoredBytes
            && $width <= $this->maxDimension
            && $height <= $this->maxDimension
        ) {
            return [
                'contents' => (string) file_get_contents($sourcePath),
                'extension' => $this->extensionFromMime((string) ($info['mime'] ?? '')),
            ];
        }

        if (! extension_loaded('gd')) {
            return $this->passthrough($sourcePath);
        }

        $image = $this->loadImage($sourcePath, (string) ($info['mime'] ?? ''));

        if (! $image instanceof GdImage) {
            return $this->passthrough($sourcePath);
        }

        $image = $this->applyExifOrientation($image, $sourcePath, (string) ($info['mime'] ?? ''));
        $image = $this->resizeIfNeeded($image);

        $best = null;

        for ($quality = $this->startQuality; $quality >= $this->minQuality; $quality -= 5) {
            $jpeg = $this->encodeJpeg($image, $quality);

            if ($best === null || strlen($jpeg) < strlen($best)) {
                $best = $jpeg;
            }

            if (strlen($jpeg) <= $this->maxStoredBytes) {
                imagedestroy($image);

                return ['contents' => $jpeg, 'extension' => 'jpg'];
            }
        }

        $shrunk = $this->resizeToFit($image, (int) (imagesx($image) * 0.85), (int) (imagesy($image) * 0.85));

        if ($shrunk instanceof GdImage) {
            imagedestroy($image);
            $image = $shrunk;

            for ($quality = $this->startQuality; $quality >= $this->minQuality; $quality -= 5) {
                $jpeg = $this->encodeJpeg($image, $quality);

                if ($best === null || strlen($jpeg) < strlen($best)) {
                    $best = $jpeg;
                }

                if (strlen($jpeg) <= $this->maxStoredBytes) {
                    imagedestroy($image);

                    return ['contents' => $jpeg, 'extension' => 'jpg'];
                }
            }
        }

        imagedestroy($image);

        if (! is_string($best) || $best === '') {
            throw new RuntimeException('Unable to compress uploaded image.');
        }

        return ['contents' => $best, 'extension' => 'jpg'];
    }

    /**
     * @return array{contents: string, extension: string}
     */
    private function passthrough(string $sourcePath): array
    {
        return [
            'contents' => (string) file_get_contents($sourcePath),
            'extension' => strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION)) ?: 'jpg',
        ];
    }

    private function loadImage(string $sourcePath, string $mime): ?GdImage
    {
        return match ($mime) {
            'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($sourcePath) ?: null,
            'image/png' => @imagecreatefrompng($sourcePath) ?: null,
            'image/webp' => function_exists('imagecreatefromwebp') ? (@imagecreatefromwebp($sourcePath) ?: null) : null,
            default => null,
        };
    }

    private function applyExifOrientation(GdImage $image, string $sourcePath, string $mime): GdImage
    {
        if (! in_array($mime, ['image/jpeg', 'image/jpg'], true) || ! function_exists('exif_read_data')) {
            return $image;
        }

        $exif = @exif_read_data($sourcePath);

        if (! is_array($exif)) {
            return $image;
        }

        $orientation = (int) ($exif['Orientation'] ?? 1);

        return match ($orientation) {
            3 => imagerotate($image, 180, 0) ?: $image,
            6 => imagerotate($image, -90, 0) ?: $image,
            8 => imagerotate($image, 90, 0) ?: $image,
            default => $image,
        };
    }

    private function resizeIfNeeded(GdImage $image): GdImage
    {
        $width = imagesx($image);
        $height = imagesy($image);
        $maxSide = max($width, $height);

        if ($maxSide <= $this->maxDimension) {
            return $image;
        }

        $ratio = $this->maxDimension / $maxSide;
        $targetWidth = max(1, (int) round($width * $ratio));
        $targetHeight = max(1, (int) round($height * $ratio));

        return $this->resizeToFit($image, $targetWidth, $targetHeight) ?? $image;
    }

    private function resizeToFit(GdImage $image, int $targetWidth, int $targetHeight): ?GdImage
    {
        $canvas = imagecreatetruecolor($targetWidth, $targetHeight);

        if ($canvas === false) {
            return null;
        }

        imagecopyresampled(
            $canvas,
            $image,
            0,
            0,
            0,
            0,
            $targetWidth,
            $targetHeight,
            imagesx($image),
            imagesy($image),
        );

        return $canvas;
    }

    private function encodeJpeg(GdImage $image, int $quality): string
    {
        ob_start();
        imagejpeg($image, null, $quality);
        $contents = ob_get_clean();

        return is_string($contents) ? $contents : '';
    }

    private function extensionFromMime(string $mime): string
    {
        return match ($mime) {
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => 'jpg',
        };
    }
}
