<?php

namespace App\Console\Commands;

use App\Models\Complaint;
use App\Models\User;
use App\Notifications\ComplaintEscalationNotification;
use Illuminate\Console\Command;

class CheckComplaintEscalation extends Command
{
    protected $signature = 'complaints:check-escalation';

    protected $description =
        'Mengecek complaint yang belum ditangani melewati batas waktu';

    public function handle(): int
    {
        $deadline = now()->subDays(2);

        $complaints = Complaint::with([
            'room.pic',
            'reporter',
        ])
        ->where(
            'status_repair',
            Complaint::STATUS_BELUM_DIPERBAIKI
        )
        ->where('created_at', '<=', $deadline)
        ->get();

        $admins = User::whereHas('role', function ($query) {
            $query->where('role_name', 'Admin');
        })->get();

        foreach ($complaints as $complaint) {

            foreach ($admins as $admin) {

                $alreadyNotified = $admin->notifications()
                    ->where(
                        'type',
                        ComplaintEscalationNotification::class
                    )
                    ->where(
                        'data->complaint_id',
                        $complaint->id
                    )
                    ->exists();

                if ($alreadyNotified) {
                    continue;
                }

                $admin->notify(
                    new ComplaintEscalationNotification($complaint)
                );

                $this->info(
                    "Escalation complaint #{$complaint->id} dikirim ke Admin."
                );
            }
        }

        return self::SUCCESS;
    }
}