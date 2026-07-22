<?php

namespace Tests\Feature;

use App\Models\Konsultasi;
use App\Models\Pemesanan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_persisted_project_management_fields(): void
    {
        $admin = $this->user('admin@example.com', 'admin');
        $customer = $this->user('customer@example.com', 'pelanggan');
        $designer = $this->user('designer@example.com', 'designer');

        $project = Pemesanan::create([
            'id_user' => $customer->id,
            'tanggal_pesan' => now()->toDateString(),
            'status_pemesanan' => 'dikonfirmasi',
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

    public function test_admin_cannot_delete_their_own_account(): void
    {
        $admin = $this->user('admin@example.com', 'admin');

        $response = $this->actingAs($admin)->delete(route('admin.users.destroy', $admin));

        $response->assertRedirect()->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
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
            'admin.proyek.index',
            'admin.katalog.index',
            'admin.users.index',
        ] as $route) {
            $this->actingAs($admin)->get(route($route))->assertOk();
        }
    }

    public function test_consultation_is_managed_inside_existing_order_page_and_can_become_project(): void
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
            'deskripsi_kebutuhan' => 'Membutuhkan desain ruang tamu.',
            'tanggal_konsultasi' => now()->addWeek()->toDateString(),
            'waktu_konsultasi' => '10:00',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)->get('/admin/konsultasi')->assertNotFound();
        $this->actingAs($admin)->get(route('admin.pemesanan.index'))
            ->assertOk()
            ->assertSee('Permintaan Konsultasi')
            ->assertSee('Membutuhkan desain ruang tamu.');

        $this->actingAs($admin)->put(route('admin.pemesanan.konsultasi.update', $consultation), [
            'status' => 'confirmed',
        ])->assertRedirect();

        $this->actingAs($admin)->post(route('admin.pemesanan.konsultasi.convert', $consultation))
            ->assertRedirect();

        $consultation->refresh();
        $this->assertNotNull($consultation->pemesanan_id);
        $this->assertDatabaseHas('pemesanan', [
            'id' => $consultation->pemesanan_id,
            'id_user' => $customer->id,
            'status_pemesanan' => 'dikonfirmasi',
            'progress' => 10,
        ]);
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
            ->assertSee('Perlu Ditindaklanjuti')
            ->assertSee('Kitchen Set Prioritas')
            ->assertSee('Pesanan baru diterima')
            ->assertDontSee('Pendapatan Terbayar');
    }

    public function test_designer_dashboard_only_shows_projects_assigned_to_that_designer(): void
    {
        $customer = $this->user('customer@example.com', 'pelanggan');
        $designer = $this->user('designer@example.com', 'designer');
        $otherDesigner = $this->user('other-designer@example.com', 'designer');

        Pemesanan::create([
            'id_user' => $customer->id,
            'designer_id' => $designer->id,
            'tanggal_pesan' => now()->toDateString(),
            'status_pemesanan' => 'sedang_dikerjakan',
            'jenis_proyek' => 'Proyek Milik Desainer',
            'progress' => 45,
        ]);

        Pemesanan::create([
            'id_user' => $customer->id,
            'designer_id' => $otherDesigner->id,
            'tanggal_pesan' => now()->toDateString(),
            'status_pemesanan' => 'sedang_dikerjakan',
            'jenis_proyek' => 'Proyek Desainer Lain',
            'progress' => 70,
        ]);

        $this->actingAs($designer)
            ->get(route('dashboard.designer'))
            ->assertOk()
            ->assertSee('Proyek Milik Desainer')
            ->assertSee('45%')
            ->assertDontSee('Proyek Desainer Lain');
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
