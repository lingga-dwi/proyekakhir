<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('konsultasi', function (Blueprint $table) {
            $table->foreignId('pemesanan_id')->nullable()->unique()->after('user_id')->constrained('pemesanan')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('konsultasi', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pemesanan_id');
        });
    }
};
