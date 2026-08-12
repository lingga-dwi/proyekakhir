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

    public function admins(string $title, string $message, string $url, string $action = 'Lihat detail'): void
    {
        User::query()->where('role', 'admin')->each(
            fn (User $admin) => $this->send($admin, $title, $message, $url, $action)
        );
    }
}
