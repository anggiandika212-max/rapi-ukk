<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

class PasswordResetController extends Controller
{
    // Menampilkan form lupa password
    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

    // Mengirim link reset password
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($request->expectsJson()) {
            if ($status === Password::RESET_LINK_SENT) {
                return response()->json([
                    'message' => 'Jika email terdaftar, tautan reset password akan dikirim.',
                ]);
            }

            if ($status === Password::RESET_THROTTLED) {
                return response()->json([
                    'message' => 'Terlalu banyak permintaan. Silakan coba lagi nanti.',
                ], 429);
            }

            // Tidak mengungkap apakah email terdaftar
            return response()->json([
                'message' => 'Jika email terdaftar, tautan reset password akan dikirim.',
            ]);
        }

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with(
                'status',
                'Jika email terdaftar, tautan reset password akan dikirim.'
            );
        }

        if ($status === Password::RESET_THROTTLED) {
            return back()
                ->withErrors([
                    'email' => 'Terlalu banyak permintaan. Silakan coba lagi nanti.',
                ])
                ->withInput($request->only('email'));
        }

        return back()->with(
            'status',
            'Jika email terdaftar, tautan reset password akan dikirim.'
        );
    }

    // Menampilkan form password baru
    public function showResetForm(Request $request, string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    // Memvalidasi token dan menyimpan password baru
    public function reset(Request $request)
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => [
                'required',
                'confirmed',
                PasswordRule::min(8),
            ],
        ]);

        $status = Password::reset(
            $validated,
            function ($user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                // Menghapus token reset setelah berhasil digunakan
            }
        );

        if ($request->expectsJson()) {
            if ($status === Password::PASSWORD_RESET) {
                return response()->json([
                    'message' => 'Password berhasil diubah. Silakan login kembali.',
                ]);
            }

            return response()->json([
                'message' => __($status),
            ], 422);
        }

        if ($status === Password::PASSWORD_RESET) {
            return redirect()
                ->route('login')
                ->with('status', 'Password berhasil diubah. Silakan login kembali.');
        }

        return back()
            ->withErrors([
                'email' => __($status),
            ])
            ->withInput($request->only('email'));
    }
}
