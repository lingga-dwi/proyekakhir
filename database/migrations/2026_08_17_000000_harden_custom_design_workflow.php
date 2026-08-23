<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('konsultasi', function (Blueprint $table) {
            $table->text('consultation_result')->nullable()->after('catatan_admin');
            $table->timestamp('consulted_at')->nullable()->after('consultation_result');
        });

        Schema::table('pemesanan', function (Blueprint $table) {
            $table->unsignedSmallInteger('draft_round')->default(1)->after('workflow_stage');
            $table->unsignedSmallInteger('final_round')->default(1)->after('draft_round');
            $table->text('survey_result')->nullable()->after('survey_notes');
            $table->timestamp('survey_completed_at')->nullable()->after('survey_result');
        });

        Schema::table('project_documents', function (Blueprint $table) {
            $table->unsignedSmallInteger('submission_round')->default(1)->after('document_type');
            $table->index(
                ['pemesanan_id', 'stage', 'submission_round', 'document_type'],
                'project_documents_submission_lookup'
            );
        });

        Schema::create('project_document_decisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pemesanan_id')->constrained('pemesanan')->cascadeOnDelete();
            $table->foreignId('decided_by')->constrained('users')->cascadeOnDelete();
            $table->enum('stage', ['draft', 'final']);
            $table->unsignedSmallInteger('submission_round');
            $table->enum('decision', ['approved', 'revision_requested']);
            $table->text('feedback')->nullable();
            $table->timestamps();
            $table->index(['pemesanan_id', 'stage', 'submission_round'], 'project_decisions_stage_round');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_document_decisions');

        Schema::table('project_documents', function (Blueprint $table) {
            $table->dropIndex('project_documents_submission_lookup');
            $table->dropColumn('submission_round');
        });

        Schema::table('pemesanan', function (Blueprint $table) {
            $table->dropColumn(['draft_round', 'final_round', 'survey_result', 'survey_completed_at']);
        });

        Schema::table('konsultasi', function (Blueprint $table) {
            $table->dropColumn(['consultation_result', 'consulted_at']);
        });
    }
};
