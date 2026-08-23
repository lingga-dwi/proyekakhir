<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $collections = [
            'Dapur' => [
                'description' => 'Portofolio kitchen set custom Daiku untuk hunian, dirancang dengan komposisi kabinet yang rapi dan fungsional.',
                'titles' => ['Kitchen Set Natural Oak', 'Kitchen Set Sage Green', 'Kitchen Set Dark Walnut', 'Kitchen Set Soft Olive', 'Kitchen Set Teal Modern', 'Kitchen Set Graphite', 'Kitchen Set Mocha', 'Kitchen Set Compact White', 'Kitchen Set Urban Oak', 'Kitchen Set Earth Tone', 'Kitchen Set Warm Beige', 'Kitchen Set Modern Linear', 'Kitchen Set Scandinavian', 'Kitchen Set Japandi', 'Kitchen Set Matte Black', 'Kitchen Set Creamy', 'Kitchen Set Island', 'Kitchen Set Layout L', 'Kitchen Set Minimalis Terang', 'Kitchen Set Walnut', 'Kitchen Set Industrial', 'Kitchen Set Panel Kayu', 'Kitchen Set Soft Grey', 'Kitchen Set Bright White', 'Kitchen Set Modern Classic', 'Kitchen Set Charcoal', 'Kitchen Set Compact Natural', 'Kitchen Set Kontemporer', 'Kitchen Set Elegant Dark', 'Kitchen Set Stone Grey', 'Kitchen Set Warm White', 'Kitchen Set Modern Sage', 'Kitchen Set Built-in'],
            ],
            'Kamar Mandi' => [
                'description' => 'Portofolio kamar mandi Daiku dengan penataan sanitair, material, dan pencahayaan yang nyaman untuk kebutuhan hunian.',
                'titles' => ['Kamar Mandi Spa Natural', 'Kamar Mandi Marble Modern', 'Kamar Mandi Minimalis Cerah', 'Kamar Mandi Hotel Style', 'Kamar Mandi Grey Stone', 'Kamar Mandi Compact Modern', 'Kamar Mandi Earth Tone', 'Kamar Mandi Clean White', 'Kamar Mandi Dark Marble', 'Kamar Mandi Warm Wood', 'Kamar Mandi Monokrom', 'Kamar Mandi Soft Beige', 'Kamar Mandi Industrial Clean', 'Kamar Mandi Modern Terrazzo', 'Kamar Mandi Japandi', 'Kamar Mandi Navy Accent', 'Kamar Mandi Luxury Gold', 'Kamar Mandi Minimalis Kayu', 'Kamar Mandi White Marble', 'Kamar Mandi Slate Grey', 'Kamar Mandi Contemporary', 'Kamar Mandi Compact Stone', 'Kamar Mandi Modern Black', 'Kamar Mandi Natural Light', 'Kamar Mandi Elegant Cream', 'Kamar Mandi Modern Oak', 'Kamar Mandi Urban Grey', 'Kamar Mandi Fresh Green', 'Kamar Mandi Calm Neutral', 'Kamar Mandi Modern Tile', 'Kamar Mandi Serene White', 'Kamar Mandi Classic Modern', 'Kamar Mandi Soft Stone'],
            ],
            'Kamar Tidur' => [
                'description' => 'Portofolio bedroom set custom Daiku yang memadukan area tidur, penyimpanan, dan detail interior untuk hunian.',
                'titles' => ['Bedroom Set Modern Warm', 'Bedroom Set Scandinavian', 'Bedroom Set Minimalis Natural', 'Bedroom Set Elegant Grey', 'Bedroom Set Japandi', 'Bedroom Set Soft Beige', 'Bedroom Set Modern Classic', 'Bedroom Set Compact', 'Bedroom Set Walnut', 'Bedroom Set Clean White', 'Bedroom Set Earth Tone', 'Bedroom Set Urban Dark', 'Bedroom Set Pastel Calm', 'Bedroom Set Contemporary', 'Bedroom Set Luxury Neutral', 'Bedroom Set Natural Oak', 'Bedroom Set Monokrom', 'Bedroom Set Cozy Brown', 'Bedroom Set Soft Blue', 'Bedroom Set Minimalis Terang', 'Bedroom Set Built-in', 'Bedroom Set Modern Cream', 'Bedroom Set Dark Wood', 'Bedroom Set Serene Grey', 'Bedroom Set Warm White', 'Bedroom Set Panel Kayu', 'Bedroom Set Calm Green', 'Bedroom Set Elegant Marble', 'Bedroom Set Modern Mocha', 'Bedroom Set Compact Studio', 'Bedroom Set Light Wood', 'Bedroom Set Modern Navy', 'Bedroom Set Classic White', 'Bedroom Set Stone Grey', 'Bedroom Set Soft Blush', 'Bedroom Set Modern Black', 'Bedroom Set Rustic Warm', 'Bedroom Set Neutral Luxe', 'Bedroom Set Modern Taupe', 'Bedroom Set Full Interior'],
            ],
            'Ruang Kerja' => [
                'description' => 'Portofolio ruang kerja Daiku dengan meja, penyimpanan, dan pencahayaan yang mendukung aktivitas produktif.',
                'titles' => ['Ruang Kerja Modern', 'Ruang Kerja Built-in', 'Ruang Kerja Minimalis', 'Ruang Kerja Kontemporer'],
            ],
            'Ruang Makan' => [
                'description' => 'Portofolio ruang makan Daiku dengan penataan meja makan, kabinet, dan pencahayaan untuk suasana yang hangat.',
                'titles' => ['Ruang Makan Modern Hangat'],
            ],
            'Ruang Keluarga' => [
                'description' => 'Portofolio ruang keluarga Daiku yang mengutamakan kenyamanan berkumpul, penyimpanan, dan komposisi visual yang seimbang.',
                'titles' => ['Ruang Keluarga Natural', 'Ruang Keluarga Modern', 'Ruang Keluarga Kontemporer'],
            ],
        ];

        foreach ($collections as $prefix => $collection) {
            $catalogs = DB::table('katalog')
                ->where('nama_desain', 'like', $prefix.' Pilihan%')
                ->orderBy('id')
                ->get(['id']);

            foreach ($catalogs as $index => $catalog) {
                if (! isset($collection['titles'][$index])) {
                    continue;
                }

                DB::table('katalog')->where('id', $catalog->id)->update([
                    'nama_desain' => $collection['titles'][$index],
                    'deskripsi' => $collection['description'],
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // Nama portofolio dibuat secara editorial; tidak dikembalikan ke label generik.
    }
};
