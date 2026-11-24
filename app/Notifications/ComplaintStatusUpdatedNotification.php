<?php

namespace App\Notifications;

use App\Models\Complaint;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ComplaintStatusUpdatedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Complaint $complaint
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $roomName = $this->complaint->room?->room_name ?? 'ruangan';
        $status = $this->complaint->status_repair;

        if ($status === Complaint::STATUS_SUDAH_DIPERBAIKI) {
            $title = 'Complaint Sudah Diperbaiki';
            $message = "Complaint pada {$roomName} sudah selesai diperbaiki.";
        } elseif ($status === Complaint::STATUS_MENUNGGU_SPARE_PART) {
            $title = 'Complaint Menunggu Spare Part';
            $message = "Complaint pada {$roomName} sedang menunggu spare part.";
        } else {
            $title = 'Complaint Belum Diperbaiki';
            $message = "Complaint pada {$roomName} belum selesai diperbaiki.";
        }

        return [
            'type' => 'complaint_status_updated',
            'complaint_id' => $this->complaint->id,
            'title' => $title,
            'message' => $message,
            'room_id' => $this->complaint->room_id,
            'room_name' => $roomName,
            'description' => $this->complaint->description,
            'status_repair' => $status,
        ];
    }
}