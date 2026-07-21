<?php

namespace Tests\Feature;

use App\Models\Katalog;
use Database\Seeders\CategorySeeder;
use Database\Seeders\KatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KatalogSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_seed_data_has_matching_categories_and_existing_images(): void
    {
        $this->seed(CategorySeeder::class);
        $this->seed(KatalogSeeder::class);
        $this->seed(KatalogSeeder::class);

        $items = Katalog::with('category')->get();

        $this->assertCount(19, $items);
        $this->assertSame(0, Katalog::whereNull('category_id')->count());

        foreach ($items as $item) {
            $this->assertNotNull($item->category, $item->nama_desain.' tidak memiliki kategori.');
            $this->assertFileExists(public_path($item->gambar_utama));

            foreach ($item->galeri_gambar ?? [] as $image) {
                $this->assertFileExists(public_path($image));
            }
        }
    }
}
