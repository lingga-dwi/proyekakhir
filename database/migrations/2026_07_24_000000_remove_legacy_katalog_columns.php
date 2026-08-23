<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $legacyColumns = collect(['kategori', 'harga_estimasi', 'product_spots'])
            ->filter(fn (string $column) => Schema::hasColumn('katalog', $column))
            ->all();

        if ($legacyColumns !== []) {
            Schema::table('katalog', function (Blueprint $table) use ($legacyColumns) {
                $table->dropColumn($legacyColumns);
            });
        }
    }

    public function down(): void
    {
        Schema::table('katalog', function (Blueprint $table) {
            $table->string('kategori')->nullable();
            $table->decimal('harga_estimasi', 12, 2)->default(0);
            $table->json('product_spots')->nullable();
        });
    }
};
