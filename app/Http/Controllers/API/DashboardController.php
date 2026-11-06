<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Room;
use App\Models\Complaint;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $role = $user->role?->role_name;

        $eventQuery = Event::query();
        $complaintQuery = Complaint::query();

        /*
        |--------------------------------------------------------------------------
        | Filter berdasarkan role
        |--------------------------------------------------------------------------
        */

        if ($role === 'PIC') {

            $complaintQuery->whereHas('room', function ($query) use ($user) {
                $query->where('pic_id', $user->id);
            });
        }

        if ($role === 'Guru') {

            $eventQuery->where('user_id', $user->id);

            $complaintQuery->where('reporter_id', $user->id);
        }

        //ruangan yang sedang terboking agenda

        $bookedRoomIds = Event::query()
        ->select('room_id')
        ->whereDate('event_date', now()->toDateString())
        ->whereIn('status', ['Pending', 'Approved'])
        // Agenda harus bertabrakan dengan jam operasional 07.00–15.30
        ->where('start_time', '<', '15:30:00')
        ->where('end_time', '>', '07:00:00');


        /*
        |--------------------------------------------------------------------------
        | Statistik
        |--------------------------------------------------------------------------
        */

        $stats = [

            'rooms_total' => Room::count(),

            // Ruangan tersedia = bukan maintenance dan tidak memiliki agenda bentrok
            'rooms_available' => Room::where('status', '!=', 'Maintenance')
                ->whereNotIn('id', $bookedRoomIds)
                ->count(),

            'events_pending' => (clone $eventQuery)
                ->where('status', 'Pending')
                ->count(),

            'events_today' => (clone $eventQuery)
                ->whereDate('event_date', now()->toDateString())
                ->count(),

            'events_approved' => (clone $eventQuery)
                ->where('status', 'Approved')
                ->count(),

            'complaints_open' => (clone $complaintQuery)
                ->whereIn('status_repair', [
                    'Belum Diperbaiki',
                    'Menunggu Spare Part',
                ])
                ->count(),
        ];

        /*
        |--------------------------------------------------------------------------
        | Statistik khusus Admin
        |--------------------------------------------------------------------------
        */

        if ($role === 'Admin') {

            $stats['users_total'] = User::count();

            $stats['users_pic'] = User::whereHas('role', function ($query) {
                $query->where('role_name', 'PIC');
            })->count();

            $stats['users_guru'] = User::whereHas('role', function ($query) {
                $query->where('role_name', 'Guru');
            })->count();
        }


        /*
        |--------------------------------------------------------------------------
        | Event mendatang
        |--------------------------------------------------------------------------
        */

        $upcomingEvents = (clone $eventQuery)
            ->where('status', 'Approved')
            ->whereDate('event_date', '>=', now()->toDateString())
            ->with('room')
            ->orderBy('event_date')
            ->orderBy('start_time')
            ->limit(5)
            ->get()
            ->map(function ($event) {

                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'date' => $event->event_date,
                    'start_time' => $event->start_time,
                    'end_time' => $event->end_time,
                    'room' => $event->room?->room_name
                        ?? $event->room?->name
                        ?? '-',
                ];
            });


        /*
        |--------------------------------------------------------------------------
        | Complaint terbaru
        |--------------------------------------------------------------------------
        */

        $recentComplaints = (clone $complaintQuery)
            ->with('room')
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($complaint) {

                return [
                    'id' => $complaint->id,

                    'room' => $complaint->room?->room_name
                        ?? $complaint->room?->name
                        ?? '-',

                    'status' => $complaint->status_repair,

                    'description' => $complaint->description,

                    'created_at' => $complaint->created_at,
                ];
            });


        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return response()->json([

            'success' => true,

            'data' => [

                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'role' => $role,
                ],

                'stats' => $stats,

                'upcoming_events' => $upcomingEvents,

                'recent_complaints' => $recentComplaints,
            ],

        ]);
    }
}