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
        Schema::create('pemesanan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_rfq')->nullable();
            $table->foreignId('id_user')->constrained('users');
            $table->date('tanggal_pesan');
            $table->enum('status_pemesanan', ['pending', 'dikonfirmasi', 'sedang_dikerjakan', 'selesai', 'dibatalkan'])->default('pending');
            $table->string('jenis_proyek')->nullable();
            $table->string('jenis_bangunan')->nullable();
            $table->decimal('luas_area', 8, 2)->nullable();
            $table->integer('jumlah_ruangan')->nullable();
            $table->string('gaya_desain_preferensi')->nullable();
            $table->string('warna_dominan')->nullable();
            $table->text('deskripsi_keinginan_desain')->nullable();
            $table->json('upload_denah_foto')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pemesanan');
    }
};
