<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->string('question');
            $table->text('answer');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        $now = now();
        DB::table('faqs')->insert([
            ['question' => 'Bagaimana proses konsultasi dengan Daiku?', 'answer' => 'Mulailah dengan mengirim informasi proyek. Tim Daiku meninjau kebutuhan ruang Anda, lalu menghubungi untuk membahas langkah dan cakupan pekerjaan berikutnya.', 'sort_order' => 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['question' => 'Informasi apa yang perlu disiapkan?', 'answer' => 'Siapkan jenis proyek, jenis bangunan, perkiraan luas area, anggaran, serta catatan kebutuhan. Foto atau referensi desain dapat dibahas saat tindak lanjut.', 'sort_order' => 2, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['question' => 'Apakah Daiku menerima furnitur custom?', 'answer' => 'Ya. Kebutuhan furnitur seperti kitchen set, kabinet built-in, meja kerja, dan penyimpanan dapat disesuaikan dengan fungsi serta ukuran ruang.', 'sort_order' => 3, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['question' => 'Apakah melayani rumah, kantor, dan ruang usaha?', 'answer' => 'Daiku melayani kebutuhan interior untuk hunian, ruang kerja, serta ruang usaha. Ceritakan fungsi ruang Anda pada formulir agar peninjauan awal lebih tepat.', 'sort_order' => 4, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['question' => 'Apakah Daiku melayani renovasi interior?', 'answer' => 'Ya. Daiku dapat membantu penataan dan renovasi interior sesuai kondisi ruang yang ada, mulai dari pembahasan kebutuhan hingga pengerjaan yang disepakati.', 'sort_order' => 5, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['question' => 'Bagaimana perkiraan biaya proyek ditentukan?', 'answer' => 'Perkiraan biaya disusun berdasarkan ukuran ruang, lingkup pekerjaan, material, furnitur, dan detail desain. Tim Daiku akan menyampaikan penawaran setelah kebutuhan proyek ditinjau.', 'sort_order' => 6, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['question' => 'Berapa lama proses desain dan pengerjaan berlangsung?', 'answer' => 'Durasi menyesuaikan skala proyek, kondisi lokasi, serta tingkat detail pekerjaan. Estimasi jadwal akan dibahas bersama setelah konsultasi dan ruang lingkup proyek disepakati.', 'sort_order' => 7, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('faqs');
    }
};
