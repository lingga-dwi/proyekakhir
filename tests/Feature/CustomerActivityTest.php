<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerActivityTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_open_combined_activity_page_and_switch_tabs(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('aktivitas.saya'))
            ->assertOk()
            ->assertSee('Aktivitas Saya')
            ->assertSee('Pesanan desain')
            ->assertSee('Pesanan')
            ->assertSee('Konsultasi');

        $this->actingAs($user)
            ->get(route('aktivitas.saya', ['tab' => 'konsultasi']))
            ->assertOk()
            ->assertSee('Jadwal konsultasi');
    }

    public function test_legacy_customer_activity_urls_redirect_to_the_matching_tab(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('pesanan.saya'))
            ->assertRedirect('/aktivitas-saya?tab=pesanan');

        $this->actingAs($user)
            ->get(route('konsultasi.saya'))
            ->assertRedirect('/aktivitas-saya?tab=konsultasi');
    }

    public function test_guest_cannot_open_customer_activity_page(): void
    {
        $this->get(route('aktivitas.saya'))
            ->assertRedirect(route('login'));
    }
}
