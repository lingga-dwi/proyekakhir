<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Services\CatalogImageService;

class Katalog extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    public const STATUS_ARCHIVED = 'archived';

    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_PUBLISHED,
        self::STATUS_ARCHIVED,
    ];

    protected $table = 'katalog';

    protected $fillable = [
        'category_id',
        'nama_desain',
        'deskripsi',
        'gambar_utama',
        'galeri_gambar',
        'style_tags',
        'room_size',
        'inspiration_story',
        'status',
    ];

    protected $casts = [
        'galeri_gambar' => 'array',
    ];

    // Relationships
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function pemesanans()
    {
        return $this->hasMany(Pemesanan::class, 'katalog_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function scopeArchived(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ARCHIVED);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', '!=', self::STATUS_ARCHIVED);
    }

    public function scopeComplete(Builder $query): Builder
    {
        return $query->active()
            ->whereNotNull('category_id')
            ->whereNotNull('gambar_utama')
            ->where('gambar_utama', '!=', '')
            ->whereNotNull('deskripsi')
            ->where('deskripsi', '!=', '');
    }

    public function scopeIncomplete(Builder $query): Builder
    {
        return $query
            ->active()
            ->where(function (Builder $query) {
                $query->whereNull('category_id')
                    ->orWhereNull('gambar_utama')
                    ->orWhere('gambar_utama', '')
                    ->orWhereNull('deskripsi')
                    ->orWhere('deskripsi', '');
            });
    }

    public function isCompleteForPublication(): bool
    {
        return filled($this->category_id)
            && filled($this->gambar_utama)
            && filled($this->deskripsi);
    }

    public function getGambarUtamaUrlAttribute()
    {
        try {
            return $this->resolveImageUrl($this->gambar_utama);
        } catch (\Throwable $exception) {
            logger()->warning('Invalid catalog main image path', [
                'catalog_id' => $this->id,
                'error' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    public function getGaleriGambarUrlsAttribute(): array
    {
        if (! $this->galeri_gambar) {
            return [];
        }

        return array_values(array_filter(array_map(function ($path) {
            try {
                // Do not force fallback image for gallery entries.
                return $this->resolveImageUrl($path, false);
            } catch (\Throwable $exception) {
                logger()->warning('Invalid catalog gallery image path', [
                    'catalog_id' => $this->id,
                    'error' => $exception->getMessage(),
                ]);

                return null;
            }
        }, is_array($this->galeri_gambar) ? $this->galeri_gambar : [])));
    }

    public function galleryImageUrl(mixed $path): ?string
    {
        return $this->resolveImageUrl($path, false);
    }

    private function resolveImageUrl(mixed $path, bool $allowFallback = true): ?string
    {
        $normalizedPath = $this->normalizePath($path);

        if ($normalizedPath) {
            if (str_starts_with($normalizedPath, 'http://') || str_starts_with($normalizedPath, 'https://')) {
                return $normalizedPath;
            }

            if (str_starts_with($normalizedPath, 'katalog/')) {
                return app(CatalogImageService::class)->url($normalizedPath);
            }

            $candidates = [$normalizedPath];

            if (str_starts_with($normalizedPath, 'katalog/')) {
                // Legacy DB values from old seeder: katalog/file.jpg -> images/katalog/file.jpg
                $candidates[] = 'images/'.$normalizedPath;
            }

            if (! str_starts_with($normalizedPath, 'images/') && ! str_starts_with($normalizedPath, 'storage/')) {
                $candidates[] = 'images/'.ltrim($normalizedPath, '/');
            }

            foreach (array_unique($candidates) as $candidate) {
                $candidate = ltrim($candidate, '/');

                if (is_file(public_path($candidate))) {
                    return asset($candidate);
                }

                if (Storage::disk('public')->exists($candidate)) {
                    return Storage::url($candidate);
                }
            }
        }

        if (! $allowFallback) {
            return null;
        }

        return $this->categoryFallbackImageUrl();
    }

    private function normalizePath(mixed $path): ?string
    {
        // Data katalog lama dapat berisi nilai JSON/array yang tidak valid
        // untuk URL. Jangan biarkan satu record rusak menjatuhkan seluruh
        // halaman katalog dengan TypeError.
        if (! is_string($path) && ! is_numeric($path)) {
            return null;
        }

        $normalized = trim(str_replace('\\', '/', (string) $path));

        return $normalized !== '' ? $normalized : null;
    }

    private function categoryFallbackImageUrl(): ?string
    {
        $folder = $this->fallbackFolder();
        $files = $this->catalogImagesByFolder($folder);

        if (empty($files)) {
            return null;
        }

        // Stable fallback per item to avoid random image changes on each request.
        $seed = $this->id ?? crc32((string) $this->nama_desain);
        $index = abs((int) $seed) % count($files);

        return asset($files[$index]);
    }

    private function fallbackFolder(): string
    {
        $kategori = strtolower((string) $this->category?->name);

        if (str_contains($kategori, 'kantor')) {
            return 'kantor';
        }

        if (str_contains($kategori, 'usaha') || str_contains($kategori, 'toko') || str_contains($kategori, 'resto')) {
            return 'usaha';
        }

        // Most room-based catalogs belong to home/residential.
        return 'rumah';
    }

    private static array $catalogImageCache = [];

    private function catalogImagesByFolder(string $folder): array
    {
        if (isset(self::$catalogImageCache[$folder])) {
            return self::$catalogImageCache[$folder];
        }

        $basePath = public_path('images/katalog/'.$folder);
        $glob = glob($basePath.'/*.{jpg,jpeg,png,webp,JPG,JPEG,PNG,WEBP}', GLOB_BRACE) ?: [];
        sort($glob);

        $relative = array_values(array_map(
            fn ($fullPath) => 'images/katalog/'.$folder.'/'.basename($fullPath),
            $glob
        ));

        self::$catalogImageCache[$folder] = $relative;

        return $relative;
    }
}
