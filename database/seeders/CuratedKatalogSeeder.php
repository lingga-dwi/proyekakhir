<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Katalog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CuratedKatalogSeeder extends Seeder
{
    private const CATEGORY_NAMES = [
        'dapur' => 'Dapur',
        'kamar-mandi' => 'Kamar Mandi',
        'kamar-tidur' => 'Kamar Tidur',
        'ruang-keluarga' => 'Ruang Keluarga',
        'ruang-kerja' => 'Ruang Kerja',
        'ruang-makan' => 'Ruang Makan',
    ];

    public function run(): void
    {
        $manifestPath = storage_path('app/catalog-import-manifest.json');

        if (! is_file($manifestPath)) {
            $this->command?->warn('Manifest katalog belum tersedia.');

            return;
        }

        $manifest = json_decode(file_get_contents($manifestPath), true, flags: JSON_THROW_ON_ERROR);
        $categories = Category::query()
            ->whereIn('slug', array_keys(self::CATEGORY_NAMES))
            ->get()
            ->keyBy('slug');

        // These are the 19 legacy placeholder records. Keep their history, but
        // remove them from the public catalog in favour of the curated posts.
        Katalog::query()
            ->whereBetween('id', [1, 19])
            ->update(['status' => Katalog::STATUS_ARCHIVED]);

        $sequenceByCategory = [];
        $processed = 0;

        foreach ($manifest['accepted'] as $item) {
            $slug = $item['category'];
            $category = $categories->get($slug);

            if (! $category || empty($item['images'])) {
                continue;
            }

            $sequence = ($sequenceByCategory[$slug] ?? 0) + 1;
            $sequenceByCategory[$slug] = $sequence;
            $name = self::CATEGORY_NAMES[$slug].' Pilihan '.str_pad((string) $sequence, 2, '0', STR_PAD_LEFT);
            $mainImage = $item['images'][0];
            $gallery = array_values(array_slice($item['images'], 1, 12));

            Katalog::updateOrCreate(
                ['gambar_utama' => $mainImage],
                [
                    'category_id' => $category->id,
                    'nama_desain' => $name,
                    'deskripsi' => 'Referensi visual '.Str::lower(self::CATEGORY_NAMES[$slug]).' dari portofolio Daiku.',
                    'galeri_gambar' => $gallery,
                    'style_tags' => 'Inspirasi desain',
                    'inspiration_story' => 'Dokumentasi portofolio untuk referensi awal pembahasan desain.',
                    'status' => Katalog::STATUS_PUBLISHED,
                ],
            );

            $processed++;
        }

        $this->command?->info("Katalog kurasi siap. Data diproses: {$processed}.");
    }
}
