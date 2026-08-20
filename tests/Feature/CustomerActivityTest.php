<?php

namespace Tests\Feature;

use App\Models\Konsultasi;
use App\Models\Pemesanan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CustomerActivityTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_open_unified_pesanan_saya_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('pesanan.saya'))
            ->assertOk()
            ->assertSee('Pesanan Saya')
            ->assertSee('Menunggu Konfirmasi')
            ->assertSee('Buat Pesanan')
            ->assertSee('aria-label="Jenis aktivitas"', false);
    }

    public function test_legacy_customer_activity_urls_redirect_to_the_matching_tab(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('aktivitas.saya'))
            ->assertRedirect('/pesanan-saya');

        $this->actingAs($user)
            ->get(route('konsultasi.saya'))
            ->assertRedirect('/pesanan-saya?tab=konsultasi');
    }

    public function test_guest_cannot_open_customer_activity_page(): void
    {
        $this->get(route('pesanan.saya'))
            ->assertRedirect(route('login'));
    }

    public function test_customer_can_filter_and_search_activity_history(): void
    {
        $user = User::factory()->create();
        $baseData = [
            'user_id' => $user->id,
            'nama' => $user->nama,
            'email' => $user->email,
            'no_telp' => '081234567890',
            'budget_range' => '10m_25m',
            'timeline' => 'flexible',
            'luas_ruangan' => 12,
            'tanggal_konsultasi' => now()->toDateString(),
            'waktu_konsultasi' => now()->format('H:i:s'),
        ];

        $pending = Konsultasi::create(array_merge($baseData, [
            'jenis_konsultasi' => 'free_consultation',
            'jenis_ruangan' => 'living_room',
            'deskripsi_kebutuhan' => 'Proyek Alpha',
            'status' => Konsultasi::STATUS_PENDING,
        ]));
        Konsultasi::create(array_merge($baseData, [
            'jenis_konsultasi' => 'virtual_design',
            'jenis_ruangan' => 'bedroom',
            'deskripsi_kebutuhan' => 'Proyek Beta',
            'status' => Konsultasi::STATUS_COMPLETED,
        ]));

        $this->actingAs($user)
            ->get(route('pesanan.saya', ['status' => 'completed']))
            ->assertOk()
            ->assertSee('Renovasi Interior')
            ->assertDontSee('Desain Interior Baru');

        $reference = 'KON-'.str_pad((string) $pending->id, 4, '0', STR_PAD_LEFT);
        $this->actingAs($user)
            ->get(route('pesanan.saya', ['q' => $reference]))
            ->assertOk()
            ->assertSee('#'.$reference)
            ->assertSee('Proyek Alpha')
            ->assertDontSee('Proyek Beta');
    }

    public function test_consultation_detail_is_not_cached_so_customers_see_admin_status_updates(): void
    {
        $user = User::factory()->create();
        $consultation = Konsultasi::create([
            'user_id' => $user->id,
            'nama' => $user->nama,
            'email' => $user->email,
            'no_telp' => '081234567890',
            'jenis_konsultasi' => 'free_consultation',
            'jenis_ruangan' => 'bedroom',
            'budget_range' => '10m_25m',
            'timeline' => 'flexible',
            'luas_ruangan' => 12,
            'deskripsi_kebutuhan' => 'Kebutuhan desain kamar.',
            'tanggal_konsultasi' => now()->toDateString(),
            'waktu_konsultasi' => now()->format('H:i:s'),
            'status' => Konsultasi::STATUS_CONFIRMED,
        ]);

        $response = $this->actingAs($user)
            ->get(route('konsultasi.show', $consultation))
            ->assertOk()
            ->assertSee('Konsultasi');

        $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
    }

    public function test_pesanan_saya_shows_tinjau_button_with_review_payload_when_draft_is_awaiting_approval(): void
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
            'deskripsi_keinginan_desain' => 'Ingin nuansa minimalis modern.',
        ]);

        $this->actingAs($admin)->post(route('admin.pemesanan.document.upload', $project), [
            'document_type' => 'design',
            'document' => UploadedFile::fake()->image('desain-awal.jpg'),
        ]);
        $this->actingAs($admin)->post(route('admin.pemesanan.document.upload', $project), [
            'document_type' => 'rab',
            'document' => UploadedFile::fake()->create('rab-awal.pdf', 100, 'application/pdf'),
        ]);
        $this->actingAs($admin)->post(route('admin.pemesanan.document.send', $project));
        $this->assertSame('awaiting_admin_validation', $project->fresh()->workflow_stage);

        $this->actingAs($admin)->post(route('admin.pemesanan.validate.send', $project), [
            'total_harga' => 10000000,
        ]);

        $project->refresh();
        $this->assertSame('awaiting_draft_approval', $project->workflow_stage);

        $response = $this->actingAs($customer)->get(route('pesanan.saya'));

        $response->assertOk()
            ->assertSee('Tinjau')
            ->assertSee('data-review=', false)
            ->assertSee('desain-awal.jpg')
            ->assertSee('rab-awal.pdf')
            ->assertDontSee('Lihat Detail');
    }

    public function test_customer_can_approve_draft_design_from_review_modal_via_ajax(): void
    {
        Storage::fake('local');

        $customer = User::factory()->create(['role' => 'pelanggan']);
        $admin = User::factory()->create(['role' => 'admin']);
        $project = Pemesanan::create([
            'id_user' => $customer->id,
            'tanggal_pesan' => now()->toDateString(),
            'status_pemesanan' => Pemesanan::STATUS_CONFIRMED,
            'workflow_stage' => 'awaiting_draft_approval',
            'progress' => 20,
            'total_harga' => 10000000,
            'jenis_proyek' => 'Desain interior',
            'jenis_bangunan' => 'Rumah tinggal',
        ]);

        $this->actingAs($admin)->postJson(route('admin.pemesanan.invoice.store', $project), [
            'name' => 'DP',
            'amount' => 2000000,
        ])->assertOk();

        $response = $this->actingAs($customer)->postJson(route('pemesanan.document.decision', $project), [
            'stage' => 'draft',
            'decision' => 'approved',
        ]);

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertSame('awaiting_dp', $project->fresh()->workflow_stage);

        // The response carries a fresh review payload so the customer's already-open
        // modal can switch straight into the payment view without a page reload, and
        // each unpaid invoice carries its own upload URL.
        $response->assertJsonPath('review.stage', 'payment');
        $response->assertJsonStructure(['review' => ['invoices' => [['uploadUrl']], 'bankAccountNumber']]);
        $response->assertJsonPath('review.invoices.0.uploadUrl', fn ($url) => str_contains($url, '/bukti-pembayaran'));
    }

    public function test_customer_can_request_revision_from_review_modal_via_ajax(): void
    {
        $customer = User::factory()->create(['role' => 'pelanggan']);
        $project = Pemesanan::create([
            'id_user' => $customer->id,
            'tanggal_pesan' => now()->toDateString(),
            'status_pemesanan' => Pemesanan::STATUS_CONFIRMED,
            'workflow_stage' => 'awaiting_draft_approval',
            'progress' => 20,
            'jenis_proyek' => 'Desain interior',
            'jenis_bangunan' => 'Rumah tinggal',
        ]);

        $response = $this->actingAs($customer)->postJson(route('pemesanan.document.decision', $project), [
            'stage' => 'draft',
            'decision' => 'revision_requested',
            'feedback' => 'Tolong perbesar area dapur.',
        ]);

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertSame('revision_requested', $project->fresh()->workflow_stage);
        $this->assertSame(2, $project->fresh()->draft_round);
    }
}
