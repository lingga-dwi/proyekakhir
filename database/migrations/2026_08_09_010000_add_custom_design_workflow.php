<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('konsultasi', function (Blueprint $table) {
            $table->foreignId('designer_id')->nullable()->after('pemesanan_id')->constrained('users')->nullOnDelete();
            $table->foreignId('scheduled_by')->nullable()->after('designer_id')->constrained('users')->nullOnDelete();
            $table->string('active_slot', 32)->nullable()->unique()->after('waktu_konsultasi');
            $table->json('attachments')->nullable()->after('deskripsi_kebutuhan');
        });

        Schema::table('pemesanan', function (Blueprint $table) {
            $table->string('workflow_stage', 40)->default('draft_design')->after('status_pemesanan');
            $table->dateTime('survey_scheduled_at')->nullable()->after('target_selesai');
            $table->text('survey_notes')->nullable()->after('survey_scheduled_at');
        });

        Schema::create('project_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pemesanan_id')->constrained('pemesanan')->cascadeOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->enum('stage', ['draft', 'final']);
            $table->enum('document_type', ['design', 'rab', 'survey']);
            $table->string('path');
            $table->string('original_name');
            $table->unsignedSmallInteger('version')->default(1);
            $table->timestamps();
            $table->index(['pemesanan_id', 'stage']);
        });

        Schema::create('project_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pemesanan_id')->constrained('pemesanan')->cascadeOnDelete();
            $table->string('number')->unique();
            $table->enum('type', ['dp_20'])->default('dp_20');
            $table->decimal('amount', 12, 2);
            $table->enum('status', ['pending', 'submitted', 'paid'])->default('pending');
            $table->date('due_date')->nullable();
            $table->string('proof_path')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            $table->unique(['pemesanan_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_invoices');
        Schema::dropIfExists('project_documents');

        Schema::table('pemesanan', function (Blueprint $table) {
            $table->dropColumn(['workflow_stage', 'survey_scheduled_at', 'survey_notes']);
        });

        Schema::table('konsultasi', function (Blueprint $table) {
            $table->dropUnique(['active_slot']);
            $table->dropConstrainedForeignId('designer_id');
            $table->dropConstrainedForeignId('scheduled_by');
            $table->dropColumn(['active_slot', 'attachments']);
        });
    }
};
