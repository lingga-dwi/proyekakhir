<?php

namespace Tests\Feature;

use App\Models\Konsultasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
            ->assertSee('Desainer Ditugaskan');

        $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
    }
}
