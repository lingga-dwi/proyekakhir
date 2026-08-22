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
        $this->assertSame('Pekanbaru', $consultation->alamat);
        $this->assertSame('denah.jpg', $consultation->attachments[0]['name']);
        Storage::disk('local')->assertExists($consultation->attachments[0]['path']);

        $this->actingAs($admin)->get(route('admin.pemesanan.index'))
            ->assertOk()
            ->assertSee('Pekanbaru')
            ->assertSee('Desain Interior Baru')
            ->assertSee('Rumah Tinggal')
            ->assertSee('Rp 25 - 50 Juta')
            ->assertSee('denah.jpg')
            ->assertSee('Membutuhkan kitchen set dan ruang makan.');

        $this->actingAs($admin)->post(route('admin.pemesanan.konsultasi.accept', $consultation), [
            'designer_id' => $designer->id,
        ])->assertRedirect();
        $consultation->refresh();
        $this->assertSame($admin->id, $consultation->accepted_by);
        $this->assertNotNull($consultation->accepted_at);

        $consultation->refresh();
        $this->assertSame(Konsultasi::STATUS_CONFIRMED, $consultation->status);
        $this->assertSame($designer->id, $consultation->designer_id);
        $this->assertNull($consultation->active_slot);

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
        $this->assertSame('Desain Interior Baru', $project->jenis_proyek);
        $this->assertSame('Rumah Tinggal', $project->jenis_bangunan);

        $this->actingAs($admin)->get(route('admin.pemesanan.index'))
            ->assertOk()
            ->assertSee('Pekanbaru')
            ->assertSee('Rp 25 - 50 Juta')
            ->assertSee('denah.jpg');
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
        $this->assertSame('draft_design', $project->workflow_stage);

        $this->actingAs($designer)->post(route('designer.proyek.document.send', $project))->assertRedirect();

        $project->refresh();
        $this->assertSame('awaiting_admin_validation', $project->workflow_stage);

        $this->actingAs($admin)->post(route('admin.pemesanan.validate.send', $project), [
            'total_harga' => 10000000,
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
        $this->assertSame('revision_requested', $project->workflow_stage);

        $this->actingAs($designer)->post(route('designer.proyek.document.send', $project))->assertRedirect();

        $project->refresh();
        $this->assertSame('awaiting_admin_validation', $project->workflow_stage);

        $this->actingAs($admin)->post(route('admin.pemesanan.validate.send', $project), [
            'total_harga' => 10000000,
        ])->assertRedirect();

        $project->refresh();
        $this->assertSame('awaiting_draft_approval', $project->workflow_stage);

        $this->actingAs($admin)->postJson(route('admin.pemesanan.invoice.store', $project), [
            'name' => 'DP',
            'amount' => 2000000,
        ])->assertOk();

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
        $this->assertSame($designer->id, $project->designer_id);

        $this->actingAs($designer)->post(route('designer.proyek.document.upload', $project), [
            'document_type' => 'design',
            'document' => UploadedFile::fake()->image('desain-final.jpg'),
        ])->assertRedirect();

        $project->refresh();
        $this->assertSame('survey_scheduled', $project->workflow_stage);

        $this->actingAs($designer)->post(route('designer.proyek.document.upload', $project), [
            'document_type' => 'rab',
            'document' => UploadedFile::fake()->create('rab-final.pdf', 100, 'application/pdf'),
        ])->assertRedirect();

        $project->refresh();
        $this->assertSame('survey_scheduled', $project->workflow_stage);

        $this->actingAs($designer)->post(route('designer.proyek.document.send', $project))->assertRedirect();

        $project->refresh();
        $this->assertSame('awaiting_final_approval', $project->workflow_stage);

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
            'total_harga' => 10000000,
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
        $this->assertSame('draft_design', $project->workflow_stage);

        $this->actingAs($admin)->post(route('admin.pemesanan.document.send', $project))->assertRedirect();

        $project->refresh();
        $this->assertSame('awaiting_admin_validation', $project->workflow_stage);
        $this->assertDatabaseCount('project_documents', 2);
        $this->assertDatabaseHas('project_documents', [
            'pemesanan_id' => $project->id,
            'uploaded_by' => $admin->id,
            'document_type' => 'design',
            'submission_round' => 1,
        ]);

        $this->actingAs($admin)->post(route('admin.pemesanan.validate.send', $project), [
            'total_harga' => 10000000,
        ])->assertRedirect();

        $project->refresh();
        $this->assertSame('awaiting_draft_approval', $project->workflow_stage);
        $this->assertSame('Desain awal dan RAB tersedia', $customer->fresh()->notifications()->first()->data['title']);

        $documents = $project->documents()->orderBy('id')->get();
        $design = $documents->firstWhere('document_type', 'design');
        $rab = $documents->firstWhere('document_type', 'rab');

        $this->actingAs($customer)
            ->get(route('pemesanan.show', $project))
            ->assertOk()
            ->assertSee('desain-admin.jpg')
            ->assertSee('rab-admin.pdf')
            ->assertSee('Setujui &amp; lanjut ke DP', false)
            ->assertSee('Minta revisi');

        $this->actingAs($customer)
            ->get(route('pemesanan.document.download', [$project, $design]))
            ->assertOk();

        $this->actingAs($customer)
            ->get(route('pemesanan.document.download', [$project, $rab]))
            ->assertOk();

        $otherCustomer = User::factory()->create(['role' => 'pelanggan']);
        $this->actingAs($otherCustomer)
            ->get(route('pemesanan.document.download', [$project, $design]))
            ->assertForbidden();
    }

    public function test_admin_can_delete_a_design_document_before_it_is_sent_to_the_customer(): void
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

        $document = $project->documents()->firstOrFail();
        Storage::disk('local')->assertExists($document->path);

        $this->actingAs($admin)
            ->delete(route('admin.pemesanan.document.delete', [$project, $document]))
            ->assertRedirect();

        $this->assertDatabaseMissing('project_documents', ['id' => $document->id]);
        Storage::disk('local')->assertMissing($document->path);
    }

    public function test_deleting_a_sent_document_reverts_the_project_back_to_draft_editing(): void
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
            'total_harga' => 10000000,
            'jenis_proyek' => 'Desain interior',
            'jenis_bangunan' => 'Rumah tinggal',
        ]);

        $this->actingAs($admin)->post(route('admin.pemesanan.document.upload', $project), [
            'document_type' => 'design',
            'document' => UploadedFile::fake()->image('desain-admin.jpg'),
        ]);
        $this->actingAs($admin)->post(route('admin.pemesanan.document.upload', $project), [
            'document_type' => 'rab',
            'document' => UploadedFile::fake()->create('rab-admin.pdf', 100, 'application/pdf'),
        ]);
        $this->actingAs($admin)->post(route('admin.pemesanan.document.send', $project));
        $this->assertSame('awaiting_admin_validation', $project->fresh()->workflow_stage);

        $this->actingAs($admin)->post(route('admin.pemesanan.validate.send', $project), [
            'total_harga' => 10000000,
        ]);

        $project->refresh();
        $this->assertSame('awaiting_draft_approval', $project->workflow_stage);
        $document = $project->documents()->where('document_type', 'design')->firstOrFail();

        $this->actingAs($admin)
            ->delete(route('admin.pemesanan.document.delete', [$project, $document]))
            ->assertRedirect();

        $this->assertDatabaseMissing('project_documents', ['id' => $document->id]);
        $this->assertSame('draft_design', $project->fresh()->workflow_stage);
        $this->assertDatabaseHas('project_documents', ['pemesanan_id' => $project->id, 'document_type' => 'rab']);
    }

    public function test_document_cannot_be_deleted_once_customer_has_already_decided_on_it(): void
    {
        Storage::fake('local');

        $customer = User::factory()->create(['role' => 'pelanggan']);
        $admin = User::factory()->create(['role' => 'admin']);
        $project = Pemesanan::create([
            'id_user' => $customer->id,
            'tanggal_pesan' => now()->toDateString(),
            'status_pemesanan' => Pemesanan::STATUS_CONFIRMED,
            'workflow_stage' => 'survey_scheduled',
            'progress' => 25,
            'total_harga' => 10000000,
            'jenis_proyek' => 'Desain interior',
            'jenis_bangunan' => 'Rumah tinggal',
        ]);

        $document = $project->documents()->create([
            'uploaded_by' => $admin->id,
            'stage' => 'draft',
            'document_type' => 'design',
            'submission_round' => 1,
            'path' => 'project-documents/'.$project->id.'/draft/desain.jpg',
            'original_name' => 'desain-final.jpg',
            'version' => 1,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.pemesanan.document.delete', [$project, $document]))
            ->assertStatus(422);

        $this->assertDatabaseHas('project_documents', ['id' => $document->id]);
    }

    public function test_designer_cannot_delete_a_project_document(): void
    {
        Storage::fake('local');

        $customer = User::factory()->create(['role' => 'pelanggan']);
        $admin = User::factory()->create(['role' => 'admin']);
        $designer = User::factory()->create(['role' => 'designer']);
        $project = Pemesanan::create([
            'id_user' => $customer->id,
            'designer_id' => $designer->id,
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
        ]);
        $document = $project->documents()->firstOrFail();

        $this->actingAs($designer)
            ->delete(route('admin.pemesanan.document.delete', [$project, $document]))
            ->assertForbidden();
    }

    public function test_document_upload_and_delete_return_json_for_ajax_requests(): void
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
            'total_harga' => 10000000,
            'jenis_proyek' => 'Desain interior',
            'jenis_bangunan' => 'Rumah tinggal',
        ]);

        $uploadResponse = $this->actingAs($admin)
            ->postJson(route('admin.pemesanan.document.upload', $project), [
                'document_type' => 'design',
                'document' => UploadedFile::fake()->image('desain-admin.jpg'),
            ]);

        $uploadResponse->assertOk()->assertJson([
            'success' => true,
            'documentType' => 'design',
            'canManageDocuments' => true,
            'isAwaitingDecision' => false,
            'canSend' => false,
        ]);
        $uploadResponse->assertJsonStructure(['document' => ['id', 'name', 'size', 'downloadUrl', 'deleteUrl']]);
        $document = $project->documents()->firstOrFail();
        $this->assertSame($document->id, $uploadResponse->json('document.id'));

        $rabResponse = $this->actingAs($admin)
            ->postJson(route('admin.pemesanan.document.upload', $project), [
                'document_type' => 'rab',
                'document' => UploadedFile::fake()->create('rab-admin.pdf', 100, 'application/pdf'),
            ]);
        $rabResponse->assertOk()->assertJson([
            'success' => true,
            'documentType' => 'rab',
            'canManageDocuments' => true,
            'isAwaitingDecision' => false,
            'canSend' => true,
        ]);
        $this->assertSame('draft_design', $project->fresh()->workflow_stage);

        $sendResponse = $this->actingAs($admin)->postJson(route('admin.pemesanan.document.send', $project));
        $sendResponse->assertOk()->assertJson([
            'success' => true,
            'canManageDocuments' => false,
            'isAwaitingDecision' => true,
        ]);
        $this->assertSame('awaiting_admin_validation', $project->fresh()->workflow_stage);

        $this->actingAs($admin)->postJson(route('admin.pemesanan.validate.send', $project), [
            'total_harga' => 10000000,
        ])->assertOk();
        $this->assertSame('awaiting_draft_approval', $project->fresh()->workflow_stage);

        $deleteResponse = $this->actingAs($admin)
            ->deleteJson(route('admin.pemesanan.document.delete', [$project, $document]));
        $deleteResponse->assertOk()->assertJson([
            'success' => true,
            'documentType' => 'design',
            'document' => null,
            'canManageDocuments' => true,
            'isAwaitingDecision' => false,
        ]);
        $this->assertSame('draft_design', $project->fresh()->workflow_stage);
    }

    public function test_designer_can_upload_and_delete_documents_via_ajax_on_own_project(): void
    {
        Storage::fake('local');

        $customer = User::factory()->create(['role' => 'pelanggan']);
        $designer = User::factory()->create(['role' => 'designer']);
        $otherDesigner = User::factory()->create(['role' => 'designer']);
        $project = Pemesanan::create([
            'id_user' => $customer->id,
            'designer_id' => $designer->id,
            'tanggal_pesan' => now()->toDateString(),
            'status_pemesanan' => Pemesanan::STATUS_CONFIRMED,
            'workflow_stage' => 'draft_design',
            'progress' => 10,
            'jenis_proyek' => 'Desain interior',
            'jenis_bangunan' => 'Rumah tinggal',
        ]);

        $uploadResponse = $this->actingAs($designer)
            ->postJson(route('designer.proyek.document.upload', $project), [
                'document_type' => 'design',
                'document' => UploadedFile::fake()->image('desain-desainer.jpg'),
            ]);

        $uploadResponse->assertOk()->assertJson(['success' => true, 'documentType' => 'design']);
        $document = $project->documents()->firstOrFail();
        $this->assertSame(
            route('designer.proyek.document.delete', [$project, $document]),
            $uploadResponse->json('document.deleteUrl')
        );

        $this->actingAs($otherDesigner)
            ->deleteJson(route('designer.proyek.document.delete', [$project, $document]))
            ->assertForbidden();

        $this->actingAs($designer)
            ->deleteJson(route('designer.proyek.document.delete', [$project, $document]))
            ->assertOk()
            ->assertJson(['success' => true, 'document' => null]);

        $this->assertDatabaseMissing('project_documents', ['id' => $document->id]);
    }

    public function test_sending_documents_fails_when_rab_is_missing(): void
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
            'total_harga' => 10000000,
            'jenis_proyek' => 'Desain interior',
            'jenis_bangunan' => 'Rumah tinggal',
        ]);

        $this->actingAs($admin)->post(route('admin.pemesanan.document.upload', $project), [
            'document_type' => 'design',
            'document' => UploadedFile::fake()->image('desain-admin.jpg'),
        ]);

        $response = $this->actingAs($admin)->postJson(route('admin.pemesanan.document.send', $project));

        $response->assertStatus(422)->assertJson(['success' => false]);
        $this->assertSame('draft_design', $project->fresh()->workflow_stage);
    }

    public function test_sending_draft_documents_succeeds_without_a_price_but_admin_validation_requires_one(): void
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
        ]);
        $this->actingAs($admin)->post(route('admin.pemesanan.document.upload', $project), [
            'document_type' => 'rab',
            'document' => UploadedFile::fake()->create('rab-admin.pdf', 100, 'application/pdf'),
        ]);

        // The designer/admin must be able to send the draft RAB for admin validation before
        // a price is set, since the draft RAB is what informs the admin's price decision.
        $this->actingAs($admin)->postJson(route('admin.pemesanan.document.send', $project))
            ->assertOk()->assertJson(['success' => true]);
        $this->assertSame('awaiting_admin_validation', $project->fresh()->workflow_stage);

        // Admin cannot validate & send to the customer without setting a price.
        $this->actingAs($admin)->post(route('admin.pemesanan.validate.send', $project))
            ->assertSessionHasErrors('total_harga');
        $this->assertSame('awaiting_admin_validation', $project->fresh()->workflow_stage);

        $this->actingAs($admin)->post(route('admin.pemesanan.validate.send', $project), [
            'total_harga' => 5000000,
        ])->assertRedirect();

        $this->assertSame('awaiting_draft_approval', $project->fresh()->workflow_stage);

        // Validating no longer locks in a fixed 20% invoice — billing is fully flexible
        // and left to the admin to create via "Buat Tagihan" whenever they choose.
        $this->assertDatabaseCount('project_invoices', 0);

        $this->actingAs($customer)->postJson(route('pemesanan.document.decision', $project), [
            'stage' => 'draft',
            'decision' => 'approved',
        ])->assertOk()->assertJson(['success' => true]);
        $this->assertNotSame('awaiting_draft_approval', $project->fresh()->workflow_stage);
    }

    public function test_admin_can_request_revision_during_validation(): void
    {
        Storage::fake('local');

        $customer = User::factory()->create(['role' => 'pelanggan']);
        $admin = User::factory()->create(['role' => 'admin']);
        $designer = User::factory()->create(['role' => 'designer']);
        $project = Pemesanan::create([
            'id_user' => $customer->id,
            'designer_id' => $designer->id,
            'tanggal_pesan' => now()->toDateString(),
            'status_pemesanan' => Pemesanan::STATUS_CONFIRMED,
            'workflow_stage' => 'awaiting_admin_validation',
            'progress' => 15,
            'draft_round' => 1,
            'jenis_proyek' => 'Desain interior',
            'jenis_bangunan' => 'Rumah tinggal',
        ]);

        $this->actingAs($admin)->post(route('admin.pemesanan.validate.revision', $project), [
            'feedback' => 'Ukuran kabinet tidak sesuai permintaan pelanggan.',
        ])->assertRedirect();

        $project->refresh();
        $this->assertSame('revision_requested', $project->workflow_stage);
        $this->assertSame(2, $project->draft_round);
        $this->assertSame(
            'Admin meminta revisi',
            $designer->fresh()->notifications()->first()->data['title']
        );
    }

    public function test_designer_can_send_draft_documents_directly_from_konsultasi_stage(): void
    {
        Storage::fake('local');

        $customer = User::factory()->create(['role' => 'pelanggan']);
        $designer = User::factory()->create(['role' => 'designer']);
        $project = Pemesanan::create([
            'id_user' => $customer->id,
            'designer_id' => $designer->id,
            'tanggal_pesan' => now()->toDateString(),
            'status_pemesanan' => Pemesanan::STATUS_CONFIRMED,
            'workflow_stage' => 'konsultasi',
            'progress' => 10,
            'jenis_proyek' => 'Desain interior',
            'jenis_bangunan' => 'Rumah tinggal',
        ]);

        $this->actingAs($designer)->post(route('designer.proyek.document.upload', $project), [
            'document_type' => 'design',
            'document' => UploadedFile::fake()->image('desain-awal.jpg'),
        ])->assertRedirect();
        $this->actingAs($designer)->post(route('designer.proyek.document.upload', $project), [
            'document_type' => 'rab',
            'document' => UploadedFile::fake()->create('rab-awal.pdf', 100, 'application/pdf'),
        ])->assertRedirect();

        $this->assertSame('konsultasi', $project->fresh()->workflow_stage);

        // The designer must be able to send the draft desain & RAB straight from the
        // Konsultasi stage, without a separate "complete consultation" step first.
        $this->actingAs($designer)->postJson(route('designer.proyek.document.send', $project))
            ->assertOk()->assertJson(['success' => true]);

        $this->assertSame('awaiting_admin_validation', $project->fresh()->workflow_stage);
        $this->assertDatabaseHas('project_documents', [
            'pemesanan_id' => $project->id,
            'document_type' => 'design',
            'stage' => 'draft',
        ]);
    }

    public function test_admin_can_create_a_flexible_invoice_for_a_project(): void
    {
        $customer = User::factory()->create(['role' => 'pelanggan']);
        $admin = User::factory()->create(['role' => 'admin']);
        $project = Pemesanan::create([
            'id_user' => $customer->id,
            'tanggal_pesan' => now()->toDateString(),
            'status_pemesanan' => Pemesanan::STATUS_CONFIRMED,
            'workflow_stage' => 'approved',
            'total_harga' => 50000000,
            'jenis_proyek' => 'Desain interior',
        ]);

        $response = $this->actingAs($admin)->postJson(route('admin.pemesanan.invoice.store', $project), [
            'name' => 'Termin 2 - Pemasangan',
            'amount' => 20000000,
            'due_date' => now()->addDays(10)->toDateString(),
            'note' => 'Dibayar setelah pemasangan kabinet selesai.',
        ]);

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseHas('project_invoices', [
            'pemesanan_id' => $project->id,
            'type' => 'custom',
            'name' => 'Termin 2 - Pemasangan',
            'amount' => 20000000,
            'status' => 'pending',
            'created_by' => $admin->id,
        ]);
        $this->assertSame(
            'Tagihan baru tersedia',
            $customer->fresh()->notifications()->first()->data['title']
        );
    }

    public function test_admin_can_create_invoice_beyond_project_value_for_additional_charges(): void
    {
        $customer = User::factory()->create(['role' => 'pelanggan']);
        $admin = User::factory()->create(['role' => 'admin']);
        $project = Pemesanan::create([
            'id_user' => $customer->id,
            'tanggal_pesan' => now()->toDateString(),
            'status_pemesanan' => Pemesanan::STATUS_CONFIRMED,
            'workflow_stage' => 'approved',
            'total_harga' => 50000000,
            'jenis_proyek' => 'Desain interior',
        ]);
        $project->invoices()->create([
            'number' => 'DP-2026-00001',
            'type' => 'dp_20',
            'name' => 'DP 20%',
            'amount' => 10000000,
            'status' => 'paid',
        ]);

        // Amounts that exceed the remaining project value are allowed, to cover
        // additional charges (biaya tambahan) beyond the original quote.
        $this->actingAs($admin)->postJson(route('admin.pemesanan.invoice.store', $project), [
            'name' => 'Biaya Tambahan',
            'amount' => 45000000,
        ])->assertOk()->assertJson(['success' => true]);

        $this->assertDatabaseHas('project_invoices', ['name' => 'Biaya Tambahan', 'amount' => 45000000]);
    }

    public function test_admin_can_mark_a_custom_invoice_as_paid(): void
    {
        $customer = User::factory()->create(['role' => 'pelanggan']);
        $admin = User::factory()->create(['role' => 'admin']);
        $project = Pemesanan::create([
            'id_user' => $customer->id,
            'tanggal_pesan' => now()->toDateString(),
            'status_pemesanan' => Pemesanan::STATUS_CONFIRMED,
            'workflow_stage' => 'approved',
            'total_harga' => 50000000,
            'jenis_proyek' => 'Desain interior',
        ]);
        $invoice = $project->invoices()->create([
            'number' => 'INV-2026-0001-'.$project->id,
            'type' => 'custom',
            'name' => 'Termin 2',
            'amount' => 15000000,
            'status' => 'pending',
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)->postJson(route('admin.pemesanan.invoice.paid', [$project, $invoice]))
            ->assertOk()->assertJson(['success' => true]);

        $this->assertSame('paid', $invoice->fresh()->status);
        $this->assertSame($admin->id, $invoice->fresh()->verified_by);
    }

    public function test_marking_the_dp_invoice_paid_advances_the_project_to_survey(): void
    {
        $customer = User::factory()->create(['role' => 'pelanggan']);
        $admin = User::factory()->create(['role' => 'admin']);
        $project = Pemesanan::create([
            'id_user' => $customer->id,
            'tanggal_pesan' => now()->toDateString(),
            'status_pemesanan' => Pemesanan::STATUS_CONFIRMED,
            'workflow_stage' => 'dp_verification',
            'progress' => 25,
            'total_harga' => 10000000,
            'jenis_proyek' => 'Desain interior',
        ]);
        // The DP invoice is whichever one was created first, regardless of
        // its type column — mirrors the real "Tandai Lunas" button used
        // instead of the dedicated verifyDp() flow.
        $invoice = $project->invoices()->create([
            'number' => 'INV-2026-0001-'.$project->id,
            'type' => 'custom',
            'name' => 'DP 20%',
            'amount' => 2000000,
            'status' => 'submitted',
        ]);

        $this->actingAs($admin)->postJson(route('admin.pemesanan.invoice.paid', [$project, $invoice]))
            ->assertOk()->assertJson(['success' => true]);

        $invoice->refresh();
        $project->refresh();
        $this->assertSame('paid', $invoice->status);
        $this->assertSame($admin->id, $invoice->verified_by);
        $this->assertSame('survey_scheduled', $project->workflow_stage);
    }

    public function test_customer_can_upload_evidence_for_any_unpaid_invoice(): void
    {
        Storage::fake('payment_evidence');

        $customer = User::factory()->create(['role' => 'pelanggan']);
        $admin = User::factory()->create(['role' => 'admin']);
        $project = Pemesanan::create([
            'id_user' => $customer->id,
            'tanggal_pesan' => now()->toDateString(),
            'status_pemesanan' => Pemesanan::STATUS_CONFIRMED,
            'workflow_stage' => 'approved',
            'total_harga' => 50000000,
            'jenis_proyek' => 'Desain interior',
        ]);
        $firstInvoice = $project->invoices()->create([
            'number' => 'DP-2026-00001',
            'type' => 'custom',
            'name' => 'DP',
            'amount' => 10000000,
            'status' => 'paid',
            'created_at' => now()->subDay(),
        ]);
        $secondInvoice = $project->invoices()->create([
            'number' => 'INV-2026-0001-'.$project->id,
            'type' => 'custom',
            'name' => 'Biaya Tambahan',
            'amount' => 5000000,
            'status' => 'pending',
            'created_by' => $admin->id,
        ]);

        // Cannot upload proof against an invoice that's already paid.
        $this->actingAs($customer)
            ->postJson(route('pemesanan.invoice-evidence.upload', [$project, $firstInvoice]), [
                'bukti_pembayaran' => UploadedFile::fake()->image('bukti.jpg'),
            ])
            ->assertStatus(422);

        $this->actingAs($customer)
            ->postJson(route('pemesanan.invoice-evidence.upload', [$project, $secondInvoice]), [
                'bukti_pembayaran' => UploadedFile::fake()->image('bukti.jpg'),
            ])
            ->assertOk()->assertJson(['success' => true]);

        $secondInvoice->refresh();
        $this->assertSame('submitted', $secondInvoice->status);
        $this->assertNotNull($secondInvoice->proof_path);
        // Only the DP invoice (the first one created) drives the workflow stage;
        // uploading proof for a later invoice doesn't touch it.
        $this->assertSame('approved', $project->fresh()->workflow_stage);
    }

    public function test_uploading_evidence_for_a_custom_typed_dp_invoice_still_transitions_to_dp_verification(): void
    {
        Storage::fake('payment_evidence');

        $customer = User::factory()->create(['role' => 'pelanggan']);
        $project = Pemesanan::create([
            'id_user' => $customer->id,
            'tanggal_pesan' => now()->toDateString(),
            'status_pemesanan' => Pemesanan::STATUS_CONFIRMED,
            'workflow_stage' => 'awaiting_dp',
            'progress' => 25,
            'total_harga' => 10000000,
            'jenis_proyek' => 'Desain interior',
        ]);

        // The "DP invoice" is whichever invoice was created first for the
        // project, regardless of its type column — admins can create it as
        // 'custom' rather than 'dp_20'. Uploading proof for it must still
        // move the project into dp_verification.
        $invoice = $project->invoices()->create([
            'number' => 'INV-2026-0001-'.$project->id,
            'type' => 'custom',
            'name' => 'Tagihan',
            'amount' => 2000000,
            'status' => 'pending',
        ]);

        $this->actingAs($customer)
            ->postJson(route('pemesanan.invoice-evidence.upload', [$project, $invoice]), [
                'bukti_pembayaran' => UploadedFile::fake()->image('bukti.jpg'),
            ])
            ->assertOk()->assertJson(['success' => true]);

        $invoice->refresh();
        $this->assertSame('submitted', $invoice->status);
        $this->assertSame('dp_verification', $project->fresh()->workflow_stage);
    }
}
