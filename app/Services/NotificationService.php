<?php

namespace App\Services;

use App\Events\NotificationCreated;
use App\Models\Notification;

class NotificationService
{
    public function notify(int $userId, string $title, ?string $body = null, ?string $type = null, array $data = []): Notification
    {
        $notification = Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'data' => $data,
        ]);

        // Broadcast event
        event(new NotificationCreated($notification));

        return $notification;
    }
}
