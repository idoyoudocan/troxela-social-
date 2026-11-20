<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/** Generic database notification for likes, comments, follows and messages. */
class UserNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $message,
        public string $url,
        public string $kind,
    ) {}

    public function via(object $notifiable): array { return ['database']; }

    public function toArray(object $notifiable): array
    {
        return ['message' => $this->message, 'url' => $this->url, 'kind' => $this->kind];
    }
}
