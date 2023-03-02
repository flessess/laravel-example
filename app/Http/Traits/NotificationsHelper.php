<?php

namespace App\Http\Traits;

use App\Models\DatabaseNotification;

/**
 * Trait NotificationsHelper.
 *
 * @package App\Http\Traits
 */
trait NotificationsHelper
{
    /**
     * @param array array
     *
     * @return void
     */
    public function saveNotificationFromEvent($event)
    {
        DatabaseNotification::create([
            'id' => $event->notifId,
            'type' => $event->notificationClass,
            'notifiable_id' => $event->user->getKey(),
            'notifiable_type' => get_class($event->user),
            'data' => [
                'eventType' => $event->eventType,
                'requestTime' => $event->requestTime,
                'url' => $event->url,
                'filesize' => $event->filesize,
                'messageType' => $event->messageType,
            ],
        ]);
    }
}
