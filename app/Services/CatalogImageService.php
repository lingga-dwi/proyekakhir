<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CatalogImageService
{
    public function store(UploadedFile $file, string $directory): string
    {
        $source = @imagecreatefromstring((string) file_get_contents($file->getRealPath()));

        if (! $source || ! function_exists('imagewebp')) {
            return $file->store($directory, 'public');
        }

        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);
        $scale = min(1, 1920 / max($sourceWidth, $sourceHeight));
        $targetWidth = max(1, (int) round($sourceWidth * $scale));
        $targetHeight = max(1, (int) round($sourceHeight * $scale));
        $target = imagecreatetruecolor($targetWidth, $targetHeight);

        imagealphablending($target, false);
        imagesavealpha($target, true);
        imagecopyresampled(
            $target,
            $source,
            0,
            0,
            0,
            0,
            $targetWidth,
            $targetHeight,
            $sourceWidth,
            $sourceHeight
        );

        ob_start();
        imagewebp($target, null, 82);
        $contents = (string) ob_get_clean();
        imagedestroy($source);
        imagedestroy($target);

        $path = trim($directory, '/').'/'.Str::uuid().'.webp';
        Storage::disk('public')->put($path, $contents);

        return $path;
    }
}
