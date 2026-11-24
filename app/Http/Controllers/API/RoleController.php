<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\RoleResource;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    /**
     * Role inti yang wajib tersedia di sistem.
     */
    private array $coreRoles = [
        'Admin',
        'PIC',
        'Guru',
        'Guest',
    ];

    /**
     * Menampilkan daftar role.
     */
    public function index(Request $request)
    {
        $query = Role::withCount('users');

        /*
        =========================
        SEARCH
        =========================
        */
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(
                'role_name',
                'like',
                "%{$search}%"
            );
        }

        /*
        =========================
        SORTING
        =========================
        */
        $sortBy = $request->get('sort_by', 'id');
        $sortOrder = strtolower(
            $request->get('sort_order', 'asc')
        );

        $allowedSort = [
            'id',
            'role_name',
            'created_at',
        ];

        if (
            in_array($sortBy, $allowedSort) &&
            in_array($sortOrder, ['asc', 'desc'])
        ) {
            $query->orderBy($sortBy, $sortOrder);
        }

        /*
        =========================
        PAGINATION
        =========================
        */
        $perPage = min(
            (int) $request->get('per_page', 10),
            100
        );

        $roles = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Data role berhasil diambil',
            'data' => RoleResource::collection($roles),
            'pagination' => [
                'current_page' => $roles->currentPage(),
                'last_page' => $roles->lastPage(),
                'per_page' => $roles->perPage(),
                'total' => $roles->total(),
            ],
        ]);
    }

    /**
     * Menambahkan role baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'role_name' => [
                'required',
                'string',
                'max:100',
                'unique:roles,role_name',
            ],
        ]);

        $role = Role::create([
            'role_name' => $validated['role_name'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Role berhasil ditambahkan',
            'data' => new RoleResource($role),
        ], 201);
    }

    /**
     * Menampilkan detail role.
     */
    public function show(string $id)
    {
        $role = Role::withCount('users')->find($id);

        if (!$role) {
            return response()->json([
                'success' => false,
                'message' => 'Role tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail role',
            'data' => new RoleResource($role),
        ]);
    }

    /**
     * Mengubah role.
     */
    public function update(Request $request, string $id)
    {
        $role = Role::find($id);

        if (!$role) {
            return response()->json([
                'success' => false,
                'message' => 'Role tidak ditemukan',
            ], 404);
        }

        $validated = $request->validate([
            'role_name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('roles', 'role_name')
                    ->ignore($role->id),
            ],
        ]);

        $role->update([
            'role_name' => $validated['role_name'],
        ]);

        $role->loadCount('users');

        return response()->json([
            'success' => true,
            'message' => 'Role berhasil diperbarui',
            'data' => new RoleResource($role),
        ]);
    }

    /**
     * Menghapus role.
     */
    public function destroy(string $id)
    {
        $role = Role::withCount('users')->find($id);

        if (!$role) {
            return response()->json([
                'success' => false,
                'message' => 'Role tidak ditemukan',
            ], 404);
        }

        /*
        ===================================
        CEGAH HAPUS ROLE INTI
        ===================================
        */
        if (in_array($role->role_name, $this->coreRoles)) {
            return response()->json([
                'success' => false,
                'message' => "Role {$role->role_name} merupakan role inti sistem dan tidak dapat dihapus.",
            ], 403);
        }

        /*
        ===================================
        CEGAH HAPUS ROLE YANG MASIH DIPAKAI
        ===================================
        */
        if ($role->users_count > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Role tidak dapat dihapus karena masih digunakan oleh user.',
                'users_count' => $role->users_count,
            ], 422);
        }

        $role->delete();

        return response()->json([
            'success' => true,
            'message' => 'Role berhasil dihapus',
        ]);
    }
}