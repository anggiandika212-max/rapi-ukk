<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\RoomResource;
use App\Models\Room;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $query = Room::with('pic');

        /*
        =====================
        PEMBATASAN PIC
        =====================
        */
        if($user->role?->role_name === 'PIC'){
            $query->where('pic_id', $user->id);
        }        
        /*
        =====================
        SEARCH
        =====================
        */

        if ($request->filled('search')) {

            $search = $request->search;

            $query->where(function ($q) use ($search) {

                $q->where('room_name', 'like', "%{$search}%")
                  ->orWhere('status', 'like', "%{$search}%");

            });
        }

        /*
        =====================
        FILTER
        =====================
        */

        if ($request->filled('status')) {

            $query->where('status', $request->status);

        }
        /*
        =====================
        FILTER PIC
        =====================
        */
        if ($request->filled('pic_id')  &&
        $user->role?->role_name === 'Admin'){

            $query->where('pic_id', $request->pic_id);

        }

        /*
        =====================
        SORTING
        =====================
        */

        $sortBy = $request->get('sort_by', 'id');
        $sortOrder = $request->get('sort_order', 'asc');

        $allowedSort = [
            'id',
            'room_name',
            'status',
            'created_at'
        ];

        if (
            in_array($sortBy, $allowedSort) &&
            in_array(strtolower($sortOrder), ['asc', 'desc'])
        ) {
            $query->orderBy($sortBy, $sortOrder);
        }

        /*
        =====================
        PAGINATION
        =====================
        */

        $perPage = $request->get('per_page', 10);

        $rooms = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Data room berhasil diambil',
            'data' => RoomResource::collection($rooms),
            'pagination' => [
                'current_page' => $rooms->currentPage(),
                'last_page' => $rooms->lastPage(),
                'per_page' => $rooms->perPage(),
                'total' => $rooms->total(),
            ]
        ]);
    }

    /**
     * Store a newly created resource.
     */
    public function store(Request $request)
    {
        $request->validate([
            'pic_id' => 'required|exists:users,id',
            'room_name' => 'required|string|max:255',
            'facilities' => 'required',
            'facility_condition' => 'required',
            'status' => 'required|in:Available,Booked,Maintenance',
            'photo' => 'nullable|string'
        ]);

        $room = Room::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Room berhasil ditambahkan',
            'data' => new RoomResource($room)
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request,string $id)
    {
        $user = $request->user();
        
        $room = Room::with('pic')->find($id);

        if (!$room) {

            return response()->json([
                'success' => false,
                'message' => 'Room tidak ditemukan'
            ], 404);

        }

        /*
    =====================================
    PEMBATASAN PIC
    =====================================
    */

        if (
            $user->role?->role_name === 'PIC' &&
            $room->pic_id !== $user->id
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke room ini.'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail room',
            'data' => new RoomResource($room)
        ]);
    }

    /**
     * Update the specified resource.
     */
    public function update(Request $request, string $id)
    {
        $room = Room::find($id);

        if (!$room) {

            return response()->json([
                'success' => false,
                'message' => 'Room tidak ditemukan'
            ], 404);

        }

        $request->validate([
            'pic_id' => 'required|exists:users,id',
            'room_name' => 'required|string|max:255',
            'facilities' => 'required',
            'facility_condition' => 'required',
            'status' => 'required|in:Available,Booked,Maintenance',
            'photo' => 'nullable|string'
        ]);

        $room->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Room berhasil diupdate',
            'data' => new RoomResource($room)
        ]);
    }

    /**
     * Remove the specified resource.
     */
    public function destroy(string $id)
    {
        $room = Room::find($id);

        if (!$room) {

            return response()->json([
                'success' => false,
                'message' => 'Room tidak ditemukan'
            ], 404);

        }

        $room->update([
            'is_active' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Room berhasil dihapus'
        ]);
    }
}