<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\Event;
use Carbon\Carbon;


class UpdateCompletedEvents extends Command
{
    
    protected $signature = 'events:update-completed';

    protected $description = 'Mengubah status event menjadi Completed setelah 1 menit dari waktu selesai';

    public function handle()
    {
        $now = Carbon::now();
        $cutoffTime = $now->copy()->subMinute();

        $events = Event::where('status', 'Approved')
            ->where(function ($query) use ($now, $cutoffTime) {

                // Acara yang tanggalnya sudah lewat
                $query->whereDate('event_date', '<', $now->toDateString())

                    // Acara hari ini yang sudah lewat 1 menit
                    ->orWhere(function ($query) use ($now, $cutoffTime) {
                        $query->whereDate('event_date', $now->toDateString())
                            ->where('end_time', '<=', $cutoffTime->format('H:i:s'));
                    });
            })
            ->get();

        foreach ($events as $event) {
            $event->update([
                'status' => 'Completed',
            ]);
        }

        $this->info($events->count() . ' event berhasil diperbarui.');

        return Command::SUCCESS;
    }
    
}