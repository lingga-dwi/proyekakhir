<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DaikuNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string $title,
        public readonly string $message,
        public readonly string $url,
        public readonly string $action = 'Lihat detail',
        private readonly bool $mailOnly = false,
    ) {}

    public function via(object $notifiable): array
    {
        return [$this->mailOnly ? 'mail' : 'database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'url' => $this->url,
            'action' => $this->action,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->title.' - Daiku Interior')
            ->greeting('Halo '.($notifiable->nama ?? 'Pelanggan').',')
            ->line($this->message)
            ->action($this->action, url($this->url))
            ->line('Terima kasih telah menggunakan layanan Daiku Interior.');
    }
}
