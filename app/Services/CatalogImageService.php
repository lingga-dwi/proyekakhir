<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CatalogImageService
{
    private function diskName(): string
    {
        // Menjaga test dan pengembangan lama yang masih memalsukan disk public.
        return app()->environment('testing') ? 'public' : 'catalog_images';
    }

    public function store(UploadedFile $file, string $directory): string
    {
        if (! function_exists('imagecreatefromstring')
            || ! function_exists('imagecreatetruecolor')
            || ! function_exists('imagewebp')) {
            return $file->store($directory, $this->diskName());
        }

        $source = @imagecreatefromstring((string) file_get_contents($file->getRealPath()));

        if (! $source) {
            return $file->store($directory, $this->diskName());
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
        Storage::disk($this->diskName())->put($path, $contents, ['visibility' => 'public']);

        return $path;
    }

    public function delete(?string $path): void
    {
        // Hanya file hasil unggahan admin yang boleh dihapus. Asset kurasi
        // bawaan di public/images tetap menjadi bagian dari source aplikasi.
        if ($path && str_starts_with(str_replace('\\', '/', $path), 'katalog/')) {
            Storage::disk($this->diskName())->delete($path);
        }
    }

    public function url(string $path): string
    {
        $disk = config('filesystems.disks.catalog_images');

        if (app()->environment('testing') || ($disk['driver'] ?? 'local') === 'local') {
            return Storage::disk($this->diskName())->url($path);
        }

        $baseUrl = rtrim((string) ($disk['url'] ?? ''), '/');

        return $baseUrl.'/'.ltrim($path, '/');
    }
}
