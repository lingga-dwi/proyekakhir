<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Katalog;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Deployment memakai database baru. Isi katalog hanya sekali saat masih
        // kosong; seed berikutnya tidak boleh menimpa katalog yang dikelola admin.
        if (Katalog::query()->exists()) {
            return;
        }

        if (! Category::query()->exists()) {
            $this->call(CategorySeeder::class);
        }

        $this->call([
            CuratedKatalogSeeder::class,
        ]);
    }
}
