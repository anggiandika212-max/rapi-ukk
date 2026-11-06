<?php

namespace App\Notifications;

use App\Models\Complaint;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ComplaintEscalationNotification extends Notification
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
        $complaint = $this->complaint;
        $room = $complaint->room;
        $pic = $room?->pic;

        $status = $complaint->status_repair;
        $roomName = $room?->room_name ?? 'Ruangan';

        if ($status === Complaint::STATUS_SUDAH_DIPERBAIKI) {
            $title = 'Complaint Sudah Diperbaiki';
            $message = "Complaint pada {$roomName} telah selesai diperbaiki.";
        } elseif ($status === Complaint::STATUS_MENUNGGU_SPARE_PART) {
            $title = 'Complaint Menunggu Spare Part';
            $message = "Complaint pada {$roomName} sedang menunggu spare part.";
        } else {
            $title = 'Complaint Belum Diperbaiki';
            $message = "Complaint pada {$roomName} masih belum diperbaiki.";
        }

        return [
            'type' => 'complaint_escalation',
            'complaint_id' => $complaint->id,
            'title' => $title,
            'message' => $message,
            'room_id' => $complaint->room_id,
            'room_name' => $roomName,
            'pic_id' => $pic?->id,
            'pic_name' => $pic?->name,
            'description' => $complaint->description,
            'status_repair' => $status,
            'created_at' => $complaint->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}