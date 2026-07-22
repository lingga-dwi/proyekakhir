<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('katalog', function (Blueprint $table) {
            $table->string('status', 20)->default('draft')->after('inspiration_story')->index();
        });

        DB::table('katalog')->update(['status' => 'published']);

        DB::table('katalog')
            ->where(function ($query) {
                $query->whereNull('category_id')
                    ->orWhereNull('gambar_utama')
                    ->orWhere('gambar_utama', '')
                    ->orWhereNull('deskripsi')
                    ->orWhere('deskripsi', '');
            })
            ->update(['status' => 'draft']);
    }

    public function down(): void
    {
        Schema::table('katalog', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropColumn('status');
        });
    }
};
