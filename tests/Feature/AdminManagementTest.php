<?php

namespace Tests\Feature;

use App\Models\Konsultasi;
use App\Models\Pemesanan;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_pemesanan_heartbeat_signal_changes_when_a_new_consultation_arrives(): void
    {
        $admin = $this->user('admin@example.com', 'admin');
        $customer = $this->user('customer@example.com', 'pelanggan');

        $before = $this->actingAs($admin)->getJson(route('admin.pemesanan.heartbeat'))
            ->assertOk()
            ->json('signal');

        Konsultasi::create([
            'user_id' => $customer->id,
            'nama' => $customer->nama,
            'email' => $customer->email,
            'no_telp' => '08123456789',
            'jenis_konsultasi' => 'free_consultation',
            'jenis_ruangan' => 'living_room',
            'budget_range' => '10m_25m',
            'timeline' => '1_month',
            'deskripsi_kebutuhan' => 'Butuh desain kamar tidur.',
            'tanggal_konsultasi' => now()->addWeek()->toDateString(),
            'waktu_konsultasi' => '10:00',
            'status' => 'pending',
        ]);

        $after = $this->actingAs($admin)->getJson(route('admin.pemesanan.heartbeat'))
            ->assertOk()
            ->json('signal');

        $this->assertNotSame($before, $after);
    }

    public function test_admin_can_delete_a_rejected_consultation(): void
    {
        $admin = $this->user('admin@example.com', 'admin');
        $customer = $this->user('customer@example.com', 'pelanggan');
        $consultation = Konsultasi::create([
            'user_id' => $customer->id,
            'nama' => $customer->nama,
            'email' => $customer->email,
            'no_telp' => '08123456789',
            'jenis_konsultasi' => 'free_consultation',
            'jenis_ruangan' => 'living_room',
            'budget_range' => '10m_25m',
            'timeline' => '1_month',
            'deskripsi_kebutuhan' => 'Butuh desain kamar.',
            'tanggal_konsultasi' => now()->addWeek()->toDateString(),
            'waktu_konsultasi' => '10:00',
            'status' => Konsultasi::STATUS_CANCELLED,
        ]);

        $this->actingAs($admin)->get(route('admin.pemesanan.index'))
            ->assertOk()
            ->assertSee('Kelola Pesanan')
            ->assertDontSee('Tidak dilanjutkan');

        $this->actingAs($admin)->delete(route('admin.pemesanan.konsultasi.destroy', $consultation))
            ->assertRedirect();

        $this->assertDatabaseMissing('konsultasi', ['id' => $consultation->id]);
    }

    public function test_admin_cannot_delete_a_consultation_that_already_became_a_project(): void
    {
        $admin = $this->user('admin@example.com', 'admin');
        $customer = $this->user('customer@example.com', 'pelanggan');
        $designer = $this->user('designer@example.com', 'designer');
        $consultation = Konsultasi::create([
            'user_id' => $customer->id,
            'nama' => $customer->nama,
            'email' => $customer->email,
            'no_telp' => '08123456789',
            'jenis_konsultasi' => 'free_consultation',
            'jenis_ruangan' => 'living_room',
            'budget_range' => '10m_25m',
            'timeline' => '1_month',
            'deskripsi_kebutuhan' => 'Butuh desain ruang tamu.',
            'tanggal_konsultasi' => now()->addWeek()->toDateString(),
            'waktu_konsultasi' => '10:00',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)->post(route('admin.pemesanan.konsultasi.accept', $consultation), [
            'designer_id' => $designer->id,
        ])->assertRedirect();

        $this->actingAs($admin)->delete(route('admin.pemesanan.konsultasi.destroy', $consultation))
            ->assertStatus(422);

        $this->assertDatabaseHas('konsultasi', ['id' => $consultation->id]);
    }

    public function test_admin_can_delete_a_project_and_its_originating_consultation_is_removed_too(): void
    {
        $admin = $this->user('admin@example.com', 'admin');
        $customer = $this->user('customer@example.com', 'pelanggan');
        $designer = $this->user('designer@example.com', 'designer');
        $consultation = Konsultasi::create([
            'user_id' => $customer->id,
            'nama' => $customer->nama,
            'email' => $customer->email,
            'no_telp' => '08123456789',
            'jenis_konsultasi' => 'free_consultation',
            'jenis_ruangan' => 'living_room',
            'budget_range' => '10m_25m',
            'timeline' => '1_month',
            'deskripsi_kebutuhan' => 'Butuh desain ruang tamu.',
            'tanggal_konsultasi' => now()->addWeek()->toDateString(),
            'waktu_konsultasi' => '10:00',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)->post(route('admin.pemesanan.konsultasi.accept', $consultation), [
            'designer_id' => $designer->id,
        ])->assertRedirect();

        $project = $consultation->fresh()->pemesanan;
        $this->assertNotNull($project);

        $this->actingAs($admin)->delete(route('admin.pemesanan.destroy', $project))
            ->assertRedirect();

        $this->assertDatabaseMissing('pemesanan', ['id' => $project->id]);
        $this->assertDatabaseMissing('konsultasi', ['id' => $consultation->id]);
    }

    public function test_admin_can_update_persisted_project_management_fields(): void
    {
        $admin = $this->user('admin@example.com', 'admin');
        $customer = $this->user('customer@example.com', 'pelanggan');
        $designer = $this->user('designer@example.com', 'designer');

        $project = Pemesanan::create([
            'id_user' => $customer->id,
            'tanggal_pesan' => now()->toDateString(),
            'status_pemesanan' => 'dikonfirmasi',
            'workflow_stage' => 'approved',
            'jenis_proyek' => 'Kitchen Set',
        ]);

        $response = $this->actingAs($admin)->put(route('admin.proyek.update', $project), [
            'status_pemesanan' => 'sedang_dikerjakan',
            'progress' => 55,
            'target_selesai' => now()->addMonth()->toDateString(),
            'designer_id' => $designer->id,
            'total_harga' => 35000000,
            'catatan_progres' => 'Produksi kabinet sedang berjalan.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('pemesanan', [
            'id' => $project->id,
            'designer_id' => $designer->id,
            'status_pemesanan' => 'sedang_dikerjakan',
            'progress' => 55,
            'total_harga' => 35000000,
        ]);
        $this->assertDatabaseHas('status_tracking', [
            'id_pemesanan' => $project->id,
            'status' => 'sedang_dikerjakan',
            'catatan' => 'Produksi kabinet sedang berjalan.',
        ]);
    }

    public function test_admin_can_update_project_via_ajax_and_receives_json(): void
    {
        $admin = $this->user('admin@example.com', 'admin');
        $customer = $this->user('customer@example.com', 'pelanggan');
        $designer = $this->user('designer@example.com', 'designer');

        $project = Pemesanan::create([
            'id_user' => $customer->id,
            'tanggal_pesan' => now()->toDateString(),
            'status_pemesanan' => 'dikonfirmasi',
            'workflow_stage' => 'approved',
            'jenis_proyek' => 'Kitchen Set',
        ]);

        $response = $this->actingAs($admin)->putJson(route('admin.proyek.update', $project), [
            'status_pemesanan' => 'dikonfirmasi',
            'designer_id' => $designer->id,
        ]);

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseHas('pemesanan', ['id' => $project->id, 'designer_id' => $designer->id]);
    }

    public function test_non_admin_cannot_update_project(): void
    {
        $customer = $this->user('customer@example.com', 'pelanggan');
        $project = Pemesanan::create([
            'id_user' => $customer->id,
            'tanggal_pesan' => now()->toDateString(),
            'status_pemesanan' => 'pending',
        ]);

        $this->actingAs($customer)->put(route('admin.proyek.update', $project), [
            'status_pemesanan' => 'selesai',
            'progress' => 100,
        ])->assertForbidden();
    }

    public function test_assigned_designer_can_update_progress_but_other_designer_cannot(): void
    {
        $customer = $this->user('customer@example.com', 'pelanggan');
        $designer = $this->user('designer@example.com', 'designer');
        $otherDesigner = $this->user('other-designer@example.com', 'designer');
        $project = Pemesanan::create([
            'id_user' => $customer->id,
            'designer_id' => $designer->id,
            'tanggal_pesan' => now()->toDateString(),
            'status_pemesanan' => Pemesanan::STATUS_CONFIRMED,
            'workflow_stage' => 'approved',
            'progress' => 10,
        ]);

        $payload = [
            'status_pemesanan' => Pemesanan::STATUS_IN_PROGRESS,
            'progress' => 35,
            'target_selesai' => now()->addMonth()->toDateString(),
            'catatan_progres' => 'Pengukuran dan rancangan awal telah selesai.',
        ];

        $this->actingAs($otherDesigner)
            ->put(route('designer.proyek.update', $project), $payload)
            ->assertForbidden();

        $this->actingAs($designer)
            ->put(route('designer.proyek.update', $project), $payload)
            ->assertRedirect();

        $this->assertDatabaseHas('pemesanan', [
            'id' => $project->id,
            'status_pemesanan' => Pemesanan::STATUS_IN_PROGRESS,
            'progress' => 35,
            'designer_id' => $designer->id,
        ]);
        $this->assertDatabaseHas('status_tracking', [
            'id_pemesanan' => $project->id,
            'actor_id' => $designer->id,
            'catatan' => 'Pengukuran dan rancangan awal telah selesai.',
        ]);
    }

    public function test_admin_can_create_update_and_delete_user(): void
    {
        $admin = $this->user('admin@example.com', 'admin');

        $this->actingAs($admin)->post(route('admin.users.store'), [
            'nama' => 'Desainer Baru',
            'email' => 'new-designer@example.com',
            'role' => 'designer',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect();

        $user = User::where('email', 'new-designer@example.com')->firstOrFail();

        $this->actingAs($admin)->put(route('admin.users.update', $user), [
            'nama' => 'Desainer Diperbarui',
            'email' => 'new-designer@example.com',
            'role' => 'designer',
            'password' => '',
            'password_confirmation' => '',
        ])->assertRedirect();

        $this->assertDatabaseHas('users', ['id' => $user->id, 'nama' => 'Desainer Diperbarui']);

        $this->actingAs($admin)->delete(route('admin.users.destroy', $user))->assertRedirect();
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_user_management_edit_button_contains_safe_modal_data(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create([
            'nama' => "D'Artagnan Interior",
            'email' => 'designer@example.com',
            'role' => 'designer',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('onclick="openUserModal(this)"', false)
            ->assertSee('data-user-name="D&#039;Artagnan Interior"', false)
            ->assertSee('data-update-url="'.route('admin.users.update', $user).'"', false);
    }

    public function test_admin_cannot_delete_their_own_account(): void
    {
        $admin = $this->user('admin@example.com', 'admin');

        $response = $this->actingAs($admin)->delete(route('admin.users.destroy', $admin));

        $response->assertRedirect()->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_admin_cannot_change_role_of_user_with_business_history(): void
    {
        $admin = $this->user('admin@example.com', 'admin');
        $customer = $this->user('customer@example.com', 'pelanggan');
        Pemesanan::create([
            'id_user' => $customer->id,
            'tanggal_pesan' => now()->toDateString(),
            'status_pemesanan' => Pemesanan::STATUS_PENDING,
        ]);

        $this->actingAs($admin)->put(route('admin.users.update', $customer), [
            'nama' => $customer->nama,
            'email' => $customer->email,
            'role' => 'designer',
            'password' => '',
            'password_confirmation' => '',
        ])->assertRedirect()->assertSessionHas('error');

        $this->assertSame('pelanggan', $customer->fresh()->role);
    }

    public function test_all_admin_management_pages_render_with_real_records(): void
    {
        $admin = $this->user('admin@example.com', 'admin');
        $customer = $this->user('customer@example.com', 'pelanggan');

        Pemesanan::create([
            'id_user' => $customer->id,
            'tanggal_pesan' => now()->toDateString(),
            'status_pemesanan' => 'sedang_dikerjakan',
            'jenis_proyek' => 'Interior Ruang Tamu',
            'progress' => 40,
        ]);

        foreach ([
            'dashboard.admin',
            'admin.pemesanan.index',
            'admin.katalog.index',
            'admin.pelanggan.index',
            'admin.users.index',
        ] as $route) {
            $this->actingAs($admin)->get(route($route))->assertOk();
        }
    }

    public function test_admin_pelanggan_index_shows_total_projects_and_total_spending(): void
    {
        $admin = $this->user('admin@example.com', 'admin');
        $customer = $this->user('customer@example.com', 'pelanggan');

        Pemesanan::create([
            'id_user' => $customer->id,
            'tanggal_pesan' => now()->toDateString(),
            'status_pemesanan' => 'sedang_dikerjakan',
            'jenis_proyek' => 'Interior Ruang Tamu',
            'progress' => 40,
            'total_harga' => 85000000,
        ]);
        Pemesanan::create([
            'id_user' => $customer->id,
            'tanggal_pesan' => now()->toDateString(),
            'status_pemesanan' => 'selesai',
            'jenis_proyek' => 'Interior Kamar Tidur',
            'progress' => 100,
            'total_harga' => 35000000,
        ]);

        $this->actingAs($admin)->get(route('admin.pelanggan.index'))
            ->assertOk()
            ->assertSee('Total Proyek')
            ->assertSee('Total Belanja')
            ->assertSee('2 Proyek')
            ->assertSee('Rp 120.000.000');
    }

    public function test_admin_pemesanan_index_renders_with_project_documents_in_various_stages(): void
    {
        $admin = $this->user('admin@example.com', 'admin');
        $customer = $this->user('customer@example.com', 'pelanggan');

        $draftProject = Pemesanan::create([
            'id_user' => $customer->id,
            'tanggal_pesan' => now()->toDateString(),
            'status_pemesanan' => 'dikonfirmasi',
            'workflow_stage' => 'draft_design',
            'progress' => 10,
            'jenis_proyek' => 'Desain interior',
            'jenis_bangunan' => 'Rumah tinggal',
        ]);
        $draftProject->documents()->create([
            'uploaded_by' => $admin->id,
            'stage' => 'draft',
            'document_type' => 'design',
            'submission_round' => 1,
            'path' => 'project-documents/'.$draftProject->id.'/draft/desain.jpg',
            'original_name' => 'desain-awal.jpg',
            'version' => 1,
        ]);

        $sentProject = Pemesanan::create([
            'id_user' => $customer->id,
            'tanggal_pesan' => now()->toDateString(),
            'status_pemesanan' => 'dikonfirmasi',
            'workflow_stage' => 'awaiting_draft_approval',
            'progress' => 20,
            'jenis_proyek' => 'Renovasi interior',
            'jenis_bangunan' => 'Apartemen',
        ]);
        foreach (['design', 'rab'] as $type) {
            $sentProject->documents()->create([
                'uploaded_by' => $admin->id,
                'stage' => 'draft',
                'document_type' => $type,
                'submission_round' => 1,
                'path' => 'project-documents/'.$sentProject->id.'/draft/'.$type.'.pdf',
                'original_name' => $type.'-awal.pdf',
                'version' => 1,
            ]);
        }
        $sentProject->documentDecisions()->create([
            'decided_by' => $customer->id,
            'stage' => 'draft',
            'submission_round' => 1,
            'decision' => 'revision_requested',
            'feedback' => 'Perbaiki tata letak dapur.',
        ]);

        $this->actingAs($admin)->get(route('admin.pemesanan.index'))
            ->assertOk()
            ->assertSee('Kelola Pesanan')
            ->assertSee('desain-awal.jpg');
    }

    public function test_admin_pemesanan_index_shows_validation_card_when_awaiting_admin_validation(): void
    {
        $admin = $this->user('admin@example.com', 'admin');
        $customer = $this->user('customer@example.com', 'pelanggan');
        $designer = $this->user('designer@example.com', 'designer');

        $project = Pemesanan::create([
            'id_user' => $customer->id,
            'designer_id' => $designer->id,
            'tanggal_pesan' => now()->toDateString(),
            'status_pemesanan' => 'dikonfirmasi',
            'workflow_stage' => 'awaiting_admin_validation',
            'progress' => 15,
            'total_harga' => 20000000,
            'jenis_proyek' => 'Desain interior',
            'jenis_bangunan' => 'Rumah tinggal',
        ]);
        foreach (['design', 'rab'] as $type) {
            $project->documents()->create([
                'uploaded_by' => $designer->id,
                'stage' => 'draft',
                'document_type' => $type,
                'submission_round' => 1,
                'path' => 'project-documents/'.$project->id.'/draft/'.$type.'.pdf',
                'original_name' => $type.'-siap-validasi.pdf',
                'version' => 1,
            ]);
        }

        $this->actingAs($admin)->get(route('admin.pemesanan.index'))
            ->assertOk()
            ->assertSee('Tinjau Pemesanan')
            ->assertSee('Dokumen dari Desainer')
            ->assertSee('design-siap-validasi.pdf')
            ->assertSee('rab-siap-validasi.pdf')
            ->assertSee('Riwayat Tagihan')
            ->assertSee('Buat Tagihan')
            ->assertSee('Total Sudah Ditagihkan')
            ->assertSee('Sisa Pembayaran')
            ->assertSee(config('company.bank.display_name'));
    }

    public function test_admin_pemesanan_index_shows_unified_stage_labels_and_gates_finalize_control(): void
    {
        $admin = $this->user('admin@example.com', 'admin');
        $customer = $this->user('customer@example.com', 'pelanggan');

        $draftProject = Pemesanan::create([
            'id_user' => $customer->id,
            'tanggal_pesan' => now()->toDateString(),
            'status_pemesanan' => 'dikonfirmasi',
            'workflow_stage' => 'draft_design',
            'progress' => 10,
            'jenis_proyek' => 'Desain interior',
            'jenis_bangunan' => 'Rumah tinggal',
        ]);

        $approvedProject = Pemesanan::create([
            'id_user' => $customer->id,
            'tanggal_pesan' => now()->toDateString(),
            'status_pemesanan' => 'dikonfirmasi',
            'workflow_stage' => 'approved',
            'progress' => 60,
            'jenis_proyek' => 'Kitchen set',
            'jenis_bangunan' => 'Rumah tinggal',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.pemesanan.index'));

        $response->assertOk()
            ->assertSee('Menunggu Desain Awal &amp; Draft RAB', false)
            ->assertSee('&quot;stageLabel&quot;:&quot;Menunggu Desain Awal &amp; Draft RAB&quot;', false)
            ->assertSee('&quot;stageLabel&quot;:&quot;Pengerjaan Proyek&quot;', false)
            ->assertSee('&quot;canFinalize&quot;:false', false)
            ->assertSee('&quot;canFinalize&quot;:true', false);
    }

    public function test_admin_pemesanan_show_page_no_longer_exists(): void
    {
        $admin = $this->user('admin@example.com', 'admin');
        $customer = $this->user('customer@example.com', 'pelanggan');

        $project = Pemesanan::create([
            'id_user' => $customer->id,
            'tanggal_pesan' => now()->toDateString(),
            'status_pemesanan' => 'dikonfirmasi',
            'workflow_stage' => 'draft_design',
            'jenis_proyek' => 'Desain interior',
        ]);

        $this->expectException(\Symfony\Component\Routing\Exception\RouteNotFoundException::class);
        route('admin.pemesanan.show', $project);
    }

    public function test_admin_can_confirm_dp_payment_and_assign_designer_from_kelola_pesanan_card(): void
    {
        $admin = $this->user('admin@example.com', 'admin');
        $customer = $this->user('customer@example.com', 'pelanggan');
        $designer = $this->user('designer@example.com', 'designer');

        $project = Pemesanan::create([
            'id_user' => $customer->id,
            'designer_id' => $designer->id,
            'tanggal_pesan' => now()->toDateString(),
            'status_pemesanan' => 'dikonfirmasi',
            'workflow_stage' => 'dp_verification',
            'progress' => 25,
            'total_harga' => 10000000,
            'jenis_proyek' => 'Desain interior',
            'jenis_bangunan' => 'Rumah tinggal',
        ]);
        $invoice = $project->invoices()->create([
            'type' => 'dp_20',
            'number' => 'DP-2026-00001',
            'amount' => 2000000,
            'status' => 'submitted',
            'due_date' => now()->addDays(7)->toDateString(),
        ]);

        $this->actingAs($admin)->get(route('admin.pemesanan.index'))
            ->assertOk()
            ->assertSee($invoice->number);

        $this->actingAs($admin)->post(route('admin.pemesanan.invoice.paid', [$project, $invoice]))
            ->assertRedirect();

        $project->refresh();
        $this->assertSame('survey_scheduled', $project->workflow_stage);
        $this->assertSame('paid', $invoice->fresh()->status);
        $this->assertSame($designer->id, $project->designer_id);
    }

    public function test_admin_navigation_separates_orders_customers_and_user_management(): void
    {
        $admin = $this->user('admin@example.com', 'admin');

        $this->actingAs($admin)->get(route('admin.pemesanan.index'))
            ->assertOk()
            ->assertSee('Kelola Pesanan')
            ->assertSee('Kelola Pelanggan')
            ->assertSee('Manajemen User')
            ->assertSee('Daftar Permintaan &amp; Pesanan', false)
            ->assertSee('Cari nama, email, atau nomor telepon')
            ->assertSee('Tambah pelanggan baru')
            ->assertDontSee('Pelanggan lama')
            ->assertDontSee('>Pekerjaan<', false)
            ->assertDontSee('Status Proyek');

        $this->actingAs($admin)->get(route('admin.proyek.index'))
            ->assertRedirect(route('admin.pemesanan.index'));
    }

    public function test_assigned_consultation_creates_a_project_after_it_is_completed(): void
    {
        $admin = $this->user('admin@example.com', 'admin');
        $customer = $this->user('customer@example.com', 'pelanggan');
        $designer = $this->user('designer@example.com', 'designer');
        $consultation = Konsultasi::create([
            'user_id' => $customer->id,
            'nama' => $customer->nama,
            'email' => $customer->email,
            'no_telp' => '08123456789',
            'jenis_konsultasi' => 'free_consultation',
            'jenis_ruangan' => 'living_room',
            'budget_range' => '10m_25m',
            'timeline' => '1_month',
            'deskripsi_kebutuhan' => 'Membutuhkan desain ruang tamu.',
            'tanggal_konsultasi' => now()->addWeek()->toDateString(),
            'waktu_konsultasi' => '10:00',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)->get('/admin/konsultasi')->assertNotFound();
        $this->actingAs($admin)->get(route('admin.pemesanan.index'))
            ->assertOk()
            ->assertSee('Daftar Permintaan &amp; Pesanan', false)
            ->assertSee('Konsultasi masuk')
            ->assertDontSee('Daftar Pemesanan')
            ->assertDontSee('Permintaan Konsultasi')
            ->assertSee('Membutuhkan desain ruang tamu.');

        $this->actingAs($admin)->post(route('admin.pemesanan.konsultasi.accept', $consultation), [
            'designer_id' => $designer->id,
        ])->assertRedirect();

        $this->actingAs($admin)->get(route('admin.pemesanan.index'))
            ->assertOk()
            ->assertSee('Penanggung Jawab')
            ->assertSee('Status')
            ->assertSee($designer->nama)
            ->assertSee('Pilih desainer')
            ->assertDontSee('Atur Jadwal')
            ->assertDontSee('>Tolak<', false);

        $this->assertSame(Konsultasi::STATUS_CONFIRMED, $consultation->fresh()->status);
        $this->assertSame($designer->id, $consultation->fresh()->designer_id);

        $this->actingAs($designer)->post(route('designer.konsultasi.complete', $consultation), [
            'consultation_result' => 'Kebutuhan pelanggan telah dibahas dan siap dilanjutkan ke desain awal.',
        ])->assertRedirect();

        $consultation->refresh();
        $this->assertNotNull($consultation->pemesanan_id);
        $this->assertSame(Konsultasi::STATUS_COMPLETED, $consultation->status);
        $this->assertDatabaseHas('pemesanan', [
            'id' => $consultation->pemesanan_id,
            'id_user' => $customer->id,
            'status_pemesanan' => 'dikonfirmasi',
            'progress' => 10,
        ]);

        $this->actingAs($admin)->get(route('admin.pemesanan.index'))
            ->assertOk()
            ->assertSee('DI-'.str_pad((string) $consultation->pemesanan_id, 3, '0', STR_PAD_LEFT))
            ->assertDontSee('KS-'.str_pad((string) $consultation->id, 3, '0', STR_PAD_LEFT));
    }

    public function test_project_is_created_and_manageable_immediately_on_accept_while_staying_in_konsultasi_stage(): void
    {
        Storage::fake('local');

        $admin = $this->user('admin@example.com', 'admin');
        $customer = $this->user('customer@example.com', 'pelanggan');
        $designer = $this->user('designer@example.com', 'designer');
        $consultation = Konsultasi::create([
            'user_id' => $customer->id,
            'nama' => $customer->nama,
            'email' => $customer->email,
            'no_telp' => '08123456789',
            'jenis_konsultasi' => 'free_consultation',
            'jenis_ruangan' => 'living_room',
            'budget_range' => '10m_25m',
            'timeline' => '1_month',
            'deskripsi_kebutuhan' => 'Membutuhkan desain ruang tamu.',
            'tanggal_konsultasi' => now()->addWeek()->toDateString(),
            'waktu_konsultasi' => '10:00',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)->post(route('admin.pemesanan.konsultasi.accept', $consultation), [
            'designer_id' => $designer->id,
        ])->assertRedirect();

        $consultation->refresh();
        $this->assertNotNull($consultation->pemesanan_id);
        $this->assertSame(Konsultasi::STATUS_CONFIRMED, $consultation->status);

        $project = $consultation->pemesanan;
        $this->assertNotNull($project);
        $this->assertSame('konsultasi', $project->workflow_stage);

        // The admin list must show a manageable "Kelola Pesanan" row, not the old placeholder text.
        $this->actingAs($admin)->get(route('admin.pemesanan.index'))
            ->assertOk()
            ->assertSee('Kelola Pesanan')
            ->assertDontSee('Menunggu hasil konsultasi');

        // Admin can upload design/RAB documents as a backup for the designer while still in Konsultasi.
        $this->actingAs($admin)->post(route('admin.pemesanan.document.upload', $project), [
            'document_type' => 'design',
            'document' => UploadedFile::fake()->image('desain-awal.jpg'),
        ])->assertRedirect();
        $this->actingAs($admin)->post(route('admin.pemesanan.document.upload', $project), [
            'document_type' => 'rab',
            'document' => UploadedFile::fake()->create('rab-awal.pdf', 100, 'application/pdf'),
        ])->assertRedirect();

        // Uploading documents must not change the workflow stage.
        $this->assertSame('konsultasi', $project->fresh()->workflow_stage);
        $this->assertDatabaseHas('project_documents', [
            'pemesanan_id' => $project->id,
            'document_type' => 'design',
            'stage' => 'draft',
        ]);
        $this->assertDatabaseHas('project_documents', [
            'pemesanan_id' => $project->id,
            'document_type' => 'rab',
            'stage' => 'draft',
        ]);

        // Admin can set the Nilai Penawaran (total_harga) as a backup for the designer.
        $this->actingAs($admin)->put(route('admin.proyek.update', $project), [
            'status_pemesanan' => $project->status_pemesanan,
            'total_harga' => 15000000,
        ])->assertRedirect();
        $this->assertSame('15000000.00', $project->fresh()->total_harga);

        // Admin can still reassign the designer while the project is in the Konsultasi stage.
        $secondDesigner = $this->user('designer2@example.com', 'designer');
        $this->actingAs($admin)->put(route('admin.pemesanan.konsultasi.assign', $consultation), [
            'designer_id' => $secondDesigner->id,
        ])->assertRedirect();
        $this->assertSame($secondDesigner->id, $consultation->fresh()->designer_id);
        $this->assertSame($secondDesigner->id, $project->fresh()->designer_id);

        // Once the designer completes the consultation, the stage advances automatically
        // and the previously uploaded backup documents remain attached.
        $this->actingAs($secondDesigner)->post(route('designer.konsultasi.complete', $consultation), [
            'consultation_result' => 'Kebutuhan pelanggan telah dibahas dan siap dilanjutkan ke desain awal.',
        ])->assertRedirect();

        $project->refresh();
        $this->assertSame('draft_design', $project->workflow_stage);
        $this->assertDatabaseHas('project_documents', [
            'pemesanan_id' => $project->id,
            'document_type' => 'design',
            'stage' => 'draft',
        ]);
        $this->assertDatabaseHas('project_documents', [
            'pemesanan_id' => $project->id,
            'document_type' => 'rab',
            'stage' => 'draft',
        ]);
    }

    public function test_admin_can_complete_consultation_directly_from_kelola_pesanan(): void
    {
        $admin = $this->user('admin@example.com', 'admin');
        $customer = $this->user('customer@example.com', 'pelanggan');
        $designer = $this->user('designer@example.com', 'designer');
        $consultation = Konsultasi::create([
            'user_id' => $customer->id,
            'nama' => $customer->nama,
            'email' => $customer->email,
            'no_telp' => '08123456789',
            'jenis_konsultasi' => 'free_consultation',
            'jenis_ruangan' => 'living_room',
            'budget_range' => '10m_25m',
            'timeline' => '1_month',
            'deskripsi_kebutuhan' => 'Membutuhkan desain ruang tamu.',
            'tanggal_konsultasi' => now()->addWeek()->toDateString(),
            'waktu_konsultasi' => '10:00',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)->post(route('admin.pemesanan.konsultasi.accept', $consultation), [
            'designer_id' => $designer->id,
        ])->assertRedirect();

        $project = $consultation->fresh()->pemesanan;
        $this->assertSame('konsultasi', $project->workflow_stage);

        $this->actingAs($admin)->post(route('admin.pemesanan.konsultasi.complete', $consultation), [
            'consultation_result' => 'Admin merangkum hasil konsultasi karena desainer belum sempat.',
        ])->assertRedirect();

        $project->refresh();
        $this->assertSame('draft_design', $project->workflow_stage);
        $this->assertSame(Konsultasi::STATUS_COMPLETED, $consultation->fresh()->status);
    }

    public function test_completed_legacy_consultation_can_be_continued_as_managed_order(): void
    {
        $admin = $this->user('admin@example.com', 'admin');
        $customer = $this->user('customer@example.com', 'pelanggan');
        $consultation = Konsultasi::create([
            'user_id' => $customer->id,
            'nama' => $customer->nama,
            'email' => $customer->email,
            'no_telp' => '08123456789',
            'alamat' => 'Kartika Indah',
            'jenis_konsultasi' => 'free_consultation',
            'jenis_ruangan' => 'living_room',
            'budget_range' => '10m_25m',
            'timeline' => 'flexible',
            'luas_ruangan' => 30,
            'deskripsi_kebutuhan' => 'Konsultasi lama yang belum menjadi pesanan.',
            'tanggal_konsultasi' => now()->toDateString(),
            'waktu_konsultasi' => '10:00',
            'status' => Konsultasi::STATUS_COMPLETED,
        ]);

        $this->actingAs($admin)->get(route('admin.pemesanan.index'))
            ->assertOk()
            ->assertSee('Lanjutkan ke Pesanan');

        $this->actingAs($admin)
            ->post(route('admin.pemesanan.konsultasi.convert', $consultation))
            ->assertRedirect();

        $consultation->refresh();
        $this->assertNotNull($consultation->pemesanan_id);
        $this->assertDatabaseHas('pemesanan', [
            'id' => $consultation->pemesanan_id,
            'jenis_proyek' => 'Desain Interior Baru',
            'jenis_bangunan' => 'Rumah Tinggal',
        ]);

        $this->actingAs($admin)->get(route('admin.pemesanan.index', ['search' => 'DI-'.$consultation->pemesanan_id]))
            ->assertOk()
            ->assertSee('Kelola Pesanan')
            ->assertDontSee('KS-'.str_pad((string) $consultation->id, 3, '0', STR_PAD_LEFT));
    }

    public function test_project_workflow_rejects_inconsistent_status_and_progress(): void
    {
        $admin = $this->user('admin@example.com', 'admin');
        $customer = $this->user('customer@example.com', 'pelanggan');
        $project = Pemesanan::create([
            'id_user' => $customer->id,
            'tanggal_pesan' => now()->toDateString(),
            'status_pemesanan' => 'dikonfirmasi',
            'progress' => 10,
        ]);

        $this->actingAs($admin)->put(route('admin.proyek.update', $project), [
            'status_pemesanan' => 'dikonfirmasi',
            'progress' => 90,
        ])->assertSessionHasErrors('progress');

        $this->assertDatabaseHas('pemesanan', [
            'id' => $project->id,
            'status_pemesanan' => 'dikonfirmasi',
            'progress' => 10,
        ]);
    }

    public function test_admin_dashboard_shows_operational_priorities_and_real_status_activity(): void
    {
        $admin = $this->user('admin@example.com', 'admin');
        $customer = $this->user('customer@example.com', 'pelanggan');

        $project = Pemesanan::create([
            'id_user' => $customer->id,
            'tanggal_pesan' => now()->toDateString(),
            'status_pemesanan' => 'pending',
            'jenis_proyek' => 'Kitchen Set Prioritas',
            'target_selesai' => now()->addDays(3)->toDateString(),
        ]);

        $project->statusTrackings()->create([
            'status' => 'pending',
            'tanggal_update' => now()->toDateString(),
            'catatan' => 'Pesanan baru diajukan oleh pelanggan.',
        ]);

        $response = $this->actingAs($admin)->get(route('dashboard.admin'));

        $response
            ->assertOk()
            ->assertSee('Deadline ≤ 7 Hari')
            ->assertSee('Pelanggan Baru')
            ->assertSee('Permintaan Baru')
            ->assertSee('Kitchen Set Prioritas')
            ->assertSee('Pesanan baru diajukan oleh pelanggan.')
            ->assertDontSee('Pendapatan Terbayar');
    }

    public function test_admin_dashboard_activity_feed_shows_distinct_titles_not_repeated_status(): void
    {
        $admin = $this->user('admin@example.com', 'admin');
        $customer = $this->user('customer@example.com', 'pelanggan');

        // status_pemesanan stays 'dikonfirmasi' across the entire design/payment
        // workflow, so every entry used to render the same generic title. The
        // activity feed must instead surface each tracking's own note.
        $project = Pemesanan::create([
            'id_user' => $customer->id,
            'tanggal_pesan' => now()->toDateString(),
            'status_pemesanan' => 'dikonfirmasi',
            'jenis_proyek' => 'Renovasi Dapur',
        ]);

        $project->statusTrackings()->create([
            'status' => 'dikonfirmasi',
            'tanggal_update' => now()->toDateString(),
            'catatan' => 'Desain awal/RAB dikirim dan menunggu validasi admin.',
            'created_at' => now()->subMinutes(2),
        ]);
        $project->statusTrackings()->create([
            'status' => 'dikonfirmasi',
            'tanggal_update' => now()->toDateString(),
            'catatan' => 'Bukti pembayaran DP telah diunggah dan menunggu verifikasi admin.',
            'created_at' => now()->subMinute(),
        ]);

        $response = $this->actingAs($admin)->get(route('dashboard.admin'));

        $response->assertOk()
            ->assertSee('Desain awal/RAB dikirim dan menunggu validasi admin.')
            ->assertSee('Bukti pembayaran DP telah diunggah dan menunggu verifikasi admin.');
    }

    public function test_designer_dashboard_only_shows_projects_assigned_to_that_designer(): void
    {
        $customer = $this->user('customer@example.com', 'pelanggan');
        $designer = $this->user('designer@example.com', 'designer');
        $otherDesigner = $this->user('other-designer@example.com', 'designer');

        $ownProject = Pemesanan::create([
            'id_user' => $customer->id,
            'designer_id' => $designer->id,
            'tanggal_pesan' => now()->toDateString(),
            'status_pemesanan' => 'sedang_dikerjakan',
            'jenis_proyek' => 'Proyek Milik Desainer',
            'progress' => 45,
        ]);
        $ownProject->statusTrackings()->create([
            'status' => 'sedang_dikerjakan',
            'tanggal_update' => now()->toDateString(),
            'catatan' => 'Proyek mulai dikerjakan.',
        ]);

        $otherProject = Pemesanan::create([
            'id_user' => $customer->id,
            'designer_id' => $otherDesigner->id,
            'tanggal_pesan' => now()->toDateString(),
            'status_pemesanan' => 'sedang_dikerjakan',
            'jenis_proyek' => 'Proyek Desainer Lain',
            'progress' => 70,
        ]);
        $otherProject->statusTrackings()->create([
            'status' => 'sedang_dikerjakan',
            'tanggal_update' => now()->toDateString(),
            'catatan' => 'Proyek mulai dikerjakan.',
        ]);

        $this->actingAs($designer)
            ->get(route('dashboard.designer'))
            ->assertOk()
            ->assertSee('Riwayat Aktivitas')
            ->assertSee('Proyek Milik Desainer')
            ->assertDontSee('Proyek Desainer Lain');
    }

    public function test_designer_has_separate_manageable_project_page_for_assigned_work(): void
    {
        $customer = $this->user('customer@example.com', 'pelanggan');
        $designer = $this->user('designer@example.com', 'designer');
        $otherDesigner = $this->user('other-designer@example.com', 'designer');

        Pemesanan::create([
            'id_user' => $customer->id,
            'designer_id' => $designer->id,
            'tanggal_pesan' => now()->toDateString(),
            'status_pemesanan' => Pemesanan::STATUS_IN_PROGRESS,
            'jenis_proyek' => 'Interior Kantor Aktif',
            'progress' => 60,
        ]);
        Pemesanan::create([
            'id_user' => $customer->id,
            'designer_id' => $designer->id,
            'tanggal_pesan' => now()->toDateString(),
            'status_pemesanan' => Pemesanan::STATUS_COMPLETED,
            'jenis_proyek' => 'Interior Selesai',
            'progress' => 100,
        ]);
        Pemesanan::create([
            'id_user' => $customer->id,
            'designer_id' => $otherDesigner->id,
            'tanggal_pesan' => now()->toDateString(),
            'status_pemesanan' => Pemesanan::STATUS_IN_PROGRESS,
            'jenis_proyek' => 'Proyek Rahasia Desainer Lain',
            'progress' => 30,
        ]);

        $this->actingAs($designer)->get(route('designer.projects.index'))
            ->assertOk()
            ->assertSee('Dashboard')
            ->assertSee('Proyek Saya')
            ->assertSee('Interior Kantor Aktif')
            ->assertSee('Interior Selesai')
            ->assertSee('Kelola')
            ->assertDontSee('Proyek Rahasia Desainer Lain');

        $this->actingAs($designer)->get(route('designer.projects.index', ['status' => Pemesanan::STATUS_COMPLETED]))
            ->assertOk()
            ->assertSee('Interior Selesai')
            ->assertDontSee('Interior Kantor Aktif')
            ->assertDontSee('Proyek Rahasia Desainer Lain');
    }

    public function test_designer_projects_page_renders_kelola_modal_data_with_documents_and_decisions(): void
    {
        $customer = $this->user('customer@example.com', 'pelanggan');
        $designer = $this->user('designer@example.com', 'designer');

        $konsultasi = Konsultasi::create([
            'user_id' => $customer->id,
            'nama' => $customer->nama,
            'email' => $customer->email,
            'no_telp' => '08123456789',
            'jenis_konsultasi' => 'free_consultation',
            'jenis_ruangan' => 'living_room',
            'budget_range' => '10m_25m',
            'timeline' => '1_month',
            'deskripsi_kebutuhan' => 'Membutuhkan konsultasi desain ruang tamu.',
            'tanggal_konsultasi' => now()->toDateString(),
            'waktu_konsultasi' => '10:00',
            'status' => Konsultasi::STATUS_COMPLETED,
            'designer_id' => $designer->id,
            'attachments' => [['path' => 'consultation-attachments/1/denah.jpg', 'name' => 'denah-rumah.jpg']],
        ]);

        $project = Pemesanan::create([
            'id_user' => $customer->id,
            'designer_id' => $designer->id,
            'tanggal_pesan' => now()->toDateString(),
            'status_pemesanan' => Pemesanan::STATUS_CONFIRMED,
            'workflow_stage' => 'draft_design',
            'progress' => 10,
            'jenis_proyek' => 'Desain interior',
            'jenis_bangunan' => 'Rumah tinggal',
            'deskripsi_keinginan_desain' => 'Ingin nuansa minimalis modern.',
        ]);
        $konsultasi->update(['pemesanan_id' => $project->id]);

        $project->documents()->create([
            'uploaded_by' => $designer->id,
            'stage' => 'draft',
            'document_type' => 'design',
            'submission_round' => 1,
            'path' => 'project-documents/'.$project->id.'/draft/desain.jpg',
            'original_name' => 'desain-awal.jpg',
            'version' => 1,
        ]);
        $project->documentDecisions()->create([
            'decided_by' => $customer->id,
            'stage' => 'draft',
            'submission_round' => 1,
            'decision' => 'revision_requested',
            'feedback' => 'Perbaiki tata letak dapur.',
        ]);

        $this->actingAs($designer)->get(route('designer.projects.index'))
            ->assertOk()
            ->assertSee('Kelola')
            ->assertSee('desain-awal.jpg')
            ->assertSee('denah-rumah.jpg');
    }

    public function test_designer_projects_page_shows_unified_stage_label_instead_of_manual_status_dropdown(): void
    {
        $customer = $this->user('customer@example.com', 'pelanggan');
        $designer = $this->user('designer@example.com', 'designer');

        Pemesanan::create([
            'id_user' => $customer->id,
            'designer_id' => $designer->id,
            'tanggal_pesan' => now()->toDateString(),
            'status_pemesanan' => 'dikonfirmasi',
            'workflow_stage' => 'awaiting_dp',
            'progress' => 25,
            'jenis_proyek' => 'Desain interior',
            'jenis_bangunan' => 'Rumah tinggal',
        ]);

        $this->actingAs($designer)->get(route('designer.projects.index'))
            ->assertOk()
            ->assertSee('&quot;stageLabel&quot;:&quot;Menunggu Pembayaran DP&quot;', false)
            ->assertDontSee('id="dpStatus" name="status_pemesanan" form="dpForm" class', false);
    }

    public function test_designer_public_navigation_has_dashboard_shortcut(): void
    {
        $designer = User::factory()->create(['role' => 'designer']);

        $this->actingAs($designer)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('aria-label="Buka Desainer Panel"', false)
            ->assertSee(route('dashboard.designer'), false);
    }

    public function test_only_assigned_designer_can_open_project_detail(): void
    {
        $customer = $this->user('customer@example.com', 'pelanggan');
        $designer = $this->user('designer@example.com', 'designer');
        $otherDesigner = $this->user('other-designer@example.com', 'designer');

        $project = Pemesanan::create([
            'id_user' => $customer->id,
            'designer_id' => $designer->id,
            'tanggal_pesan' => now()->toDateString(),
            'status_pemesanan' => 'sedang_dikerjakan',
            'jenis_proyek' => 'Interior Kantor',
        ]);

        $this->actingAs($designer)
            ->get(route('pemesanan.show', $project))
            ->assertOk();

        $this->actingAs($otherDesigner)
            ->get(route('pemesanan.show', $project))
            ->assertForbidden();
    }

    public function test_admin_can_record_whatsapp_order_for_existing_customer(): void
    {
        $admin = $this->user('admin@example.com', 'admin');
        $customer = $this->user('customer@example.com', 'pelanggan');

        $this->actingAs($admin)->post(route('admin.pemesanan.store'), [
            'customer_mode' => 'existing',
            'id_user' => $customer->id,
            'sumber_masuk' => 'whatsapp',
            'tanggal_pesan' => now()->toDateString(),
            'jenis_proyek' => 'Kitchen Set',
            'jenis_bangunan' => 'Rumah tinggal',
            'deskripsi_keinginan_desain' => 'Pelanggan mengirim ukuran awal melalui WhatsApp.',
        ])->assertRedirect(route('admin.pemesanan.index'));

        $this->assertDatabaseHas('pemesanan', [
            'id_user' => $customer->id,
            'sumber_masuk' => 'whatsapp',
            'jenis_proyek' => 'Kitchen Set',
            'status_pemesanan' => 'pending',
        ]);
        $this->assertDatabaseHas('status_tracking', [
            'actor_id' => $admin->id,
            'status' => 'pending',
            'catatan' => 'Pesanan dicatat admin dari WhatsApp.',
        ]);
    }

    public function test_admin_can_record_offline_order_and_create_customer(): void
    {
        Notification::fake();
        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => 'smtp.example.com',
            'mail.from.address' => 'noreply@daiku.test',
        ]);
        $admin = $this->user('admin@example.com', 'admin');

        $this->actingAs($admin)->post(route('admin.pemesanan.store'), [
            'customer_mode' => 'new',
            'nama' => 'Pelanggan Kantor',
            'email' => 'pelanggan-kantor@example.com',
            'no_telp' => '085805908809',
            'alamat' => 'Pekanbaru',
            'sumber_masuk' => 'kantor',
            'tanggal_pesan' => now()->toDateString(),
            'jenis_proyek' => 'Interior Ruang Tamu',
        ])->assertRedirect(route('admin.pemesanan.index'));

        $customer = User::where('email', 'pelanggan-kantor@example.com')->firstOrFail();
        $this->assertSame('pelanggan', $customer->role);
        $this->assertDatabaseHas('pemesanan', [
            'id_user' => $customer->id,
            'sumber_masuk' => 'kantor',
            'jenis_proyek' => 'Interior Ruang Tamu',
        ]);
        Notification::assertSentTo($customer, ResetPassword::class);
    }

    public function test_offline_order_reports_when_activation_email_is_not_configured(): void
    {
        Notification::fake();
        config(['mail.default' => 'log']);
        $admin = $this->user('admin@example.com', 'admin');

        $this->actingAs($admin)->post(route('admin.pemesanan.store'), [
            'customer_mode' => 'new',
            'nama' => 'Pelanggan Tanpa Email Aktif',
            'email' => 'mail-log@example.com',
            'no_telp' => '081234567890',
            'sumber_masuk' => 'kantor',
            'tanggal_pesan' => now()->toDateString(),
            'jenis_proyek' => 'Interior Kamar',
        ])->assertRedirect(route('admin.pemesanan.index'))
            ->assertSessionHas('success', fn (string $message) => str_contains($message, 'belum dikirim karena layanan email belum dikonfigurasi'));

        Notification::assertNothingSent();
    }

    private function user(string $email, string $role): User
    {
        return User::create([
            'nama' => ucfirst(strstr($email, '@', true)),
            'email' => $email,
            'password' => Hash::make('password123'),
            'role' => $role,
        ]);
    }
}
