<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Katalog;
use App\Models\Konsultasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProjectFlowHardeningTest extends TestCase
{
    use RefreshDatabase;

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
            'kategori' => 'Ruang Tamu',
            'deskripsi' => 'Deskripsi',
            'harga_estimasi' => 25000000,
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

    public function test_admin_dashboard_consultation_stat_comes_from_konsultasi_table(): void
    {
        $admin = User::create([
            'nama' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        $customer = User::create([
            'nama' => 'Customer',
            'email' => 'customer@example.com',
            'password' => Hash::make('password123'),
            'role' => 'pelanggan',
        ]);

        Konsultasi::create([
            'user_id' => $customer->id,
            'nama' => $customer->nama,
            'email' => $customer->email,
            'no_telp' => '081211111111',
            'jenis_konsultasi' => 'virtual_design',
            'jenis_ruangan' => 'office',
            'budget_range' => '25m_50m',
            'timeline' => '3_months',
            'deskripsi_kebutuhan' => 'Perlu konsultasi kantor.',
            'tanggal_konsultasi' => now()->addDays(2)->toDateString(),
            'waktu_konsultasi' => '14:00',
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($admin)->get(route('dashboard.admin'));

        $response->assertOk();
        $response->assertViewHas('stats', fn (array $stats) => $stats['consultations'] === 1);
    }
}
