<?php

namespace Tests\Feature;

use App\Models\Konsultasi;
use App\Models\User;
use App\Notifications\DaikuNotification;
use App\Services\DaikuNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
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

    public function test_admin_broadcast_also_emails_the_fixed_admin_notification_address(): void
    {
        config([
            'services.daiku.email_notifications' => true,
            'services.daiku.admin_notification_email' => 'daikuadmin@gmail.com',
        ]);
        Notification::fake();

        $admin = User::factory()->create(['role' => 'admin']);

        app(DaikuNotificationService::class)->admins(
            'Pesanan baru masuk',
            'Ada pesanan baru dari pelanggan.',
            '/admin/pemesanan',
            'Lihat detail',
            'order'
        );

        Notification::assertSentTo($admin, DaikuNotification::class);
        Notification::assertSentOnDemand(
            DaikuNotification::class,
            fn ($notification, $channels, $notifiable) => $notifiable instanceof AnonymousNotifiable
                && $notifiable->routes['mail'] === 'daikuadmin@gmail.com'
        );
    }

    public function test_admin_broadcast_skips_fixed_email_when_notifications_disabled(): void
    {
        config([
            'services.daiku.email_notifications' => false,
            'services.daiku.admin_notification_email' => 'daikuadmin@gmail.com',
        ]);
        Notification::fake();

        $admin = User::factory()->create(['role' => 'admin']);

        app(DaikuNotificationService::class)->admins('Judul', 'Pesan', '/');

        Notification::assertSentTo($admin, DaikuNotification::class);
        Notification::assertNotSentTo(new AnonymousNotifiable, DaikuNotification::class);
    }

    public function test_admin_broadcast_only_emails_order_and_payment_categories(): void
    {
        config([
            'services.daiku.email_notifications' => true,
            'services.daiku.admin_notification_email' => 'daikuadmin@gmail.com',
        ]);
        Notification::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $service = app(DaikuNotificationService::class);

        $service->admins('Desain menunggu validasi', 'Pesan', '/', 'Lihat detail', 'general');
        $service->admins('Pesanan baru masuk', 'Pesan', '/', 'Lihat detail', 'order');
        $service->admins('Bukti pembayaran masuk', 'Pesan', '/', 'Lihat detail', 'payment');

        // In-app (database) notification fires for every category.
        Notification::assertSentTo($admin, DaikuNotification::class, 3);

        // Only 'order' and 'payment' categories reach the admin's email inbox.
        Notification::assertSentOnDemand(
            DaikuNotification::class,
            fn ($notification, $channels, $notifiable) => $notifiable instanceof AnonymousNotifiable
                && $notification->title === 'Pesanan baru masuk'
        );
        Notification::assertSentOnDemand(
            DaikuNotification::class,
            fn ($notification, $channels, $notifiable) => $notifiable instanceof AnonymousNotifiable
                && $notification->title === 'Bukti pembayaran masuk'
        );
        Notification::assertNotSentTo(
            new AnonymousNotifiable,
            DaikuNotification::class,
            fn ($notification) => $notification->title === 'Desain menunggu validasi'
        );
    }

    public function test_admin_evidence_notification_email_includes_detail_table(): void
    {
        config([
            'services.daiku.email_notifications' => true,
            'services.daiku.admin_notification_email' => 'daikuadmin@gmail.com',
        ]);

        $notification = new DaikuNotification(
            'Bukti pembayaran DP masuk',
            'Pelanggan mengunggah bukti DP untuk proyek DI-1.',
            '/admin/pemesanan',
            'Verifikasi pembayaran',
            true,
            'payment',
            ['Referensi' => 'DI-1', 'Nama Pelanggan' => 'Budi', 'Nominal' => 'Rp 2.000.000']
        );

        $mail = $notification->toMail((object) []);
        $rendered = implode(' ', $mail->introLines);

        $this->assertStringContainsString('Referensi', $rendered);
        $this->assertStringContainsString('DI-1', $rendered);
        $this->assertStringContainsString('Rp 2.000.000', $rendered);
        $this->assertSame('Bukti pembayaran DP masuk', $mail->subject);
    }
}
