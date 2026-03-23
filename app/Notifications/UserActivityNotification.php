<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class UserActivityNotification extends Notification
{
    protected string $message;

    public function __construct(string $message)
    {
        $this->message = $message;
    }

    public function via(object $notifiable): array
    {
        return ['database']; // store in DB
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'message' => $this->message,
        ];
    }
}