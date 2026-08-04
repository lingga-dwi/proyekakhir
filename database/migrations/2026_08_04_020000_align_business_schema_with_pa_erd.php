<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('konsultasi', function (Blueprint $table) {
            $table->dropUnique('konsultasi_active_slot_unique');
            $table->dropColumn(['gaya_preferensi', 'upload_foto', 'active_slot']);
        });

        Schema::table('pemesanan', function (Blueprint $table) {
            $table->dropColumn(['jumlah_ruangan', 'gaya_desain_preferensi', 'warna_dominan', 'upload_denah_foto']);
        });
    }

    public function down(): void
    {
        // Penyelarasan ini sengaja satu arah agar struktur mengikuti ERD PA.
    }
};
