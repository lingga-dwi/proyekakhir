<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DaikuNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  array<string, string>  $details  Key-value pairs rendered as a table in the email (e.g. Referensi, Pelanggan, Nominal).
     */
    public function __construct(
        public readonly string $title,
        public readonly string $message,
        public readonly string $url,
        public readonly string $action = 'Lihat detail',
        private readonly bool $mailOnly = false,
        private readonly string $category = 'general',
        private readonly array $details = [],
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
        $greetingName = $notifiable->nama ?? ($this->category === 'general' ? 'Pelanggan' : 'Admin');

        $mail = (new MailMessage)
            ->subject($this->title)
            ->greeting('Halo '.$greetingName.',')
            ->line($this->message);

        foreach ($this->details as $label => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            $mail->line('**'.$label.':** '.$value);
        }

        return $mail
            ->action($this->action, url($this->url))
            ->line('Email ini dikirim otomatis oleh sistem Daiku Interior. Mohon tidak membalas email ini.');
    }

}
