<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('katalog', function (Blueprint $table) {
            $table->id();
            $table->string('nama_desain');
            $table->string('kategori');
            $table->text('deskripsi');
            $table->decimal('harga_estimasi', 12, 2);
            $table->string('gambar_utama')->nullable();
            $table->json('galeri_gambar')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('katalog');
    }
};
