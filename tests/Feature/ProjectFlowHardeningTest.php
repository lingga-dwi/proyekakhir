<?php

namespace Tests\Feature;

use App\Models\Konsultasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProjectFlowHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_and_designer_cannot_use_customer_only_routes(): void
    {
        foreach (['admin', 'designer'] as $role) {
            $user = User::factory()->create(['role' => $role]);

            $this->actingAs($user)->get(route('aktivitas.saya'))->assertForbidden();
            $this->actingAs($user)->get(route('pemesanan.create'))->assertForbidden();
            $this->actingAs($user)->get(route('konsultasi.create'))->assertForbidden();
            $this->actingAs($user)->post(route('pemesanan.store'))->assertForbidden();
            $this->actingAs($user)->post(route('konsultasi.store'))->assertForbidden();
        }
    }

    public function test_consultation_call_to_action_matches_the_authenticated_role(): void
    {
        $customer = User::factory()->create(['role' => 'pelanggan']);
        $admin = User::factory()->create(['role' => 'admin']);
        $designer = User::factory()->create(['role' => 'designer']);

        $this->actingAs($customer)->get(route('konsultasi.index'))
            ->assertOk()
            ->assertSee('Buat Pesanan')
            ->assertSee('href="'.route('konsultasi.create').'"', false);

        $this->actingAs($admin)->get(route('konsultasi.index'))
            ->assertOk()
            ->assertSee('Kelola Pesanan')
            ->assertSee('href="'.route('admin.pemesanan.index').'"', false)
            ->assertDontSee('href="'.route('konsultasi.create').'"', false);

        $this->actingAs($designer)->get(route('konsultasi.index'))
            ->assertOk()
            ->assertSee('Dashboard Desainer')
            ->assertSee('href="'.route('dashboard.designer').'"', false)
            ->assertDontSee('href="'.route('konsultasi.create').'"', false);
    }

    public function test_direct_pemesanan_redirects_to_consultation_without_mutating_profile(): void
    {
        $user = User::create([
            'nama' => 'Akun Asli',
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
            'role' => 'pelanggan',
            'alamat' => 'Alamat Asli',
            'no_telp' => '081234567890',
        ]);

        $response = $this->actingAs($user)->post(route('pemesanan.store'), [
            'nama' => 'Nama Di Form',
            'email' => 'berubah@example.com',
            'no_hp' => '089999999999',
            'alamat' => 'Alamat Baru',
            'jenis_proyek' => 'desain_baru',
            'jenis_bangunan' => 'rumah_tinggal',
            'luas_area' => 42,
            'jumlah_ruangan' => 6,
            'gaya_desain_preferensi' => 'modern_minimalis',
            'warna_dominan' => 'putih',
            'deskripsi_keinginan_desain' => 'Butuh desain yang bersih.',
            'terms' => 'on',
        ]);

        $response->assertRedirect(route('konsultasi.create'));
        $this->assertDatabaseCount('pemesanan', 0);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'nama' => 'Akun Asli',
            'email' => 'user@example.com',
            'alamat' => 'Alamat Asli',
            'no_telp' => '081234567890',
        ]);
    }

    public function test_konsultasi_stores_request_details_and_updates_contact_profile(): void
    {
        $user = User::create([
            'nama' => 'Konsultan Asli',
            'email' => 'konsultan@example.com',
            'password' => Hash::make('password123'),
            'role' => 'pelanggan',
            'alamat' => 'Jakarta',
            'no_telp' => '081200000000',
        ]);

        $response = $this->actingAs($user)->post(route('konsultasi.store'), [
            'nama' => 'Nama Pemohon',
            'email' => 'pemohon@example.com',
            'no_telp' => '088888888888',
            'alamat' => 'Pekanbaru',
            'jenis_konsultasi' => 'virtual_design',
            'jenis_ruangan' => 'living_room',
            'budget_range' => '10m_25m',
            'luas_ruangan' => 24,
            'deskripsi_kebutuhan' => 'Butuh masukan desain.',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('konsultasi', [
            'user_id' => $user->id,
            'nama' => 'Nama Pemohon',
            'email' => 'pemohon@example.com',
            'no_telp' => '088888888888',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'nama' => 'Nama Pemohon',
            'email' => 'konsultan@example.com',
            'no_telp' => '088888888888',
            'alamat' => 'Pekanbaru',
        ]);
    }

    public function test_admin_can_assign_same_designer_to_multiple_consultations(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $designer = User::factory()->create(['role' => 'designer']);
        $firstCustomer = User::factory()->create(['role' => 'pelanggan']);
        $secondCustomer = User::factory()->create(['role' => 'pelanggan']);
        $slot = now()->addWeek()->toDateString();

        $first = Konsultasi::create($this->consultationData($firstCustomer, $slot));
        $second = Konsultasi::create($this->consultationData($secondCustomer, $slot));

        $this->actingAs($admin)->post(route('admin.pemesanan.konsultasi.accept', $first), [
            'designer_id' => $designer->id,
        ])->assertRedirect();
        $this->actingAs($admin)->post(route('admin.pemesanan.konsultasi.accept', $second), [
            'designer_id' => $designer->id,
        ])->assertRedirect();

        $this->assertSame($designer->id, $first->fresh()->designer_id);
        $this->assertSame($designer->id, $second->fresh()->designer_id);
        $this->assertSame(Konsultasi::STATUS_CONFIRMED, $first->fresh()->status);
        $this->assertSame(Konsultasi::STATUS_CONFIRMED, $second->fresh()->status);
        $this->assertNull($first->fresh()->active_slot);
        $this->assertNull($second->fresh()->active_slot);
    }

    public function test_admin_must_choose_a_designer_when_accepting_a_consultation(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'pelanggan']);
        $consultation = Konsultasi::create(
            $this->consultationData($customer, now()->addWeek()->toDateString())
        );

        $this->actingAs($admin)
            ->post(route('admin.pemesanan.konsultasi.accept', $consultation))
            ->assertSessionHasErrors('designer_id');

        $consultation->refresh();
        $this->assertSame(Konsultasi::STATUS_PENDING, $consultation->status);
        $this->assertNull($consultation->accepted_at);
        $this->assertNull($consultation->designer_id);
    }

    public function test_admin_can_change_the_assigned_consultation_designer(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $firstDesigner = User::factory()->create(['role' => 'designer']);
        $secondDesigner = User::factory()->create(['role' => 'designer']);
        $firstCustomer = User::factory()->create(['role' => 'pelanggan']);
        $slot = now()->addWeek()->toDateString();

        $first = Konsultasi::create($this->consultationData($firstCustomer, $slot));

        $this->actingAs($admin)->post(route('admin.pemesanan.konsultasi.accept', $first), [
            'designer_id' => $firstDesigner->id,
        ])->assertRedirect();
        $this->actingAs($admin)->put(route('admin.pemesanan.konsultasi.assign', $first), [
            'designer_id' => $secondDesigner->id,
        ])->assertRedirect();

        $this->assertSame($secondDesigner->id, $first->fresh()->designer_id);
        $this->assertSame(Konsultasi::STATUS_CONFIRMED, $first->fresh()->status);
        $this->assertNull($first->fresh()->active_slot);
    }

    public function test_customer_attachments_are_private_and_owner_authorized(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create(['role' => 'pelanggan']);
        $otherCustomer = User::factory()->create(['role' => 'pelanggan']);

        $this->actingAs($owner)->post(route('konsultasi.store'), [
            'nama' => $owner->nama,
            'email' => $owner->email,
            'no_telp' => '081234567890',
            'alamat' => 'Pekanbaru',
            'jenis_konsultasi' => 'virtual_design',
            'jenis_ruangan' => 'living_room',
            'budget_range' => '10m_25m',
            'luas_ruangan' => 42,
            'deskripsi_kebutuhan' => 'Lampiran privat.',
            'attachments' => [UploadedFile::fake()->image('denah.jpg')],
        ])->assertRedirect();

        $consultation = Konsultasi::latest('id')->firstOrFail();
        $path = $consultation->attachments[0]['path'];
        $this->assertSame('denah.jpg', $consultation->attachments[0]['name']);
        Storage::disk('local')->assertExists($path);

        $this->actingAs($owner)->get(route('konsultasi.attachment.download', [$consultation, 0]))->assertOk();
        $this->actingAs($otherCustomer)->get(route('konsultasi.attachment.download', [$consultation, 0]))->assertForbidden();
    }

    private function consultationData(User $user, string $date): array
    {
        return [
            'user_id' => $user->id,
            'nama' => $user->nama,
            'email' => $user->email,
            'no_telp' => '08123456789',
            'jenis_konsultasi' => 'free_consultation',
            'jenis_ruangan' => 'living_room',
            'budget_range' => '10m_25m',
            'timeline' => '1_month',
            'deskripsi_kebutuhan' => 'Membutuhkan konsultasi desain.',
            'tanggal_konsultasi' => $date,
            'waktu_konsultasi' => '10:00',
            'status' => Konsultasi::STATUS_PENDING,
        ];
    }
}
