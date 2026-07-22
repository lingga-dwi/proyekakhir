<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Katalog extends Model
{
    use HasFactory;

    protected $table = 'katalog';

    protected $fillable = [
        'category_id',
        'nama_desain',
        'kategori',
        'deskripsi',
        'harga_estimasi',
        'gambar_utama',
        'galeri_gambar',
        'product_spots',
        'style_tags',
        'room_size',
        'inspiration_story',
        'status',
    ];

    protected $casts = [
        'galeri_gambar' => 'array',
        'product_spots' => 'array',
        'harga_estimasi' => 'decimal:2',
    ];

    // Relationships
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function rfqs()
    {
        return $this->hasMany(Rfq::class, 'id_katalog');
    }

    public function pemesanans()
    {
        return $this->hasMany(Pemesanan::class, 'katalog_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function scopeArchived(Builder $query): Builder
    {
        return $query->where('status', 'archived');
    }

    public function scopeIncomplete(Builder $query): Builder
    {
        return $query
            ->where('status', '!=', 'archived')
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

    // Helper methods
    public function getFormattedHargaAttribute()
    {
        return 'Rp '.number_format($this->harga_estimasi, 0, ',', '.');
    }

    public function getGambarUtamaUrlAttribute()
    {
        return $this->resolveImageUrl($this->gambar_utama);
    }

    public function getGaleriGambarUrlsAttribute(): array
    {
        if (! $this->galeri_gambar) {
            return [];
        }

        return array_values(array_filter(array_map(function ($path) {
            // Do not force fallback image for gallery entries.
            return $this->resolveImageUrl($path, false);
        }, $this->galeri_gambar)));
    }

    public function galleryImageUrl(?string $path): ?string
    {
        return $this->resolveImageUrl($path, false);
    }

    private function resolveImageUrl(?string $path, bool $allowFallback = true): ?string
    {
        $normalizedPath = $this->normalizePath($path);

        if ($normalizedPath) {
            if (str_starts_with($normalizedPath, 'http://') || str_starts_with($normalizedPath, 'https://')) {
                return $normalizedPath;
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

    private function normalizePath(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        $normalized = trim(str_replace('\\', '/', $path));

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
        $kategori = strtolower((string) $this->kategori);

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
