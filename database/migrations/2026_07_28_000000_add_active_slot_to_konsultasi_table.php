<?php

use App\Models\Konsultasi;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('konsultasi', function (Blueprint $table) {
            $table->string('active_slot', 32)->nullable()->after('waktu_konsultasi');
        });

        $reserved = [];

        DB::table('konsultasi')
            ->whereIn('status', [Konsultasi::STATUS_PENDING, Konsultasi::STATUS_CONFIRMED])
            ->orderBy('id')
            ->get(['id', 'tanggal_konsultasi', 'waktu_konsultasi'])
            ->each(function (object $consultation) use (&$reserved): void {
                $slot = substr((string) $consultation->tanggal_konsultasi, 0, 10)
                    .' '.substr((string) $consultation->waktu_konsultasi, 0, 5);

                if (! isset($reserved[$slot])) {
                    DB::table('konsultasi')->where('id', $consultation->id)->update(['active_slot' => $slot]);
                    $reserved[$slot] = true;
                }
            });

        Schema::table('konsultasi', function (Blueprint $table) {
            $table->unique('active_slot', 'konsultasi_active_slot_unique');
        });
    }

    public function down(): void
    {
        Schema::table('konsultasi', function (Blueprint $table) {
            $table->dropUnique('konsultasi_active_slot_unique');
            $table->dropColumn('active_slot');
        });
    }
};
