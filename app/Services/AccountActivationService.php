<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Password;

class AccountActivationService
{
    public function isDeliveryConfigured(): bool
    {
        $mailer = (string) config('mail.default');

        if ($mailer === '' || in_array($mailer, ['array', 'log'], true)) {
            return false;
        }

        if ($mailer !== 'smtp') {
            return true;
        }

        $host = (string) config('mail.mailers.smtp.host');
        $from = (string) config('mail.from.address');

        return $host !== ''
            && $host !== '127.0.0.1'
            && $from !== ''
            && $from !== 'hello@example.com';
    }

    public function send(User $customer): bool
    {
        if (! $this->isDeliveryConfigured()) {
            return false;
        }

        return Password::sendResetLink(['email' => $customer->email]) === Password::RESET_LINK_SENT;
    }
}
