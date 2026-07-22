<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pemesanan', function (Blueprint $table) {
            $table->foreignId('designer_id')->nullable()->after('id_user')->constrained('users')->nullOnDelete();
            $table->unsignedTinyInteger('progress')->default(0)->after('status_pemesanan');
            $table->date('target_selesai')->nullable()->after('progress');
            $table->text('catatan_progres')->nullable()->after('target_selesai');
        });

        DB::table('pemesanan')->where('status_pemesanan', 'dikonfirmasi')->update(['progress' => 10]);
        DB::table('pemesanan')->where('status_pemesanan', 'sedang_dikerjakan')->update(['progress' => 25]);
        DB::table('pemesanan')->where('status_pemesanan', 'selesai')->update(['progress' => 100]);
    }

    public function down(): void
    {
        Schema::table('pemesanan', function (Blueprint $table) {
            $table->dropConstrainedForeignId('designer_id');
            $table->dropColumn(['progress', 'target_selesai', 'catatan_progres']);
        });
    }
};
