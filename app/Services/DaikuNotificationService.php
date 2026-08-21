<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\DaikuNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class DaikuNotificationService
{
    public function send(User $user, string $title, string $message, string $url, string $action = 'Lihat detail'): void
    {
        $user->notify(new DaikuNotification($title, $message, $url, $action));

        if (! config('services.daiku.email_notifications', false) || ! $user->email) {
            return;
        }

        try {
            Notification::route('mail', $user->email)
                ->notify(new DaikuNotification($title, $message, $url, $action, true));
        } catch (\Throwable $exception) {
            // Kegagalan SMTP tidak boleh membatalkan perubahan status proyek.
            Log::warning('Email notification could not be delivered', [
                'user_id' => $user->id,
                'title' => $title,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * Categories that are important enough to justify emailing the admin
     * inbox. Every other admin event still appears in-app only, so the
     * inbox is not flooded with routine workflow updates.
     */
    private const EMAILABLE_CATEGORIES = ['order', 'payment'];

    public function admins(string $title, string $message, string $url, string $action = 'Lihat detail', string $category = 'general', array $details = []): void
    {
        $shouldEmail = in_array($category, self::EMAILABLE_CATEGORIES, true);

        User::query()->where('role', 'admin')->each(function (User $admin) use ($title, $message, $url, $action, $category, $details, $shouldEmail): void {
            // In-app notification always fires; the per-admin email only
            // goes out for categories important enough to justify an inbox
            // interruption (order/payment), matching the fixed-address rule below.
            $admin->notify(new DaikuNotification($title, $message, $url, $action));

            if (! $shouldEmail || ! config('services.daiku.email_notifications', false) || ! $admin->email) {
                return;
            }

            try {
                Notification::route('mail', $admin->email)
                    ->notify(new DaikuNotification($title, $message, $url, $action, true, $category, $details));
            } catch (\Throwable $exception) {
                Log::warning('Email notification could not be delivered', [
                    'user_id' => $admin->id,
                    'title' => $title,
                    'error' => $exception->getMessage(),
                ]);
            }
        });

        if (! $shouldEmail) {
            return;
        }

        $fixedEmail = config('services.daiku.admin_notification_email');
        if (! $fixedEmail || ! config('services.daiku.email_notifications', false)) {
            return;
        }

        try {
            Notification::route('mail', $fixedEmail)
                ->notify(new DaikuNotification($title, $message, $url, $action, true, $category, $details));
        } catch (\Throwable $exception) {
            Log::warning('Admin notification email could not be delivered', [
                'email' => $fixedEmail,
                'title' => $title,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
