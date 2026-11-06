<?php
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;
use App\Http\Controllers\API\RoomController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\EventController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\ComplaintController;
use App\Http\Controllers\API\RoleController;
use App\Http\Controllers\API\DashboardController;
use App\Http\Controllers\API\MonthlyReportController;
use App\Http\Controllers\API\PasswordResetController;
use App\Http\Controllers\Api\HolidayController;
use App\Models\Complaint;


Route::post('/register',[AuthController::class,'register']);
Route::post('/login',[AuthController::class,'login']);
Route::get('/holidays', [HolidayController::class, 'index']);

Route::middleware('auth:sanctum')->group(function(){
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('role:Admin,PIC,Guru');
    /*
    =========================
    Event
    =========================
    */
    
    
    Route::get('/events/calendar', [EventController::class,'calendarInternal'])
        ->middleware('role:Admin,PIC,Guru');

    Route::get('/events', [EventController::class,'index'])
        ->middleware('role:Admin,PIC,Guru');
        
    Route::get('/events/available-rooms', [EventController::class,'availableRooms'])
        ->middleware('role:Admin,Guru');

    Route::get('/events/{id}', [EventController::class,'show'])
        ->middleware('role:Admin,PIC,Guru');

    Route::post('/events', [EventController::class,'store'])
        ->middleware('role:Admin,Guru');

    Route::put('/events/{id}', [EventController::class,'update'])
        ->middleware('role:Admin,Guru');

    Route::delete('/events/{id}', [EventController::class,'destroy'])
        ->middleware('role:Admin,Guru');

    Route::patch('/events/{id}/approval', [EventController::class,'approval'])
        ->middleware('role:Admin');
    
    Route::patch('/events/{id}/move', [EventController::class,'moveRoom'])
        ->middleware('role:Admin');
        
    Route::get('/my-events', [EventController::class,'myEvents'])
        ->middleware('role:Admin,Guru');


    /*
     ========================
     Room
     ========================
     */
    Route::get('/rooms', [RoomController::class, 'index'])
        ->middleware('role:Admin,PIC,Guru');
    Route::get('/rooms/{id}', [RoomController::class, 'show'])
        ->middleware('role:Admin,PIC,Guru');

    //hanya admin yang boleh mengelola master room
    Route::post('/rooms', [RoomController::class, 'store'])
        ->middleware('role:Admin');
    Route::put('/rooms/{id}', [RoomController::class, 'update'])
        ->middleware('role:Admin');
    Route::delete('/rooms/{id}', [RoomController::class, 'destroy'])
        ->middleware('role:Admin');
    /* 
    =========================
    Roels
    =========================
    */
    Route::apiResource('roles', RoleController::class)
        ->middleware('role:Admin');
    /*
    =========================
    Users
    =========================
    */
    Route::apiResource('users', UserController::class)
        ->middleware('role:Admin');
    /*
    ==========================
    Complain
    ==========================
    */
    //route complain
    Route::get('/complaints', [ComplaintController::class,'index'])
        ->middleware('role:Admin,PIC,Guru');
    //guru membuat complain
    Route::post('/complaints', [ComplaintController::class,'store'])
        ->middleware('role:Guru');
    //detail complain
    Route::get('/complaints/{id}', [ComplaintController::class,'show'])
        ->middleware('role:Admin,PIC,Guru');
    //guru edit deskripsi
    Route::patch('/complaints/{id}', [ComplaintController::class,'update'])
        ->middleware('role:Guru');
    //guru ganti photo
    Route::post('/complaints/{id}/photo', [ComplaintController::class,'updatePhoto'])
        ->middleware(['auth:sanctum', 'role:Guru']);
    
     // PIC update status
    Route::patch('/complaints/{id}/status', [ComplaintController::class,'updatestatus'])
        ->middleware('role:PIC');
    //admin mengirimkan pesan ke
    Route::post('/complaints/{id}/whatsapp', [ComplaintController::class,'whatsapp'])
        ->middleware('role:Admin');

    /*
    =====================
    Report
    =====================
    */
    Route::Get('/reports/monthly', [MonthlyReportController::class,'index'])
    ->Middleware('role:Admin') ->name('reports.monthly');
    /*
    =====================
    Logout
    =====================
    */
    Route::post('/logout',[AuthController::class,'logout'])->middleware('auth:sanctum');


    Route::get('/notifications', function (Request $request) {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->get();

        // Payload database notification adalah snapshot saat notifikasi dibuat.
        // Untuk complaint, segarkan judul/pesan/status dari data complaint terkini.
        $complaintIds = $notifications
            ->filter(fn ($notification) =>
                ($notification->data['type'] ?? null) === 'complaint_escalation'
                && !empty($notification->data['complaint_id'])
            )
            ->pluck('data.complaint_id')
            ->unique()
            ->values();

        $complaints = $complaintIds->isEmpty()
            ? collect()
            : \App\Models\Complaint::with('room')
                ->whereIn('id', $complaintIds)
                ->get()
                ->keyBy('id');

        $notifications->transform(function ($notification) use ($complaints) {
            $data = $notification->data ?? [];

            if (($data['type'] ?? null) !== 'complaint_escalation'
                || empty($data['complaint_id'])) {
                return $notification;
            }

            $complaint = $complaints->get($data['complaint_id']);
            if (!$complaint) {
                return $notification;
            }

            $roomName = $complaint->room?->room_name ?? 'Ruangan';
            $status = $complaint->status_repair;

            if ($status === \App\Models\Complaint::STATUS_SUDAH_DIPERBAIKI) {
                $title = 'Complaint Sudah Diperbaiki';
                $message = "Complaint pada {$roomName} telah selesai diperbaiki.";
            } elseif ($status === \App\Models\Complaint::STATUS_MENUNGGU_SPARE_PART) {
                $title = 'Complaint Menunggu Spare Part';
                $message = "Complaint pada {$roomName} sedang menunggu spare part.";
            } else {
                $title = 'Complaint Belum Diperbaiki';
                $message = "Complaint pada {$roomName} masih belum diperbaiki.";
            }

            $data['title'] = $title;
            $data['message'] = $message;
            $data['room_id'] = $complaint->room_id;
            $data['room_name'] = $roomName;
            $data['description'] = $complaint->description;
            $data['status_repair'] = $status;
            $data['updated_at'] = $complaint->updated_at?->format('Y-m-d H:i:s');
            $notification->data = $data;

            return $notification;
        });

        return response()->json([
            'success' => true,
            'data' => $notifications,
        ]);

    })->middleware('auth:sanctum');
        Route::put('/user/profile', function (Request $request) {
        $user = $request->user();

        $validated = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => [
            'required',
            'email',
            'max:255',
            Rule::unique('users', 'email')->ignore($user->id),
        ],
        'phone' => ['nullable', 'string', 'max:20'],
        ]);
        $user->update($validated);

        return response()->json([
        'success' => true,
        'message' => 'Profil berhasil diperbarui.',
        'data' => $user->load('role'),
        ]);
    })->middleware('auth:sanctum');

    Route::patch('/notifications/{id}/read', function (
    Request $request,
    string $id
    ) 

{

    $notification = $request->user()
        ->notifications()
        ->where('id', $id)
        ->first();

    if (!$notification) {
        return response()->json([
            'success' => false,
            'message' => 'Notifikasi tidak ditemukan.',
        ], 404);
    }

    $notification->markAsRead();

    return response()->json([
        'success' => true,
        'message' => 'Notifikasi ditandai sebagai sudah dibaca.',
    ]);

    })->middleware('auth:sanctum');

    Route::post('/holidays', [HolidayController::class, 'store'])
    ->Middleware('role:Admin');
    Route::put('/holidays/{id}', [HolidayController::class, 'update'])
    ->Middleware('role:Admin');
    Route::delete('/holidays/{id}', [HolidayController::class, 'destroy'])
    ->Middleware('role:Admin');

});
Route::post('/forgot-password', [PasswordResetController::class, 'sendOtp'])
    ->middleware('throttle:5,1');

Route::post('/verify-otp', [PasswordResetController::class, 'verifyOtp'])
    ->middleware('throttle:10,1');

Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])
    ->middleware('throttle:5,1');


Route::get('/user', function (Request $request) {
    $user = $request->user()->load('role');
    return response()->json([
        'data' => $user
    ]);
})->middleware('auth:sanctum');
Route::get('/public/events', [EventController::class, 'publicCalendar']);