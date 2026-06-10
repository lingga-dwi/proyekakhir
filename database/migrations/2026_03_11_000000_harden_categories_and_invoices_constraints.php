<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('categories')
            ->select('slug')
            ->groupBy('slug')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('slug')
            ->each(function (string $slug): void {
                $duplicates = DB::table('categories')
                    ->where('slug', $slug)
                    ->orderBy('id')
                    ->get(['id']);

                $duplicates->slice(1)->each(function ($category) use ($slug): void {
                    DB::table('categories')
                        ->where('id', $category->id)
                        ->update(['slug' => $slug . '-' . $category->id]);
                });
            });

        DB::table('invoices')
            ->select('id_pemesanan')
            ->groupBy('id_pemesanan')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('id_pemesanan')
            ->each(function (int $pemesananId): void {
                $invoices = DB::table('invoices')
                    ->where('id_pemesanan', $pemesananId)
                    ->orderBy('id')
                    ->get(['id']);

                $keepInvoiceId = $invoices->first()->id;
                $duplicateIds = $invoices->slice(1)->pluck('id');

                if ($duplicateIds->isEmpty()) {
                    return;
                }

                DB::table('pembayaran')
                    ->whereIn('id_invoice', $duplicateIds)
                    ->update(['id_invoice' => $keepInvoiceId]);

                DB::table('invoices')
                    ->whereIn('id', $duplicateIds)
                    ->delete();
            });

        Schema::table('categories', function (Blueprint $table) {
            $table->unique('slug');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->unique('id_pemesanan');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique(['slug']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropUnique(['id_pemesanan']);
        });
    }
};
