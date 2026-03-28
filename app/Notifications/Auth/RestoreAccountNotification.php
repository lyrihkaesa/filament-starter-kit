<?php

declare(strict_types=1);

namespace App\Notifications\Auth;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

final class RestoreAccountNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        if (! ($notifiable instanceof User)) {
            return new MailMessage;
        }

        $url = URL::temporarySignedRoute(
            'account.restore',
            now()->addDays(7),
            ['id' => $notifiable->getKey()]
        );

        return (new MailMessage)
            ->subject('Permohonan Pemulihan Akun')
            ->line('Anda menerima email ini karena kami menerima permintaan pemulihan akun untuk akun Anda.')
            ->action('Pulihkan Akun', (string) $url)
            ->line('Tautan pemulihan ini akan kedaluwarsa dalam 60 menit.')
            ->line('Jika Anda tidak merasa meminta pemulihan akun, abaikan email ini.');
    }
}
