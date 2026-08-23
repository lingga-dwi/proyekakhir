<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_invoices', function (Blueprint $table) {
            // MySQL needs some index left on pemesanan_id to support its
            // foreign key before the composite unique index can be dropped.
            $table->index('pemesanan_id');
        });

        Schema::table('project_invoices', function (Blueprint $table) {
            $table->dropUnique(['pemesanan_id', 'type']);
        });

        // Preserve each row's existing type before the column is dropped and
        // re-added as a plain string (avoids requiring doctrine/dbal to alter
        // an enum column, and keeps this portable across MySQL/Postgres/SQLite).
        $existingTypes = DB::table('project_invoices')->pluck('type', 'id');

        Schema::table('project_invoices', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        Schema::table('project_invoices', function (Blueprint $table) {
            $table->string('type', 20)->default('custom')->after('number');
            $table->string('name')->default('Tagihan')->after('type');
            $table->text('note')->nullable()->after('due_date');
            $table->foreignId('created_by')->nullable()->after('note')->constrained('users')->nullOnDelete();
        });

        foreach ($existingTypes as $id => $type) {
            DB::table('project_invoices')->where('id', $id)->update([
                'type' => $type,
                'name' => $type === 'dp_20' ? 'DP 20%' : 'Tagihan',
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('project_invoices', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
            $table->dropColumn(['name', 'note']);
        });

        Schema::table('project_invoices', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        Schema::table('project_invoices', function (Blueprint $table) {
            $table->enum('type', ['dp_20'])->default('dp_20')->after('number');
        });

        Schema::table('project_invoices', function (Blueprint $table) {
            $table->dropIndex(['pemesanan_id']);
            $table->unique(['pemesanan_id', 'type']);
        });
    }
};
