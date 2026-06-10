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
        Schema::table('katalog', function (Blueprint $table) {
            $table->json('product_spots')->nullable()->after('galeri_gambar');
            $table->string('style_tags')->nullable()->after('product_spots');
            $table->integer('room_size')->nullable()->after('style_tags');
            $table->text('inspiration_story')->nullable()->after('room_size');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('katalog', function (Blueprint $table) {
            $table->dropColumn(['product_spots', 'style_tags', 'room_size', 'inspiration_story']);
        });
    }
};
