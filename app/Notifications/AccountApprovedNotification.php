<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class AccountApprovedNotification extends Notification
{
    use Queueable;

    public function __construct()
    {
        //
    }

    public function via($notifiable): array
    {
        return ['mail']; // Menggunakan jalur Email
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Aktivasi Akun Berhasil - Drone Monitoring App')
            ->greeting('Halo, ' . $notifiable->full_name . '!')
            ->line('Selamat! Akun Anda telah diperiksa dan disetujui oleh Administrator.')
            ->line('Sekarang Anda sudah dapat masuk ke dalam sistem untuk mengelola log penerbangan drone dan operasional lainnya.')
            ->action('Masuk ke Dashboard', url('/admin/login'))
            ->line('Terima kasih telah menggunakan sistem monitoring drone kami!');
    }
}