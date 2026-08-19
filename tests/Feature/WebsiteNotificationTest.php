<?php

namespace Tests\Feature;

use App\Models\Konsultasi;
use App\Models\User;
use App\Notifications\DaikuNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebsiteNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_consultation_events_create_role_specific_website_notifications(): void
    {
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
            'budget_range' => '10m_25m',
            'luas_ruangan' => 24,
            'deskripsi_kebutuhan' => 'Ruang keluarga minimalis.',
        ])->assertRedirect();

        $this->assertSame('Permintaan konsultasi baru', $admin->fresh()->notifications()->first()->data['title']);

        $consultation = Konsultasi::firstOrFail();
        $this->actingAs($admin)->post(route('admin.pemesanan.konsultasi.accept', $consultation), [
            'designer_id' => $designer->id,
        ])->assertRedirect();

        $this->assertSame('Permintaan diterima dan desainer ditugaskan', $customer->fresh()->notifications()->first()->data['title']);
        $this->assertSame('Konsultasi baru ditugaskan', $designer->fresh()->notifications()->first()->data['title']);
    }

    public function test_user_can_open_own_notification_and_mark_all_as_read(): void
    {
        $user = User::factory()->create(['role' => 'pelanggan']);
        $other = User::factory()->create(['role' => 'pelanggan']);
        $user->notify(new DaikuNotification('Pembaruan proyek', 'Ada pembaruan.', '/pesanan-saya'));
        $other->notify(new DaikuNotification('Notifikasi lain', 'Bukan milik pengguna.', '/pesanan-saya'));

        $notification = $user->notifications()->firstOrFail();
        $otherNotification = $other->notifications()->firstOrFail();

        $this->actingAs($user)->get(route('notifications.open', $notification->id))
            ->assertRedirect('/pesanan-saya');
        $this->assertNotNull($notification->fresh()->read_at);

        $this->actingAs($user)->get(route('notifications.open', $otherNotification->id))->assertNotFound();

        $user->notify(new DaikuNotification('Pembaruan kedua', 'Ada pembaruan lagi.', '/pesanan-saya'));
        $this->actingAs($user)->post(route('notifications.read-all'))->assertRedirect();
        $this->assertSame(0, $user->fresh()->unreadNotifications()->count());
    }

    public function test_notification_supports_database_and_optional_mail_channels(): void
    {
        $databaseNotification = new DaikuNotification('Judul', 'Pesan', '/');
        $mailNotification = new DaikuNotification('Judul', 'Pesan', '/', 'Lihat', true);

        $this->assertSame(['database'], $databaseNotification->via(new \stdClass));
        $this->assertSame(['mail'], $mailNotification->via(new \stdClass));
    }
}
