<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $bedroomCategoryId = DB::table('categories')
            ->whereIn('slug', ['kamar-tidur', 'kamar-tidur-utama'])
            ->orWhere('name', 'Kamar Tidur')
            ->value('id');

        DB::table('katalog')
            ->where('kategori', '2 Bedroom')
            ->update([
                'kategori' => 'Kamar Tidur',
                'category_id' => $bedroomCategoryId,
            ]);
    }

    public function down(): void
    {
        DB::table('katalog')
            ->where('nama_desain', 'Apartemen 2 Kamar Modern')
            ->update([
                'kategori' => '2 Bedroom',
                'category_id' => null,
            ]);
    }
};
