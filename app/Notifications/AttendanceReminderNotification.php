<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class AttendanceReminderNotification extends Notification
{
    use Queueable;

    protected string $type;
    protected string $title;
    protected string $message;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $type, string $title, string $message)
    {
        $this->type = $type;
        $this->title = $title;
        $this->message = $message;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return [WebPushChannel::class];
    }

    /**
     * Get the web push representation of the notification.
     */
    public function toWebPush(object $notifiable, object $notification): WebPushMessage
    {
        // Gunakan APP_URL dari config agar URL selalu cocok dengan environment yang digunakan
        $appUrl = rtrim(config('app.url'), '/');
        $targetUrl = $appUrl . '/pegawai/absensi-saya';

        return (new WebPushMessage)
            ->title($this->title)
            ->icon('/logo-knmp.png')
            ->badge('/logo-knmp.png')
            ->body($this->message)
            ->action('Buka Presensi', 'open_presensi')
            ->data(['url' => $targetUrl])
            ->vibrate([100, 50, 100]);
    }
}
