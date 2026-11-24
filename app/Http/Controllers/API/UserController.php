<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Menampilkan daftar user dengan pencarian,
     * filter role, sorting, dan pagination.
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'role_id' => ['nullable', 'integer', 'exists:roles,id'],
            'sort_by' => [
                'nullable',
                Rule::in(['id', 'name', 'email', 'created_at']),
            ],
            'sort_order' => [
                'nullable',
                Rule::in(['asc', 'desc']),
            ],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $query = User::with('role');

        if (!empty($validated['search'])) {
            $search = $validated['search'];

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (!empty($validated['role_id'])) {
            $query->where('role_id', $validated['role_id']);
        }

        $sortBy = $validated['sort_by'] ?? 'id';
        $sortOrder = $validated['sort_order'] ?? 'asc';

        $query->orderBy($sortBy, $sortOrder);

        $perPage = (int) ($validated['per_page'] ?? 10);

        $users = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Data user berhasil diambil',
            'data' => UserResource::collection(
                $users->getCollection()
            ),
            'pagination' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
        ]);
    }

    /**
     * Membuat user baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'role_id' => [
                'required',
                'integer',
                Rule::exists('roles', 'id'),
            ],
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
            ],
            'phone' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('users', 'phone'),
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
        ]);

        $user = User::create([
            'role_id' => $validated['role_id'],
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
        ]);

        $user->load('role');

        return response()->json([
            'success' => true,
            'message' => 'User berhasil ditambahkan',
            'data' => new UserResource($user),
        ], 201);
    }

    /**
     * Menampilkan detail user.
     */
    public function show(string $id)
    {
        $user = User::with('role')->find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail user berhasil diambil',
            'data' => new UserResource($user),
        ]);
    }

    /**
     * Memperbarui data user.
     */
    public function update(Request $request, string $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan',
            ], 404);
        }

        $validated = $request->validate([
            'role_id' => [
                'required',
                'integer',
                Rule::exists('roles', 'id'),
            ],
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'phone' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('users', 'phone')->ignore($user->id),
            ],
            'password' => [
                'nullable',
                'string',
                'min:8',
                'confirmed',
            ],
        ]);

        $user->role_id = $validated['role_id'];
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->phone = $validated['phone'] ?? null;

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();
        $user->load('role');

        return response()->json([
            'success' => true,
            'message' => 'User berhasil diperbarui',
            'data' => new UserResource($user),
        ]);
    }

    /**
     * Menghapus user beserta token Sanctum miliknya.
     */
    public function destroy(Request $request, string $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan',
            ], 404);
        }

        $currentUser = $request->user();

        if (!$currentUser) {
            return response()->json([
                'success' => false,
                'message' => 'Autentikasi diperlukan.',
            ], 401);
        }

        if ((string) $currentUser->id === (string) $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak dapat menghapus akun sendiri.',
            ], 403);
        }

        DB::transaction(function () use ($user) {
            $user->tokens()->delete();
            $user->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'User berhasil dihapus',
        ]);
    }
}