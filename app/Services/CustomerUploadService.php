<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomerUploadService
{
    /** @param array<int, UploadedFile> $files */
    public function storeMany(array $files, string $directory): array
    {
        return collect($files)
            ->map(fn (UploadedFile $file) => $file->store($directory, 'local'))
            ->filter()
            ->values()
            ->all();
    }

    public function response(array $paths, int $index): StreamedResponse
    {
        abort_unless(array_key_exists($index, $paths), 404);

        $path = $paths[$index];
        $disk = Storage::disk('local')->exists($path) ? 'local' : 'public';

        abort_unless(Storage::disk($disk)->exists($path), 404);

        return Storage::disk($disk)->response($path, basename($path), [
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
