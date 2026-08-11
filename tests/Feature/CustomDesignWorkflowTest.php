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

        $this->actingAs($admin)->put(route('admin.pemesanan.konsultasi.schedule', $consultation), [
            'tanggal_konsultasi' => now()->addDay()->toDateString(),
            'waktu_konsultasi' => '10:00',
            'designer_id' => $designer->id,
        ])->assertRedirect();

        $consultation->refresh();
        $this->assertSame(Konsultasi::STATUS_CONFIRMED, $consultation->status);
        $this->assertSame($designer->id, $consultation->designer_id);
        $this->assertNotNull($consultation->active_slot);

        $this->actingAs($admin)->put(route('admin.pemesanan.konsultasi.update', $consultation), [
            'status' => Konsultasi::STATUS_COMPLETED,
        ])->assertRedirect();

        $project = Pemesanan::firstOrFail();
        $this->assertSame('draft_design', $project->workflow_stage);
        $this->assertSame($designer->id, $project->designer_id);
        $project->update(['total_harga' => 10000000]);

        $this->actingAs($designer)->post(route('designer.proyek.document.upload', $project), [
            'document_type' => 'rab',
            'document' => UploadedFile::fake()->create('rab-awal.pdf', 100, 'application/pdf'),
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
        $this->assertSame('survey_scheduled', $project->workflow_stage);

        $this->actingAs($admin)->put(route('admin.pemesanan.survey.schedule', $project), [
            'survey_scheduled_at' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'survey_notes' => 'Pastikan pengukuran plafon dan titik listrik.',
        ])->assertRedirect();

        $project->refresh();
        $this->assertSame('final_design', $project->workflow_stage);

        $this->actingAs($designer)->post(route('designer.proyek.document.upload', $project), [
            'document_type' => 'design',
            'document' => UploadedFile::fake()->image('desain-final.jpg'),
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
    }
}
