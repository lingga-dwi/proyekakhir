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
        Schema::create('konsultasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->string('nama');
            $table->string('email');
            $table->string('no_telp');
            $table->enum('jenis_konsultasi', ['free_consultation', 'virtual_design', 'in_home_visit', 'chat_support']);
            $table->enum('jenis_ruangan', ['living_room', 'bedroom', 'kitchen', 'bathroom', 'office', 'whole_house']);
            $table->enum('budget_range', ['under_10m', '10m_25m', '25m_50m', '50m_100m', 'above_100m']);
            $table->enum('timeline', ['immediate', '1_month', '3_months', '6_months', 'flexible']);
            $table->decimal('luas_ruangan', 8, 2)->nullable();
            $table->string('gaya_preferensi')->nullable();
            $table->text('deskripsi_kebutuhan');
            $table->json('upload_foto')->nullable();
            $table->date('tanggal_konsultasi');
            $table->time('waktu_konsultasi');
            $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled'])->default('pending');
            $table->text('catatan_admin')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('konsultasi');
    }
};
