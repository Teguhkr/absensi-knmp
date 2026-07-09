<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class AdminNotification extends Notification
{
    use Queueable;

    protected string $title;
    protected string $message;
    protected string $url;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $title, string $message, string $url = '/admin')
    {
        $this->title = $title;
        $this->message = $message;
        $this->url = $url;
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
        $appUrl = rtrim(config('app.url'), '/');
        $targetUrl = $appUrl . $this->url;

        return (new WebPushMessage)
            ->title($this->title)
            ->icon('/logo-knmp.png')
            ->badge('/logo-knmp.png')
            ->body($this->message)
            ->action('Buka Detail', 'open_detail')
            ->data(['url' => $targetUrl])
            ->vibrate([150, 50, 150]);
    }
}
