<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (app()->environment(['local', 'testing'])) {
            $this->call(UserSeeder::class);
        }

        $this->call([
            CategorySeeder::class,
            KatalogSeeder::class,
            ProjectDummySeeder::class,
        ]);
    }
}
