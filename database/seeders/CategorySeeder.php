<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Parent Categories
        $rumahTinggal = \App\Models\Category::create([
            'name' => 'Rumah Tinggal',
            'slug' => 'rumah-tinggal',
            'description' => 'Desain interior untuk rumah tinggal dan hunian pribadi',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $apartemen = \App\Models\Category::create([
            'name' => 'Apartemen & Kondominium',
            'slug' => 'apartemen-kondominium',
            'description' => 'Desain interior untuk apartemen dan kondominium modern',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $komersial = \App\Models\Category::create([
            'name' => 'Ruang Komersial',
            'slug' => 'ruang-komersial',
            'description' => 'Desain interior untuk kantor, toko, dan ruang bisnis',
            'sort_order' => 3,
            'is_active' => true,
        ]);

        $outdoor = \App\Models\Category::create([
            'name' => 'Outdoor & Taman',
            'slug' => 'outdoor-taman',
            'description' => 'Desain landscape dan ruang luar',
            'sort_order' => 4,
            'is_active' => true,
        ]);

        // Sub Categories untuk Rumah Tinggal
        $ruangKeluarga = \App\Models\Category::create([
            'name' => 'Ruang Keluarga',
            'slug' => 'ruang-keluarga',
            'description' => 'Desain ruang keluarga yang nyaman untuk berkumpul',
            'parent_id' => $rumahTinggal->id,
            'sort_order' => 1,
        ]);

        \App\Models\Category::create([
            'name' => 'Ruang Tamu',
            'slug' => 'ruang-tamu',
            'description' => 'Desain ruang tamu yang elegant untuk menerima tamu',
            'parent_id' => $rumahTinggal->id,
            'sort_order' => 2,
        ]);

        \App\Models\Category::create([
            'name' => 'Kamar Tidur',
            'slug' => 'kamar-tidur',
            'description' => 'Desain kamar tidur yang nyaman dan sesuai kebutuhan penghuni',
            'parent_id' => $rumahTinggal->id,
            'sort_order' => 3,
        ]);

        \App\Models\Category::create([
            'name' => 'Kamar Tidur Anak',
            'slug' => 'kamar-tidur-anak',
            'description' => 'Desain kamar tidur anak yang fun dan edukatif',
            'parent_id' => $rumahTinggal->id,
            'sort_order' => 4,
        ]);

        \App\Models\Category::create([
            'name' => 'Dapur',
            'slug' => 'dapur',
            'description' => 'Desain dapur modern dan fungsional',
            'parent_id' => $rumahTinggal->id,
            'sort_order' => 5,
        ]);

        \App\Models\Category::create([
            'name' => 'Ruang Makan',
            'slug' => 'ruang-makan',
            'description' => 'Desain ruang makan untuk keluarga',
            'parent_id' => $rumahTinggal->id,
            'sort_order' => 6,
        ]);

        \App\Models\Category::create([
            'name' => 'Kamar Mandi',
            'slug' => 'kamar-mandi',
            'description' => 'Desain kamar mandi modern dan higienis',
            'parent_id' => $rumahTinggal->id,
            'sort_order' => 7,
        ]);

        \App\Models\Category::create([
            'name' => 'Ruang Kerja',
            'slug' => 'ruang-kerja',
            'description' => 'Desain home office dan ruang kerja produktif',
            'parent_id' => $rumahTinggal->id,
            'sort_order' => 8,
        ]);

        // Sub Categories untuk Apartemen
        \App\Models\Category::create([
            'name' => 'Studio Apartment',
            'slug' => 'studio-apartment',
            'description' => 'Desain studio apartment yang kompak dan efisien',
            'parent_id' => $apartemen->id,
            'sort_order' => 1,
        ]);

        \App\Models\Category::create([
            'name' => '1 Bedroom',
            'slug' => '1-bedroom',
            'description' => 'Desain apartemen 1 kamar tidur',
            'parent_id' => $apartemen->id,
            'sort_order' => 2,
        ]);

        \App\Models\Category::create([
            'name' => '2 Bedroom',
            'slug' => '2-bedroom',
            'description' => 'Desain apartemen 2 kamar tidur',
            'parent_id' => $apartemen->id,
            'sort_order' => 3,
        ]);

        \App\Models\Category::create([
            'name' => 'Penthouse',
            'slug' => 'penthouse',
            'description' => 'Desain penthouse mewah',
            'parent_id' => $apartemen->id,
            'sort_order' => 4,
        ]);

        \App\Models\Category::create([
            'name' => 'Balkon & Teras',
            'slug' => 'balkon-teras',
            'description' => 'Desain balkon dan teras apartemen',
            'parent_id' => $apartemen->id,
            'sort_order' => 5,
        ]);

        // Sub Categories untuk Komersial
        \App\Models\Category::create([
            'name' => 'Kantor Modern',
            'slug' => 'kantor-modern',
            'description' => 'Desain kantor modern dan produktif',
            'parent_id' => $komersial->id,
            'sort_order' => 1,
        ]);

        \App\Models\Category::create([
            'name' => 'Retail & Toko',
            'slug' => 'retail-toko',
            'description' => 'Desain toko dan ruang retail',
            'parent_id' => $komersial->id,
            'sort_order' => 2,
        ]);

        \App\Models\Category::create([
            'name' => 'Restoran & Kafe',
            'slug' => 'restoran-kafe',
            'description' => 'Desain restoran dan kafe',
            'parent_id' => $komersial->id,
            'sort_order' => 3,
        ]);

        \App\Models\Category::create([
            'name' => 'Hotel & Hospitality',
            'slug' => 'hotel-hospitality',
            'description' => 'Desain hotel dan industri hospitality',
            'parent_id' => $komersial->id,
            'sort_order' => 4,
        ]);

        // Sub Categories untuk Outdoor
        \App\Models\Category::create([
            'name' => 'Taman Rumah',
            'slug' => 'taman-rumah',
            'description' => 'Desain taman dan landscape rumah',
            'parent_id' => $outdoor->id,
            'sort_order' => 1,
        ]);

        \App\Models\Category::create([
            'name' => 'Kolam Renang',
            'slug' => 'kolam-renang',
            'description' => 'Desain area kolam renang',
            'parent_id' => $outdoor->id,
            'sort_order' => 2,
        ]);

        \App\Models\Category::create([
            'name' => 'Gazebo & Pergola',
            'slug' => 'gazebo-pergola',
            'description' => 'Desain gazebo dan pergola outdoor',
            'parent_id' => $outdoor->id,
            'sort_order' => 3,
        ]);
    }
}
