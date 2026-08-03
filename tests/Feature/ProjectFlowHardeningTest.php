<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Katalog;
use App\Models\Konsultasi;
use App\Models\User;
use Illuminate\Database\QueryException;
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

    public function test_pemesanan_creation_does_not_mutate_authenticated_user_profile(): void
    {
        $user = User::create([
            'nama' => 'Akun Asli',
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
            'role' => 'pelanggan',
            'alamat' => 'Alamat Asli',
            'no_telp' => '081234567890',
        ]);

        $category = Category::create([
            'name' => 'Ruang Tamu',
            'slug' => 'ruang-tamu',
        ]);

        $katalog = Katalog::create([
            'category_id' => $category->id,
            'nama_desain' => 'Desain Aman',
            'deskripsi' => 'Deskripsi',
            'status' => 'published',
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
            'katalog_id' => $katalog->id,
            'terms' => 'on',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('pemesanan', [
            'id_user' => $user->id,
            'katalog_id' => $katalog->id,
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'nama' => 'Akun Asli',
            'email' => 'user@example.com',
            'alamat' => 'Alamat Asli',
            'no_telp' => '081234567890',
        ]);
    }

    public function test_konsultasi_uses_authenticated_identity_instead_of_spoofed_form_values(): void
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
            'nama' => 'Nama Palsu',
            'email' => 'palsu@example.com',
            'no_telp' => '088888888888',
            'jenis_konsultasi' => 'virtual_design',
            'jenis_ruangan' => 'living_room',
            'budget_range' => '10m_25m',
            'timeline' => '1_month',
            'luas_ruangan' => 24,
            'gaya_preferensi' => 'Minimalis',
            'deskripsi_kebutuhan' => 'Butuh masukan desain.',
            'tanggal_konsultasi' => now()->addDay()->toDateString(),
            'waktu_konsultasi' => '10:00',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('konsultasi', [
            'user_id' => $user->id,
            'nama' => 'Konsultan Asli',
            'email' => 'konsultan@example.com',
            'no_telp' => '081200000000',
        ]);
    }

    public function test_database_prevents_two_active_consultations_from_reserving_the_same_slot(): void
    {
        $firstCustomer = User::factory()->create(['role' => 'pelanggan']);
        $secondCustomer = User::factory()->create(['role' => 'pelanggan']);
        $slot = now()->addWeek()->toDateString();

        Konsultasi::create($this->consultationData($firstCustomer, $slot));

        $this->expectException(QueryException::class);
        Konsultasi::create($this->consultationData($secondCustomer, $slot));
    }

    public function test_cancelled_consultation_releases_its_reserved_slot(): void
    {
        $firstCustomer = User::factory()->create(['role' => 'pelanggan']);
        $secondCustomer = User::factory()->create(['role' => 'pelanggan']);
        $slot = now()->addWeek()->toDateString();

        $first = Konsultasi::create($this->consultationData($firstCustomer, $slot));
        $first->update(['status' => Konsultasi::STATUS_CANCELLED]);
        $second = Konsultasi::create($this->consultationData($secondCustomer, $slot));

        $this->assertNull($first->fresh()->active_slot);
        $this->assertNotNull($second->active_slot);
    }

    public function test_customer_attachments_are_private_and_owner_authorized(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        $owner = User::factory()->create(['role' => 'pelanggan']);
        $otherCustomer = User::factory()->create(['role' => 'pelanggan']);

        $this->actingAs($owner)->post(route('pemesanan.store'), [
            'nama' => $owner->nama,
            'email' => $owner->email,
            'no_hp' => '081234567890',
            'alamat' => 'Pekanbaru',
            'jenis_proyek' => 'desain_baru',
            'jenis_bangunan' => 'rumah_tinggal',
            'luas_area' => 42,
            'jumlah_ruangan' => 2,
            'gaya_desain_preferensi' => 'minimalis',
            'warna_dominan' => 'putih',
            'deskripsi_keinginan_desain' => 'Lampiran privat.',
            'upload_denah_foto' => [UploadedFile::fake()->image('denah.jpg')],
            'terms' => 'on',
        ])->assertRedirect();

        $project = \App\Models\Pemesanan::latest('id')->firstOrFail();
        $path = $project->upload_denah_foto[0];
        Storage::disk('local')->assertExists($path);
        Storage::disk('public')->assertMissing($path);

        $this->actingAs($owner)->get(route('pemesanan.attachment', [$project, 0]))->assertOk();
        $this->actingAs($otherCustomer)->get(route('pemesanan.attachment', [$project, 0]))->assertForbidden();
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
