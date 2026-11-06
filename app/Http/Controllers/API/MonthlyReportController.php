<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Event;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class MonthlyReportController extends Controller
{
    public function index(Request $request)
    {
        // Endpoint ini hanya boleh diakses Admin yang sudah login melalui Sanctum.
        $user = $request->user();
        $user->loadMissing('role');

        if (($user->role->role_name ?? null) !== 'Admin') {
            return response()->json(['message' => 'Akses laporan hanya tersedia untuk Admin.'], 403);
        }

        $validated = $request->validate([
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'between:2000,2100'],
        ]);

        $month = (int) $validated['month'];
        $year = (int) $validated['year'];

        // Pengaduan dikelompokkan berdasarkan tanggal dibuat (created_at),
        // karena tabel complaints tidak memiliki kolom tanggal pengaduan tersendiri.
        $complaints = Complaint::query()
            ->with(['room:id,room_name', 'reporter:id,name'])
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderByDesc('created_at')
            ->get();

        // Agenda dikelompokkan berdasarkan tanggal pelaksanaan (event_date).
        $events = Event::query()
            ->with(['room:id,room_name', 'user:id,name'])
            ->whereYear('event_date', $year)
            ->whereMonth('event_date', $month)
            ->orderBy('event_date')
            ->orderBy('start_time')
            ->get();

        // Kondisi ruangan adalah kondisi TERKINI, bukan histori pada bulan terpilih.
        // Pengecekan kolom membuat query tetap aman pada instalasi yang migrasinya berbeda.
        $roomQuery = Room::query()->with('pic:id,name');
        if (Schema::hasColumn('rooms', 'is_active')) {
            $roomQuery->where('is_active', true);
        }
        if (Schema::hasColumn('rooms', 'deleted_at')) {
            $roomQuery->whereNull('deleted_at');
        }
        $rooms = $roomQuery->orderBy('room_name')->get();

        $completedComplaints = $complaints->where('status_repair', Complaint::STATUS_SUDAH_DIPERBAIKI)->count();
        $outstandingComplaints = $complaints->count() - $completedComplaints;

        return response()->json([
            'period' => ['month' => $month, 'year' => $year],
            'summary' => [
                'total_complaints' => $complaints->count(),
                'completed_complaints' => $completedComplaints,
                'outstanding_complaints' => $outstandingComplaints,
                'total_events' => $events->count(),
                'total_rooms' => $rooms->count(),
            ],
            'complaints' => $complaints,
            'events' => $events,
            'rooms' => $rooms,
        ]);
    }
}
