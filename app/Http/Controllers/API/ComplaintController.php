<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\ComplaintResource;
use App\Models\Complaint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Notifications\ComplaintStatusUpdatedNotification;


class ComplaintController extends Controller
{
    /**
     * Menampilkan complaint.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Complaint::with([
            'room',
            'reporter',
        ]);

        /*
        =====================================
        PEMBATASAN BERDASARKAN ROLE
        =====================================
        */

        if ($user->role?->role_name === 'PIC') {

            $query->whereHas('room', function ($q) use ($user) {
                $q->where('pic_id', $user->id);
            });

        } elseif ($user->role?->role_name === 'Guru') {

            $query->where('reporter_id', $user->id);

        }
        /*
        Admin tidak diberi filter,
        sehingga dapat melihat semua complaint.
        */

        /*
        =====================================
        FILTER ROOM
        =====================================
        */

        if ($request->filled('room_id')) {
            $query->where('room_id', $request->room_id);
        }

        /*
        =====================================
        FILTER STATUS
        =====================================
        */

        if ($request->filled('status_repair')) {
            $query->where(
                'status_repair',
                $request->status_repair
            );
        }

        /*
        =====================================
        PAGINATION
        =====================================
        */

        $perPage = min(
            (int) $request->get('per_page', 10),
            100
        );

        $complaints = $query
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Data complaint berhasil diambil',
            'data' => ComplaintResource::collection($complaints),
            'pagination' => [
                'current_page' => $complaints->currentPage(),
                'last_page' => $complaints->lastPage(),
                'per_page' => $complaints->perPage(),
                'total' => $complaints->total(),
            ],
        ]);
    }

    /**
     * Guru membuat complaint.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_id' => [
                'required',
                'integer',
                'exists:rooms,id',
            ],

            'description' => [
                'required',
                'string',
            ],

            'photo' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ]);

        /*
        =====================================
        UPLOAD FOTO
        =====================================
        */

        $photoPath = $request->file('photo')
            ->store('complaints', 'public');

        /*
        =====================================
        SIMPAN COMPLAINT
        =====================================
        */

        $complaint = Complaint::create([
            'room_id' => $validated['room_id'],
            'reporter_id' => $request->user()->id,
            'description' => $validated['description'],
            'photo_path' => $photoPath,
            'status_repair' => 'Belum Diperbaiki',
        ]);

        $complaint->load([
            'room',
            'reporter',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengaduan berhasil dibuat',
            'data' => new ComplaintResource($complaint),
        ], 201);
    }

    /**
     * Menampilkan detail complaint.
     */
    public function show(Request $request, string $id)
    {
        $user = $request->user();

        $complaint = Complaint::with([
            'room',
            'reporter',
        ])->find($id);

        if (!$complaint) {
            return response()->json([
                'success' => false,
                'message' => 'Complaint tidak ditemukan',
            ], 404);
        }

        /*
        =====================================
        AKSES PIC
        =====================================
        */

        if (
            $user->role?->role_name === 'PIC' &&
            $complaint->room->pic_id !== $user->id
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke complaint ini.',
            ], 403);
        }

        /*
        =====================================
        AKSES GURU
        =====================================
        */

        if (
            $user->role?->role_name === 'Guru' &&
            $complaint->reporter_id !== $user->id
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke complaint ini.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail complaint',
            'data' => new ComplaintResource($complaint),
        ]);
    }

    /**
     * Update status perbaikan oleh PIC.
     */
    
    public function updatestatus(Request $request, string $id)
    {
        $user = $request->user();

        $complaint = Complaint::with('room')->find($id);

        // 1. Periksa apakah complaint tersedia.
        if (!$complaint) {
            return response()->json([
                'success' => false,
                'message' => 'Complaint tidak ditemukan.',
            ], 404);
        }

        // 2. Pastikan hanya PIC ruangan terkait yang dapat mengubah status.
        if (
            $user->role?->role_name !== 'PIC' ||
            !$complaint->room ||
            (int) $complaint->room->pic_id !== (int) $user->id
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin mengubah complaint ini.',
            ], 403);
        }

        // 3. Validasi status perbaikan.
        $validated = $request->validate([
            'status_repair' => [
                'required',
                Rule::in([
                    Complaint::STATUS_BELUM_DIPERBAIKI,
                    Complaint::STATUS_MENUNGGU_SPARE_PART,
                    Complaint::STATUS_SUDAH_DIPERBAIKI,
                ]),
            ],
        ]);

        // 4. Perbarui status complaint.
       $oldStatus = $complaint->status_repair;

        $complaint->update([
            'status_repair' => $validated['status_repair'],
        ]);

        $complaint->load([
            'room',
            'reporter',
        ]);

        // Kirim notifikasi hanya jika status benar-benar berubah.
        if (
            $oldStatus !== $complaint->status_repair &&
            $complaint->reporter
        ) {
            $complaint->reporter->notify(
                new ComplaintStatusUpdatedNotification($complaint)
            );
        }
    }
    //guru edit complaint
    public function update(Request $request, $id)
    {
        $user = $request->user();

        // Hanya Guru yang boleh menggunakan fitur edit.
        if (!$user || $user->role?->role_name !== 'Guru') {
            return response()->json([
                'message' => 'Hanya Guru yang dapat mengedit pengaduan.'
            ], 403);
        }

        $complaint = Complaint::findOrFail($id);

        // Pengaduan hanya boleh diedit oleh pembuatnya sendiri.
        if ((int) $complaint->reporter_id !== (int) $user->id) {
            return response()->json([
                'message' => 'Kamu tidak memiliki akses untuk mengedit pengaduan ini.'
            ], 403);
        }

        // Pengaduan hanya dapat diedit sebelum diperbaiki.
        if ($complaint->status_repair !== 'Belum Diperbaiki') {
            return response()->json([
                'message' => 'Pengaduan hanya dapat diedit saat status Belum Diperbaiki.'
            ], 422);
        }

        $validated = $request->validate([
            'room_id' => ['required', 'integer', 'exists:rooms,id'],
            'description' => ['required', 'string', 'max:5000'],
            'photo' => [
                'sometimes',
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120'
            ],
        ], [
            'room_id.required' => 'Ruangan wajib dipilih.',
            'room_id.exists' => 'Ruangan yang dipilih tidak ditemukan.',
            'description.required' => 'Deskripsi pengaduan wajib diisi.',
            'description.max' => 'Deskripsi maksimal 5000 karakter.',
            'photo.image' => 'File harus berupa gambar.',
            'photo.mimes' => 'Foto harus berformat JPG, PNG, atau WebP.',
            'photo.max' => 'Ukuran foto maksimal 5 MB.',
        ]);

        $oldPhotoPath = $complaint->photo_path;
        $newPhotoPath = null;

        if ($request->hasFile('photo')) {
            $newPhotoPath = $request->file('photo')
                ->store('complaints', 'public');
        }

        try {
            $complaint->room_id = $validated['room_id'];
            $complaint->description = $validated['description'];

            // Foto lama dipertahankan jika pengguna tidak mengganti foto.
            if ($newPhotoPath !== null) {
                $complaint->photo_path = $newPhotoPath;
            }

            // reporter_id dan status_repair tidak diubah oleh endpoint ini.
            $complaint->save();
        } catch (\Throwable $e) {
            if ($newPhotoPath !== null) {
                Storage::disk('public')->delete($newPhotoPath);
            }

            throw $e;
        }

        // Hapus foto lama setelah foto baru berhasil disimpan ke database.
        if (
            $newPhotoPath !== null &&
            $oldPhotoPath &&
            $oldPhotoPath !== $newPhotoPath
        ) {
            Storage::disk('public')->delete($oldPhotoPath);
        }

        return response()->json([
            'message' => 'Pengaduan berhasil diperbarui.',
            'data' => new ComplaintResource(
                $complaint->load(['room', 'reporter'])
            ),
        ]);
    }

    public function whatsapp(Request $request, string $id)
    {
        $complaint = Complaint::with([
            'room.pic',
            'reporter'
        ])->find($id);

        if (!$complaint) {
            return response()->json([
                'success' => false,
                'message' => 'Pengaduan tidak ditemukan.',
            ], 404);
        }

        /*
        ==========================================
        HANYA ADMIN YANG SUDAH DIIZINKAN DI ROUTE
        ==========================================
        */

        /*
        ==========================================
        CEK STATUS COMPLAINT
        ==========================================
        */
        if (
            $complaint->status_repair !== Complaint::STATUS_BELUM_DIPERBAIKI
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Peringatan WhatsApp hanya dapat dikirim untuk pengaduan yang belum diperbaiki.',
            ], 422);
        }

        /*
        ==========================================
        CEK PIC RUANG
        ==========================================
        */
        $pic = $complaint->room?->pic;

        if (!$pic) {
            return response()->json([
                'success' => false,
                'message' => 'Ruangan ini belum memiliki PIC.',
            ], 422);
        }

        /*
        ==========================================
        CEK NOMOR TELEPON PIC
        ==========================================
        */
        if (!$pic->phone) {
            return response()->json([
                'success' => false,
                'message' => 'Nomor WhatsApp PIC belum tersedia.',
            ], 422);
        }

        /*
        ==========================================
        FORMAT NOMOR TELEPON
        ==========================================
        */
        $phone = preg_replace('/[^0-9]/', '', $pic->phone);

        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }

        /*
        ==========================================
        BUAT PESAN DINAMIS
        ==========================================
        */
        $message =
            "Peringatan Penanganan Fasilitas\n\n" .
            "Halo {$pic->name},\n\n" .
            "Terdapat pengaduan kerusakan fasilitas yang belum diperbaiki.\n\n" .
            "Ruangan: " . ($complaint->room?->room_name ?? '-') . "\n" .
            "Pelapor: " . ($complaint->reporter?->name ?? '-') . "\n" .
            "Kerusakan: {$complaint->description}\n" .
            "Status: {$complaint->status_repair}\n\n" .
            "Mohon segera melakukan tindak lanjut terhadap pengaduan tersebut.\n\n" .
            "Terima kasih.\n" .
            "RAPI-Sekolah";

        /*
        ==========================================
        BUAT URL WHATSAPP
        ==========================================
        */
        $whatsappUrl =
            'https://wa.me/' .
            $phone .
            '?text=' .
            urlencode($message);

        return response()->json([
            'success' => true,
            'message' => 'Link WhatsApp berhasil dibuat.',
            'data' => [
                'complaint_id' => $complaint->id,

                'pic' => [
                    'id' => $pic->id,
                    'name' => $pic->name,
                    'phone' => $pic->phone,
                ],

                'room' => [
                    'id' => $complaint->room?->id,
                    'name' => $complaint->room?->room_name,
                ],

                'status_repair' => $complaint->status_repair,

                'whatsapp_url' => $whatsappUrl,
            ],
        ]);
    }
}