<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Proyek lama yang memang sudah dikerjakan/selesai tidak boleh kembali
        // tertahan pada tahap desain awal setelah alur baru diterapkan.
        DB::table('pemesanan')
            ->whereIn('status_pemesanan', ['sedang_dikerjakan', 'selesai'])
            ->update(['workflow_stage' => 'approved']);
    }

    public function down(): void
    {
        // Nilai tahap lama tidak dapat diturunkan secara andal.
    }
};
