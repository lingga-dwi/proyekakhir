<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\Katalog;
use App\Models\Pembayaran;
use App\Models\Pemesanan;
use App\Models\Rfq;
use App\Models\StatusTracking;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ProjectDummySeeder extends Seeder
{
    /**
     * Seed dummy project and order data for admin pages.
     */
    public function run(): void
    {
        if (Pemesanan::query()->exists()) {
            return;
        }

        Rfq::query()->whereDoesntHave('pemesanan')->delete();

        $pelanggan = User::query()
            ->where('role', 'pelanggan')
            ->orderBy('id')
            ->get();

        $katalog = Katalog::query()->orderBy('id')->get();

        if ($pelanggan->isEmpty() || $katalog->isEmpty()) {
            return;
        }

        $blueprints = [
            [
                'title' => 'Redesain Ruang Tamu Minimalis',
                'status' => 'pending',
                'rfq_status' => 'pending',
                'harga' => 28000000,
                'days_ago' => 2,
                'jatuh_tempo_in_days' => 14,
                'jenis_proyek' => 'Interior Ruang Tamu',
                'jenis_bangunan' => 'Rumah Tinggal',
                'luas_area' => 24,
                'jumlah_ruangan' => 1,
                'gaya' => 'Minimalis Modern',
                'warna' => 'Putih, beige, oak muda',
                'catatan' => 'Klien ingin ruang tamu terasa lebih luas dan ramah keluarga.',
                'tracking' => [
                    ['status' => 'pending', 'offset_days' => 2, 'catatan' => 'Pengajuan proyek baru diterima dan menunggu verifikasi admin.'],
                ],
            ],
            [
                'title' => 'Desain Kamar Tidur Anak',
                'status' => 'dikonfirmasi',
                'rfq_status' => 'disetujui',
                'harga' => 18500000,
                'days_ago' => 5,
                'jatuh_tempo_in_days' => 10,
                'jenis_proyek' => 'Interior Kamar Tidur',
                'jenis_bangunan' => 'Rumah Tinggal',
                'luas_area' => 16,
                'jumlah_ruangan' => 1,
                'gaya' => 'Scandinavian Fun',
                'warna' => 'Putih, biru pastel, kayu alami',
                'catatan' => 'Butuh kamar anak yang rapi, terang, dan punya area belajar kecil.',
                'tracking' => [
                    ['status' => 'pending', 'offset_days' => 5, 'catatan' => 'Pengajuan awal diterima dari pelanggan.'],
                    ['status' => 'dikonfirmasi', 'offset_days' => 4, 'catatan' => 'Admin menyetujui kebutuhan proyek dan memulai konsultasi awal.'],
                ],
            ],
            [
                'title' => 'Kitchen Set Apartemen Studio',
                'status' => 'sedang_dikerjakan',
                'rfq_status' => 'disetujui',
                'harga' => 42000000,
                'days_ago' => 9,
                'jatuh_tempo_in_days' => 7,
                'jenis_proyek' => 'Kitchen Set',
                'jenis_bangunan' => 'Apartemen',
                'luas_area' => 12,
                'jumlah_ruangan' => 1,
                'gaya' => 'Japandi Compact',
                'warna' => 'Broken white, walnut, hitam matte',
                'catatan' => 'Layout dapur perlu efisien dengan banyak penyimpanan tersembunyi.',
                'tracking' => [
                    ['status' => 'pending', 'offset_days' => 9, 'catatan' => 'Pengajuan diterima.'],
                    ['status' => 'dikonfirmasi', 'offset_days' => 8, 'catatan' => 'Kebutuhan ruang dan preferensi desain telah dikonfirmasi.'],
                    ['status' => 'sedang_dikerjakan', 'offset_days' => 6, 'catatan' => 'Tim desain mulai menyusun layout dan moodboard awal.'],
                ],
            ],
            [
                'title' => 'Renovasi Ruang Keluarga',
                'status' => 'selesai',
                'rfq_status' => 'disetujui',
                'harga' => 65000000,
                'days_ago' => 18,
                'jatuh_tempo_in_days' => -1,
                'jenis_proyek' => 'Interior Ruang Keluarga',
                'jenis_bangunan' => 'Rumah Tinggal',
                'luas_area' => 35,
                'jumlah_ruangan' => 1,
                'gaya' => 'Modern Warm',
                'warna' => 'Krem, terracotta, kayu gelap',
                'catatan' => 'Klien meminta ruang keluarga hangat untuk area kumpul dan nonton bersama.',
                'tracking' => [
                    ['status' => 'pending', 'offset_days' => 18, 'catatan' => 'Permintaan desain diterima.'],
                    ['status' => 'dikonfirmasi', 'offset_days' => 17, 'catatan' => 'Tim menyepakati ruang lingkup pekerjaan.'],
                    ['status' => 'sedang_dikerjakan', 'offset_days' => 14, 'catatan' => 'Proses desain dan revisi berjalan sesuai jadwal.'],
                    ['status' => 'selesai', 'offset_days' => 3, 'catatan' => 'Final desain disetujui dan proyek dinyatakan selesai.'],
                ],
                'invoice_status' => 'dibayar',
                'payment_method' => 'transfer_bank',
            ],
            [
                'title' => 'Home Office Produktif',
                'status' => 'selesai',
                'rfq_status' => 'disetujui',
                'harga' => 33000000,
                'days_ago' => 13,
                'jatuh_tempo_in_days' => 2,
                'jenis_proyek' => 'Interior Home Office',
                'jenis_bangunan' => 'Rumah Tinggal',
                'luas_area' => 14,
                'jumlah_ruangan' => 1,
                'gaya' => 'Industrial Clean',
                'warna' => 'Abu-abu muda, hitam, kayu oak',
                'catatan' => 'Ruang kerja butuh meja custom, rak dokumen, dan pencahayaan fokus.',
                'tracking' => [
                    ['status' => 'pending', 'offset_days' => 13, 'catatan' => 'Kebutuhan ruang kerja diterima.'],
                    ['status' => 'dikonfirmasi', 'offset_days' => 12, 'catatan' => 'Diskusi kebutuhan fungsi dan ergonomi selesai.'],
                    ['status' => 'sedang_dikerjakan', 'offset_days' => 10, 'catatan' => 'Desain final sedang diproduksi.'],
                    ['status' => 'selesai', 'offset_days' => 1, 'catatan' => 'Pekerjaan selesai dan siap serah terima.'],
                ],
                'invoice_status' => 'pending',
            ],
            [
                'title' => 'Revamp Kafe Mini',
                'status' => 'dibatalkan',
                'rfq_status' => 'ditolak',
                'harga' => 54000000,
                'days_ago' => 7,
                'jatuh_tempo_in_days' => 5,
                'jenis_proyek' => 'Interior Komersial',
                'jenis_bangunan' => 'Ruko / Kafe',
                'luas_area' => 28,
                'jumlah_ruangan' => 2,
                'gaya' => 'Urban Industrial',
                'warna' => 'Hitam, bata ekspos, kayu coklat',
                'catatan' => 'Rencana diubah oleh klien karena penyesuaian budget dan jadwal renovasi.',
                'tracking' => [
                    ['status' => 'pending', 'offset_days' => 7, 'catatan' => 'Permintaan desain kafe masuk ke antrean.'],
                    ['status' => 'dibatalkan', 'offset_days' => 5, 'catatan' => 'Klien menunda proyek dan pengajuan dibatalkan.'],
                ],
                'invoice_status' => 'overdue',
            ],
        ];

        foreach ($blueprints as $index => $blueprint) {
            $customer = $pelanggan[$index % $pelanggan->count()];
            $catalogItem = $katalog[$index % $katalog->count()];
            $baseDate = Carbon::now()->subDays($blueprint['days_ago']);

            $rfq = Rfq::create([
                'id_user' => $customer->id,
                'id_katalog' => $catalogItem->id,
                'tanggal_pengajuan' => $baseDate->copy()->toDateString(),
                'kebutuhan_proyek' => $blueprint['catatan'],
                'status_rfq' => $blueprint['rfq_status'],
            ]);

            $pemesanan = Pemesanan::create([
                'id_rfq' => $rfq->id,
                'id_user' => $customer->id,
                'tanggal_pesan' => $baseDate->copy()->addDay()->toDateString(),
                'status_pemesanan' => $blueprint['status'],
                'total_harga' => $blueprint['harga'],
                'jenis_proyek' => $blueprint['jenis_proyek'],
                'jenis_bangunan' => $blueprint['jenis_bangunan'],
                'luas_area' => $blueprint['luas_area'],
                'jumlah_ruangan' => $blueprint['jumlah_ruangan'],
                'gaya_desain_preferensi' => $blueprint['gaya'],
                'warna_dominan' => $blueprint['warna'],
                'deskripsi_keinginan_desain' => $blueprint['title'] . '. ' . $blueprint['catatan'],
                'upload_denah_foto' => [],
            ]);

            foreach ($blueprint['tracking'] as $tracking) {
                StatusTracking::create([
                    'id_pemesanan' => $pemesanan->id,
                    'status' => $tracking['status'],
                    'tanggal_update' => Carbon::now()->subDays($tracking['offset_days'])->toDateString(),
                    'catatan' => $tracking['catatan'],
                ]);
            }

            $invoice = Invoice::create([
                'id_pemesanan' => $pemesanan->id,
                'total_tagihan' => $blueprint['harga'],
                'status_invoice' => $blueprint['invoice_status'] ?? ($blueprint['status'] === 'selesai' ? 'dibayar' : 'pending'),
                'tanggal_jatuh_tempo' => Carbon::now()->addDays($blueprint['jatuh_tempo_in_days'])->toDateString(),
            ]);

            if (($blueprint['invoice_status'] ?? null) === 'dibayar' || $blueprint['status'] === 'selesai') {
                Pembayaran::create([
                    'id_invoice' => $invoice->id,
                    'jumlah_bayar' => $blueprint['harga'],
                    'tanggal_bayar' => Carbon::now()->subDays(max(1, $blueprint['days_ago'] - 1))->toDateString(),
                    'metode_bayar' => $blueprint['payment_method'] ?? 'transfer_bank',
                    'bukti_pembayaran' => null,
                ]);
            }
        }
    }
}
