<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    // Menampilkan daftar hari libur
    public function index(Request $request)
    {
        $query = Holiday::query();

        if ($request->filled('year')) {
            $query->whereYear('date', $request->year);
        }

        return response()->json([
            'success' => true,
            'data' => $query->orderBy('date')->get(),
        ]);
    }

    // Menambahkan hari libur
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date|unique:holidays,date',
            'type' => 'required|in:nasional,cuti_bersama,sekolah',
            'description' => 'nullable|string',
        ]);

        $holiday = Holiday::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Hari libur berhasil ditambahkan.',
            'data' => $holiday,
        ], 201);
    }

    // Mengubah hari libur
    public function update(Request $request, $id)
    {
        $holiday = Holiday::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date|unique:holidays,date,' . $id,
            'type' => 'required|in:nasional,cuti_bersama,sekolah',
            'description' => 'nullable|string',
        ]);

        $holiday->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Hari libur berhasil diperbarui.',
            'data' => $holiday,
        ]);
    }

    // Menghapus hari libur
    public function destroy($id)
    {
        $holiday = Holiday::findOrFail($id);
        $holiday->delete();

        return response()->json([
            'success' => true,
            'message' => 'Hari libur berhasil dihapus.',
        ]);
    }
}