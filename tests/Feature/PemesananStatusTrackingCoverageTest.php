<?php

namespace Tests\Feature;

use App\Models\Pemesanan;
use App\Models\ProjectDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PemesananStatusTrackingCoverageTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_workflow_transition_writes_a_status_tracking_entry(): void
    {
        Storage::fake('local');
        Storage::fake('payment_evidence');

        $customer = User::factory()->create(['role' => 'pelanggan']);
        $admin = User::factory()->create(['role' => 'admin']);
        $designer = User::factory()->create(['role' => 'designer']);

        $project = Pemesanan::create([
            'id_user' => $customer->id,
            'designer_id' => $designer->id,
            'tanggal_pesan' => now()->toDateString(),
            'status_pemesanan' => Pemesanan::STATUS_CONFIRMED,
            'workflow_stage' => 'draft_design',
            'progress' => 5,
            'total_harga' => 0,
            'jenis_proyek' => 'Simulasi',
            'jenis_bangunan' => 'Rumah tinggal',
        ]);

        $expectCount = fn (int $n, string $label) => $this->assertSame(
            $n, $project->statusTrackings()->count(), "Expected {$n} tracking rows after: {$label}"
        );

        $expectCount(0, 'initial');

        // 1. Designer uploads design + RAB, then sends them.
        ProjectDocument::create([
            'pemesanan_id' => $project->id, 'uploaded_by' => $designer->id,
            'stage' => 'draft', 'document_type' => 'design', 'submission_round' => 1,
            'path' => 'x', 'original_name' => 'design.pdf', 'version' => 1,
        ]);
        ProjectDocument::create([
            'pemesanan_id' => $project->id, 'uploaded_by' => $designer->id,
            'stage' => 'draft', 'document_type' => 'rab', 'submission_round' => 1,
            'path' => 'x', 'original_name' => 'rab.pdf', 'version' => 1,
        ]);
        $this->actingAs($designer)->post(route('designer.proyek.document.send', $project))->assertRedirect();
        $expectCount(1, 'sendDocuments (draft)');
        $this->assertSame('awaiting_admin_validation', $project->fresh()->workflow_stage);

        // 2. Admin validates and sends the offer.
        $this->actingAs($admin)->post(route('admin.pemesanan.validate.send', $project), [
            'total_harga' => 10000000,
        ])->assertRedirect();
        $expectCount(2, 'validateDraft');
        $this->assertSame('awaiting_draft_approval', $project->fresh()->workflow_stage);

        // 3. Customer approves the draft design.
        $this->actingAs($customer)->postJson(route('pemesanan.document.decision', $project), [
            'stage' => 'draft',
            'decision' => 'approved',
        ])->assertOk();
        $expectCount(3, 'decideDocument approved (draft)');
        $project->refresh();
        $this->assertSame('awaiting_dp', $project->workflow_stage);

        // 4. Customer uploads DP evidence.
        $invoice = $project->invoices()->create([
            'number' => 'SIM-1', 'type' => 'custom', 'name' => 'DP 20%',
            'amount' => 2000000, 'status' => 'pending',
        ]);
        $this->actingAs($customer)->postJson(
            route('pemesanan.invoice-evidence.upload', [$project, $invoice]),
            ['bukti_pembayaran' => UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf')]
        )->assertOk();
        $expectCount(4, 'uploadInvoiceEvidence (DP)');
        $this->assertSame('dp_verification', $project->fresh()->workflow_stage);

        // 5. Admin confirms the DP payment.
        $this->actingAs($admin)->post(route('admin.pemesanan.invoice.paid', [$project, $invoice]))->assertRedirect();
        $expectCount(5, 'markInvoicePaid (DP)');
        $this->assertSame('survey_scheduled', $project->fresh()->workflow_stage);

        // Sanity: every recorded entry has a non-empty, distinct-ish catatan.
        $notes = $project->statusTrackings()->pluck('catatan')->all();
        foreach ($notes as $note) {
            $this->assertNotEmpty($note);
        }
        $this->assertGreaterThan(1, count(array_unique($notes)), 'Expected varied notes, not all identical.');
    }
}
