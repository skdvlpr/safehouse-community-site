<?php

namespace Tests\Unit;

use App\Support\UploadedImageCompressor;
use Tests\TestCase;

class UploadedImageCompressorTest extends TestCase
{
    public function test_small_image_is_stored_without_reencoding(): void
    {
        $path = $this->createTempJpeg(400, 300, 80);
        $original = file_get_contents($path);

        $result = app(UploadedImageCompressor::class)->process($path);

        $this->assertSame($original, $result['contents']);
        $this->assertSame('jpg', $result['extension']);
    }

    public function test_large_image_is_compressed_below_max_stored_size(): void
    {
        $path = $this->createTempJpeg(4200, 2800, 95);
        $targetBytes = 100 * 1024;

        $compressor = new UploadedImageCompressor(
            maxStoredBytes: $targetBytes,
            maxDimension: 1200,
        );

        $result = $compressor->process($path);

        $this->assertLessThanOrEqual($targetBytes, strlen($result['contents']));
        $this->assertSame('jpg', $result['extension']);
    }

    private function createTempJpeg(int $width, int $height, int $quality): string
    {
        $image = imagecreatetruecolor($width, $height);
        $background = imagecolorallocate($image, 180, 40, 40);
        imagefilledrectangle($image, 0, 0, $width, $height, $background);

        $path = tempnam(sys_get_temp_dir(), 'carousel-test-').'.jpg';
        imagejpeg($image, $path, $quality);
        imagedestroy($image);

        return $path;
    }
}
