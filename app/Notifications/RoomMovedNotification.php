<?php

namespace App\Notifications;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RoomMovedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Event $event,
        public string $oldRoomName,
    ) {
    }

    /**
     * Channel yang digunakan.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Data yang disimpan ke database.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'room_moved',

            'event_id' => $this->event->id,

            'title' => 'Ruangan Dipindahkan',

            'message' => 'Ruangan untuk agenda "' .
                $this->event->title .
                '" telah dipindahkan.',

            'old_room' => $this->oldRoomName,

            'new_room' => $this->event->room->room_name,

            'move_reason' => $this->event->move_reason,

            'event_date' => $this->event->event_date?->format('Y-m-d'),

            'start_time' => $this->event->start_time,

            'end_time' => $this->event->end_time,
        ];
    }
}