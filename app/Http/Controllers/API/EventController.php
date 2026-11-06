<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Notifications\RoomMovedNotification;
use App\Models\Room;

class EventController extends Controller
{
    /**
     * Display a listing of the events.
     */
    public function index(Request $request){
        
    $user = $request->user();

    $query = Event::with(['room', 'user', 'approver']);
    
    if ($user->role?->role_name !== 'Admin') {
        $query->where('user_id', $user->id);
    }

        /*
        ==========================
        SEARCH
        ==========================
        */

        if ($request->filled('search')) {

            $search = $request->search;

            $query->where(function ($q) use ($search) {

                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");

            });
        }

        /*
        ==========================
        FILTER
        ==========================
        */

        if ($request->filled('room_id')) {
            $query->where('room_id', $request->room_id);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('event_date')) {
            $query->where('event_date', $request->event_date);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        /*
        ==========================
        SORTING
        ==========================
        */

        $sortBy = $request->get('sort_by', 'id');
        $sortOrder = strtolower(
            $request->get('sort_order', 'desc')
        );

        $allowedSort = [
            'id',
            'title',
            'event_date',
            'start_time',
            'end_time',
            'status',
            'created_at',
        ];

        if (
            in_array($sortBy, $allowedSort) &&
            in_array($sortOrder, ['asc', 'desc'])
        ) {
            $query->orderBy($sortBy, $sortOrder);
        }

        /*
        ==========================
        PAGINATION
        ==========================
        */

        $perPage = $request->get('per_page', 10);

        $events = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Data event berhasil diambil',
            'data' => EventResource::collection($events),
            'pagination' => [
                'current_page' => $events->currentPage(),
                'last_page' => $events->lastPage(),
                'per_page' => $events->perPage(),
                'total' => $events->total(),
            ],
        ]);
    }
        
    /**
     * Menampilkan event yang dibuat oleh user yang sedang login.
     */
    public function myEvents(Request $request)
    {
            $user = $request->user();

            $query = Event::with(['room', 'user', 'approver'])
                ->where('user_id', $user->id);

            // Pencarian berdasarkan judul, deskripsi, atau status.
            if ($request->filled('search')) {
                $search = $request->search;

                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%");
                });
            }

            // Filter berdasarkan ruangan.
            if ($request->filled('room_id')) {
                $query->where('room_id', $request->room_id);
            }

            // Filter berdasarkan tanggal.
            if ($request->filled('event_date')) {
                $query->whereDate('event_date', $request->event_date);
            }

            // Filter berdasarkan status.
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $allowedSort = [
                'id',
                'title',
                'event_date',
                'start_time',
                'end_time',
                'status',
                'created_at',
            ];

            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = strtolower($request->get('sort_order', 'desc'));

            if (!in_array($sortBy, $allowedSort, true)) {
                $sortBy = 'created_at';
            }

            if (!in_array($sortOrder, ['asc', 'desc'], true)) {
                $sortOrder = 'desc';
            }

            $query->orderBy($sortBy, $sortOrder);

            $perPage = $request->integer('per_page', 10);
            $perPage = max(1, min($perPage, 100));

            $events = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Data event milik Anda berhasil diambil.',
                'data' => EventResource::collection($events->getCollection()),
                'pagination' => [
                    'current_page' => $events->currentPage(),
                    'last_page' => $events->lastPage(),
                    'per_page' => $events->perPage(),
                    'total' => $events->total(),
                ],
            ]);
    }
    public function store(Request $request)
    {
        $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'event_date' => 'required|date|after:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'status' => 'nullable|in:Pending,Approved,Rejected,Cancelled,Completed',
        ],[
            'event_date.after' => 'Peminjaman ruangan harus diajukan minimal satu hari sebelum tanggal penggunaan.',
        ]);
        $roomMaintenance = Room::where('id', $request->room_id)
            ->where('status', 'Maintenance')
            ->exists();

        if ($roomMaintenance) {
            return response()->json([
                'success' => false,
                'message' => 'Ruangan sedang dalam perbaikan dan tidak dapat dipinjam.',
            ], 422);
        }
        //cek jadwal yang masih memblokir ruangan
        $conflict = Event::where('room_id', $request->room_id)
            ->whereDate('event_date', $request->event_date)
            ->whereIn('status', ['Pending', 'Approved'])
            ->where('start_time', '<', $request->end_time)
            ->where('end_time', '>', $request->start_time)
            ->exists();

        if ($conflict) {
            return response()->json([
                'success' => false,
                'message' => 'Ruangan sudah digunakan pada waktu tersebut. Silakan pilih ruangan atau waktu lain.',
            ], 422);
        }

        $event = Event::create([
            'room_id' => $request->room_id,

            // ID user diambil dari token Sanctum
            'user_id' => $request->user()->id,

            'title' => $request->title,
            'description' => $request->description,
            'event_date' => $request->event_date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'status' => 'Pending',
        ]);

        $event->load(['room', 'user']);

        return response()->json([
            'success' => true,
            'message' => 'Event berhasil ditambahkan',
            'data' => new EventResource($event),
        ], 201);
    }

    /**
     * Display the specified event.
     */
    public function show(string $id)
    {
        $event = Event::with(['room', 'user', 'approver'])->find($id);

        if (!$event) {

            return response()->json([
                'success' => false,
                'message' => 'Event tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail event',
            'data' => new EventResource($event),
        ]);
    }

    /**
     * Update the specified event.
     */
    public function update(Request $request, string $id)
    {
        $event = Event::find($id);

        if (!$event) {
            return response()->json([
                'success' => false,
                'message' => 'Event tidak ditemukan.',
            ], 404);
        }

        $user = $request->user();
        $role = $user->role?->role_name;

        /*
        ==========================================
        EVENT YANG SUDAH DIPROSES TIDAK BOLEH
        DIUBAH MELALUI ENDPOINT UPDATE
        ==========================================

        Event Approved sudah menjadi jadwal resmi.
        Perubahan ruangan harus menggunakan endpoint
        khusus moveRoom() milik Admin, sedangkan
        perubahan status diproses melalui approval().
        */
        if ($event->status !== 'Pending') {
            return response()->json([
                'success' => false,
                'message' => 'Event dengan status ' . $event->status . ' tidak dapat diedit. Event yang sudah Approved harus menggunakan proses perubahan khusus.',
            ], 422);
        }

        /*
        ==========================================
        GURU HANYA BOLEH MENGEDIT EVENT MILIKNYA
        ==========================================
        */
        if (
            $role === 'Guru' &&
            $event->user_id !== $user->id
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk mengubah event ini.',
            ], 403);
        }

        /*
        ==========================================
        GURU TIDAK BOLEH MENGUBAH STATUS EVENT
        ==========================================
        */
        if (
            $role === 'Guru' &&
            $request->has('status')
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Guru tidak diperbolehkan mengubah status event.',
            ], 403);
        }

        /*
        ==========================================
        VALIDASI DATA EVENT
        ==========================================
        */
        $validated = $request->validate([
            'room_id' => [
                'required',
                'integer',
                'exists:rooms,id',
            ],

            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'event_date' => [
                'required',
                'date',
                'after:today',
            ],

            'start_time' => [
                'required',
                'date_format:H:i',
            ],

            'end_time' => [
                'required',
                'date_format:H:i',
                'after:start_time',
            ],
        ]);

        /*
        ==========================================
        CEK BENTROK JADWAL
        ==========================================
        */
        $roomMaintenance = Room::where('id', $validated['room_id'])
            ->where('status', 'Maintenance')
            ->exists();

        if ($roomMaintenance) {
            return response()->json([
                'success' => false,
                'message' => 'Ruangan sedang dalam perbaikan dan tidak dapat digunakan.',
            ], 422);
        }

        $conflict = Event::where('room_id', $validated['room_id'])
            ->whereDate('event_date', $validated['event_date'])
            ->where('id', '!=', $event->id)
            ->whereIn('status', ['Pending', 'Approved'])
            ->where('start_time', '<', $validated['end_time'])
            ->where('end_time', '>', $validated['start_time'])
            ->exists();

        if ($conflict) {
            return response()->json([
                'success' => false,
                'message' => 'Jadwal bentrok dengan event lain. Silakan pilih waktu atau ruangan lain.',
            ], 422);
        }

        /*
        ==========================================
        UPDATE DATA
        ==========================================
        */
        $event->update([
            'room_id' => $validated['room_id'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'event_date' => $validated['event_date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
        ]);

        $event->load(['room', 'user']);

        return response()->json([
            'success' => true,
            'message' => 'Event berhasil diperbarui.',
            'data' => new EventResource($event),
        ]);
    }

    /**
     * Remove the specified event.
     */
    public function destroy(Request $request, string $id)
    {
        $event = Event::find($id);

        if (!$event) {

            return response()->json([
                'success' => false,
                'message' => 'Event tidak ditemukan',
            ], 404);
        }
        /*
        ==========================================
        EVENT YANG SUDAH DISETUJUI TIDAK BOLEH
        DIHAPUS MELALUI ENDPOINT DELETE
        ==========================================

        Event Approved sudah menjadi jadwal resmi.
        */
        if (in_array($event->status, ['Approved', 'Completed', 'Cancelled'])) {
            return response()->json([
                'success' => false,
                'message' => 'Event dengan status ' . $event->status . ' tidak dapat dihapus.',
            ], 422);
        }

        //guru hanya boleh menghapus even miliknya sendiri
        if (
            $request->user()->role?->role_name === 'Guru' &&
            $event->user_id !== $request->user()->id
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk mengubah event ini.',
            ], 403);
        }

        $event->delete();

        return response()->json([
            'success' => true,
            'message' => 'Event berhasil dihapus',
        ]);
    }
    public function moveRoom(Request $request, string $id)
    {
        $event = Event::with(['room', 'user'])->find($id);

        if (!$event) {
            return response()->json([
                'success' => false,
                'message' => 'Event tidak ditemukan.',
            ], 404);
        }

        /*
        ============================
        VALIDASI STATUS EVENT
        ============================
        */

        if (in_array($event->status, [
            'Rejected',
            'Cancelled',
            'Completed'
        ])) {
            return response()->json([
                'success' => false,
                'message' => 'Event dengan status tersebut tidak dapat dipindahkan.',
            ], 422);
        }

        /*
        ============================
        VALIDASI INPUT
        ============================
        */

        $validated = $request->validate([
            'room_id' => [
                'required',
                'integer',
                'exists:rooms,id',
            ],

            'move_reason' => [
                'required',
                'string',
                'max:1000',
            ],
        ]);

        /*
        ============================
        ROOM SAMA
        ============================
        */

        if ((int) $validated['room_id'] === (int) $event->room_id) {
            return response()->json([
                'success' => false,
                'message' => 'Room baru tidak boleh sama dengan room saat ini.',
            ], 422);
        }

        /*
        ============================
        CEK KONFLIK
        ============================
        */

        $roomMaintenance = Room::where('id', $validated['room_id'])
            ->where('status', 'Maintenance')
            ->exists();

        if ($roomMaintenance) {
            return response()->json([
                'success' => false,
                'message' => 'Ruangan tujuan sedang dalam perbaikan.',
            ], 422);
        }

        $conflict = Event::where('room_id', $validated['room_id'])
            ->whereDate('event_date', $event->event_date)
            ->where('id', '!=', $event->id)
            ->whereIn('status', ['Pending', 'Approved'])
            ->where('start_time', '<', $event->end_time)
            ->where('end_time', '>', $event->start_time)
            ->exists();

        if ($conflict) {
            return response()->json([
                'success' => false,
                'message' => 'Ruangan tujuan sudah digunakan pada waktu tersebut.',
            ], 422);
        }

        /*
        ============================
        SIMPAN ROOM LAMA
        ============================
        */

        $oldRoomId = $event->room_id;
        $oldRoomName = $event->room->room_name;

        /*
        ============================
        UPDATE EVENT
        ============================
        */

        $event->update([
            'room_id' => $validated['room_id'],
            'move_reason' => $validated['move_reason'],
        ]);

        /*
        ============================
        LOAD DATA TERBARU
        ============================
        */

        $event->load([
            'room',
            'user',
            'approver',
        ]);

        /*
        ============================
        KIRIM NOTIFIKASI
        ============================
        */

        $event->user->notify(
            new RoomMovedNotification(
                $event,
                $oldRoomName
            )
        );

        /*
        ============================
        RESPONSE
        ============================
        */

        return response()->json([
            'success' => true,
            'message' => 'Room event berhasil dipindahkan.',
            'data' => new EventResource($event),

            'move' => [
                
                'old_room' => [
                    'id' => $oldRoomId,
                    'room_name' => $oldRoomName,
                ],

                'new_room' => [
                    'id' => $event->room->id,
                    'room_name' => $event->room->room_name,
                ],

                'move_reason' => $event->move_reason,
            ],
        ]);
    }
    public function approval(Request $request, string $id)
    {
        $event = Event::find($id);

        if (!$event) {
            return response()->json([
                'success' => false,
                'message' => 'Event tidak ditemukan.',
            ], 404);
        }

        $validated = $request->validate([
            'status' => [
                'required',
                Rule::in(['Approved', 'Rejected']),
            ],
        ]);

        /*
        ==========================================
        HANYA EVENT PENDING YANG BISA DI-APPROVE
        ==========================================
        */
        if ($event->status !== 'Pending') {
            return response()->json([
                'success' => false,
                'message' => 'Event hanya dapat diproses ketika status masih Pending.',
            ], 422);
        }

        /*
        ==========================================
        CEK BENTROK SAAT APPROVAL
        ==========================================
        */
        if ($validated['status'] === 'Approved') {

        $conflict = Event::where('room_id', $event->room_id)
            ->whereDate('event_date', $event->event_date)
            ->where('id', '!=', $event->id)
            ->whereIn('status', ['Pending', 'Approved'])
            ->where('start_time', '<', $event->end_time)
            ->where('end_time', '>', $event->start_time)
            ->exists();

        if ($conflict) {
            return response()->json([
                'success' => false,
                'message' => 'Event tidak dapat disetujui karena jadwal ruangan bertabrakan dengan pengajuan lain.',
            ], 422);
        }
        }

        $event->update([
            'status' => $validated['status'],
            'approved_by' => $validated['status'] === 'Approved'
                ? $request->user()->id
                : null,
        ]);

        $event->load(['room', 'user', 'approver']);

        return response()->json([
            'success' => true,
            'message' => $validated['status'] === 'Approved'
                ? 'Event berhasil disetujui.'
                : 'Event berhasil ditolak.',
            'data' => new EventResource($event),
        ]);
    }
    public function calendarInternal(Request $request)
    {
        $request->validate([
            'start' => ['nullable', 'date'],
            'end' => ['nullable', 'date', 'after:start'],
        ]);

        $query = Event::with('room');

        if ($request->filled('start')) {
            $query->whereDate('event_date', '>=', $request->start);
        }

        if ($request->filled('end')) {
            $query->whereDate('event_date', '<', $request->end);
        }

        $events = $query
            ->orderBy('event_date')
            ->orderBy('start_time')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $events->map(function ($event) {
                $date = $event->event_date->format('Y-m-d');

                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'start' => $date . 'T' . $event->start_time,
                    'end' => $date . 'T' . $event->end_time,
                    'status' => $event->status,
                    'room' => [
                        'id' => $event->room?->id,
                        'name' => $event->room?->room_name,
                    ],
                ];
            })->values(),
        ]);
    }
    public function publicCalendar(Request $request)
    {
        $request->validate([
            'start' => ['nullable', 'date'],
            'end' => ['nullable', 'date', 'after_or_equal:start'],
        ]);

        $query = Event::with('room')
            ->whereIn('status', [
                'Approved',
                'Completed',
            ]);

        if ($request->filled('start')) {
            $query->whereDate('event_date', '>=', $request->start);
        }

        if ($request->filled('end')) {
            $query->whereDate('event_date', '<=', $request->end);
        }

        $events = $query
            ->orderBy('event_date')
            ->orderBy('start_time')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $events->map(function ($event) {
                $date = $event->event_date->format('Y-m-d');

                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'description' => $event->description,
                    'start' => $date . 'T' . $event->start_time,
                    'end' => $date . 'T' . $event->end_time,
                    'room' => [
                        'id' => $event->room?->id,
                        'name' => $event->room?->room_name,
                    ],
                ];
            })->values(),
        ]);
    }
    public function availableRooms(Request $request)
    {
        $validated = $request->validate([
            'event_date' => ['required', 'date', 'after:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => [
                'required',
                'date_format:H:i',
                'after:start_time',
            ],
            'exclude_event_id' => [
                'nullable',
                'integer',
                'exists:events,id',
            ],
        ], [
            'event_date.after' => 'Peminjaman ruangan harus diajukan minimal satu hari sebelum tanggal penggunaan.',
        ]);

        $conflictQuery = Event::query()
            ->select('room_id')
            ->whereDate('event_date', $validated['event_date'])
            ->whereIn('status', ['Pending', 'Approved'])
            ->where('start_time', '<', $validated['end_time'])
            ->where('end_time', '>', $validated['start_time']);

        // Saat mengedit event, abaikan event yang sedang diedit.
        if (!empty($validated['exclude_event_id'])) {
            $conflictQuery->where(
                'id',
                '!=',
                $validated['exclude_event_id']
            );
        }

        $rooms = Room::query()
            ->where('status', ['Available', 'Booked'])
            ->whereNotIn('id', $conflictQuery)
            ->orderBy('room_name')
            ->get(['id', 'room_name', 'status']);

        return response()->json([
            'success' => true,
            'message' => 'Daftar ruangan tersedia berhasil diambil.',
            'data' => $rooms,
        ]);
    }

}