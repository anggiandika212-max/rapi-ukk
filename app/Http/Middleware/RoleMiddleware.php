<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Pastikan user sudah login
        if (!$request->user()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        // Ambil role user
        $userRole = $request->user()->role?->role_name;

        // Pastikan user mempunyai role
        if (!$userRole) {
            return response()->json([
                'success' => false,
                'message' => 'User belum memiliki role.',
            ], 403);
        }

        // Cek apakah role user diizinkan
        if (!in_array($userRole, $roles)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk mengakses fitur ini.',
            ], 403);
        }

        return $next($request);
    }
}