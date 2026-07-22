<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('status_tracking', function (Blueprint $table) {
            $table->foreignId('actor_id')->nullable()->after('id_pemesanan')->constrained('users')->nullOnDelete();
            $table->string('previous_status')->nullable()->after('actor_id');
            $table->unsignedTinyInteger('progress')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('status_tracking', function (Blueprint $table) {
            $table->dropConstrainedForeignId('actor_id');
            $table->dropColumn(['previous_status', 'progress']);
        });
    }
};
