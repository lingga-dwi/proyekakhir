<?php

namespace Tests\Feature;

use App\Models\Konsultasi;
use App\Models\Pemesanan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CustomDesignWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_custom_design_request_follows_consultation_to_final_approval_workflow(): void
    {
        Storage::fake('local');
        Storage::fake('payment_evidence');

        $customer = User::factory()->create(['role' => 'pelanggan']);
        $admin = User::factory()->create(['role' => 'admin']);
        $designer = User::factory()->create(['role' => 'designer']);

        $this->actingAs($customer)->post(route('konsultasi.store'), [
            'nama' => $customer->nama,
            'email' => $customer->email,
            'no_telp' => '081234567890',
            'alamat' => 'Pekanbaru',
            'jenis_konsultasi' => 'free_consultation',
            'jenis_ruangan' => 'living_room',
            'budget_range' => '25m_50m',
            'luas_ruangan' => 48,
            'deskripsi_kebutuhan' => 'Membutuhkan kitchen set dan ruang makan.',
            'attachments' => [UploadedFile::fake()->image('denah.jpg')],
        ])->assertRedirect();

        $consultation = Konsultasi::firstOrFail();
        $this->assertNotEmpty($consultation->attachments);

        $this->actingAs($admin)->post(route('admin.pemesanan.konsultasi.accept', $consultation))->assertRedirect();
        $consultation->refresh();
        $this->assertSame($admin->id, $consultation->accepted_by);
        $this->assertNotNull($consultation->accepted_at);

        $this->actingAs($admin)->put(route('admin.pemesanan.konsultasi.schedule', $consultation), [
            'tanggal_konsultasi' => now()->addDay()->toDateString(),
            'waktu_konsultasi' => '10:00',
            'designer_id' => $designer->id,
        ])->assertRedirect();

        $consultation->refresh();
        $this->assertSame(Konsultasi::STATUS_CONFIRMED, $consultation->status);
        $this->assertSame($designer->id, $consultation->designer_id);
        $this->assertNotNull($consultation->active_slot);

        $this->actingAs($designer)->post(route('designer.konsultasi.complete', $consultation), [
            'consultation_result' => 'Ukuran dan kebutuhan pelanggan telah dikonfirmasi. Lanjutkan desain awal dan RAB.',
        ])->assertRedirect();

        $consultation->refresh();
        $this->assertSame(Konsultasi::STATUS_COMPLETED, $consultation->status);
        $this->assertNotNull($consultation->consulted_at);
        $this->assertNotEmpty($consultation->consultation_result);

        $project = Pemesanan::firstOrFail();
        $this->assertSame('draft_design', $project->workflow_stage);
        $this->assertSame($designer->id, $project->designer_id);
        $project->update(['total_harga' => 10000000]);

        $this->actingAs($designer)->post(route('designer.proyek.document.upload', $project), [
            'document_type' => 'design',
            'document' => UploadedFile::fake()->image('desain-awal.jpg'),
        ])->assertRedirect();

        $project->refresh();
        $this->assertSame('draft_design', $project->workflow_stage);

        $this->actingAs($designer)->post(route('designer.proyek.document.upload', $project), [
            'document_type' => 'rab',
            'document' => UploadedFile::fake()->create('rab-awal.pdf', 100, 'application/pdf'),
        ])->assertRedirect();

        $project->refresh();
        $this->assertSame('awaiting_draft_approval', $project->workflow_stage);

        $this->actingAs($customer)->post(route('pemesanan.document.decision', $project), [
            'stage' => 'draft',
            'decision' => 'revision_requested',
            'feedback' => 'Sesuaikan posisi kabinet dan rincian biaya material.',
        ])->assertRedirect();

        $project->refresh();
        $this->assertSame('revision_requested', $project->workflow_stage);
        $this->assertSame(2, $project->draft_round);

        $this->actingAs($designer)->post(route('designer.proyek.document.upload', $project), [
            'document_type' => 'design',
            'document' => UploadedFile::fake()->image('desain-awal-revisi.jpg'),
        ])->assertRedirect();

        $project->refresh();
        $this->assertSame('revision_requested', $project->workflow_stage);

        $this->actingAs($designer)->post(route('designer.proyek.document.upload', $project), [
            'document_type' => 'rab',
            'document' => UploadedFile::fake()->create('rab-awal-revisi.pdf', 100, 'application/pdf'),
        ])->assertRedirect();

        $project->refresh();
        $this->assertSame('awaiting_draft_approval', $project->workflow_stage);

        $this->actingAs($customer)->post(route('pemesanan.document.decision', $project), [
            'stage' => 'draft',
            'decision' => 'approved',
        ])->assertRedirect();

        $project->refresh();
        $this->assertSame('awaiting_dp', $project->workflow_stage);
        $this->assertDatabaseHas('project_invoices', ['pemesanan_id' => $project->id, 'amount' => 2000000]);

        $this->actingAs($customer)->post(route('pemesanan.dp-evidence.upload', $project), [
            'bukti_pembayaran' => UploadedFile::fake()->image('dp.jpg'),
        ])->assertRedirect();
        $this->actingAs($admin)->post(route('admin.pemesanan.dp.verify', $project))->assertRedirect();

        $project->refresh();
        $this->assertSame('survey_pending', $project->workflow_stage);

        $this->actingAs($admin)->put(route('admin.pemesanan.survey.schedule', $project), [
            'designer_id' => $designer->id,
            'survey_scheduled_at' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'survey_notes' => 'Pastikan pengukuran plafon dan titik listrik.',
        ])->assertRedirect();

        $project->refresh();
        $this->assertSame('survey_scheduled', $project->workflow_stage);

        $this->actingAs($designer)->post(route('designer.proyek.survey.complete', $project), [
            'survey_result' => 'Ukuran aktual, kondisi plafon, dan titik listrik telah didokumentasikan.',
            'survey_document' => UploadedFile::fake()->image('hasil-survei.jpg'),
        ])->assertRedirect();

        $project->refresh();
        $this->assertSame('final_design', $project->workflow_stage);
        $this->assertNotNull($project->survey_completed_at);
        $this->assertNotEmpty($project->survey_result);

        $this->actingAs($designer)->post(route('designer.proyek.document.upload', $project), [
            'document_type' => 'design',
            'document' => UploadedFile::fake()->image('desain-final.jpg'),
        ])->assertRedirect();

        $project->refresh();
        $this->assertSame('final_design', $project->workflow_stage);

        $this->actingAs($designer)->post(route('designer.proyek.document.upload', $project), [
            'document_type' => 'rab',
            'document' => UploadedFile::fake()->create('rab-final.pdf', 100, 'application/pdf'),
        ])->assertRedirect();

        $this->actingAs($customer)->post(route('pemesanan.document.decision', $project), [
            'stage' => 'final',
            'decision' => 'approved',
        ])->assertRedirect();

        $this->assertDatabaseHas('pemesanan', [
            'id' => $project->id,
            'workflow_stage' => 'approved',
            'status_pemesanan' => Pemesanan::STATUS_IN_PROGRESS,
        ]);
        $this->assertDatabaseHas('project_document_decisions', [
            'pemesanan_id' => $project->id,
            'stage' => 'draft',
            'submission_round' => 1,
            'decision' => 'revision_requested',
            'feedback' => 'Sesuaikan posisi kabinet dan rincian biaya material.',
        ]);
        $this->assertDatabaseHas('project_document_decisions', [
            'pemesanan_id' => $project->id,
            'stage' => 'draft',
            'submission_round' => 2,
            'decision' => 'approved',
        ]);
        $this->assertDatabaseHas('project_document_decisions', [
            'pemesanan_id' => $project->id,
            'stage' => 'final',
            'submission_round' => 1,
            'decision' => 'approved',
        ]);
    }

    public function test_admin_can_upload_design_and_rab_for_customer_review(): void
    {
        Storage::fake('local');

        $customer = User::factory()->create(['role' => 'pelanggan']);
        $admin = User::factory()->create(['role' => 'admin']);
        $project = Pemesanan::create([
            'id_user' => $customer->id,
            'tanggal_pesan' => now()->toDateString(),
            'status_pemesanan' => Pemesanan::STATUS_CONFIRMED,
            'workflow_stage' => 'draft_design',
            'progress' => 10,
            'jenis_proyek' => 'Desain interior',
            'jenis_bangunan' => 'Rumah tinggal',
        ]);

        $this->actingAs($admin)->post(route('admin.pemesanan.document.upload', $project), [
            'document_type' => 'design',
            'document' => UploadedFile::fake()->image('desain-admin.jpg'),
        ])->assertRedirect();

        $project->refresh();
        $this->assertSame('draft_design', $project->workflow_stage);

        $this->actingAs($admin)->post(route('admin.pemesanan.document.upload', $project), [
            'document_type' => 'rab',
            'document' => UploadedFile::fake()->create('rab-admin.pdf', 100, 'application/pdf'),
        ])->assertRedirect();

        $project->refresh();
        $this->assertSame('awaiting_draft_approval', $project->workflow_stage);
        $this->assertDatabaseCount('project_documents', 2);
        $this->assertDatabaseHas('project_documents', [
            'pemesanan_id' => $project->id,
            'uploaded_by' => $admin->id,
            'document_type' => 'design',
            'submission_round' => 1,
        ]);
        $this->assertSame('Desain awal dan RAB tersedia', $customer->fresh()->notifications()->first()->data['title']);
    }
}
