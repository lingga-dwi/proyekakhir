<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('pemesanan', 'id_rfq')) {
            Schema::table('pemesanan', function (Blueprint $table) {
                $table->dropForeign(['id_rfq']);
            });

            Schema::table('pemesanan', function (Blueprint $table) {
                $table->dropColumn('id_rfq');
            });
        }

        Schema::dropIfExists('pembayaran');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('rfq');
    }

    public function down(): void
    {
        Schema::create('rfq', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_user')->constrained('users');
            $table->foreignId('id_katalog')->constrained('katalog');
            $table->date('tanggal_pengajuan');
            $table->text('kebutuhan_proyek');
            $table->enum('status_rfq', ['pending', 'disetujui', 'ditolak'])->default('pending');
            $table->timestamps();
        });

        Schema::table('pemesanan', function (Blueprint $table) {
            $table->foreignId('id_rfq')
                ->nullable()
                ->constrained('rfq')
                ->nullOnDelete();
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_pemesanan')->unique()->constrained('pemesanan');
            $table->decimal('total_tagihan', 12, 2);
            $table->enum('status_invoice', ['pending', 'dibayar', 'overdue'])->default('pending');
            $table->date('tanggal_jatuh_tempo');
            $table->timestamps();
        });

        Schema::create('pembayaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_invoice')->constrained('invoices');
            $table->decimal('jumlah_bayar', 12, 2);
            $table->date('tanggal_bayar');
            $table->enum('metode_bayar', ['transfer_bank', 'tunai', 'kartu_kredit'])->default('transfer_bank');
            $table->string('bukti_pembayaran')->nullable();
            $table->timestamps();
        });
    }
};
