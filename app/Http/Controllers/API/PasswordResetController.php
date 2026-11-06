<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class PasswordResetController extends Controller
{
    // Mengirim OTP ke email pengguna
    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Format email tidak valid.',
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        // Respons dibuat sama agar email terdaftar tidak dapat ditebak.
        if (!$user) {
            return response()->json([
                'success' => true,
                'message' => 'Jika email terdaftar, kode OTP akan dikirim.',
            ]);
        }

        $existingOtp = DB::table('password_reset_otps')
            ->where('user_id', $user->id)
            ->first();

        // Jeda pengiriman ulang OTP minimal 60 detik.
        if (
            $existingOtp &&
            $existingOtp->last_sent_at &&
            now()->diffInSeconds($existingOtp->last_sent_at) < 60
        ) {
            return response()->json([
                'success' => true,
                'message' => 'Jika email terdaftar, kode OTP akan dikirim.',
            ]);
        }

        $otp = (string) random_int(100000, 999999);

        DB::table('password_reset_otps')->updateOrInsert(
            ['user_id' => $user->id],
            [
                'otp_hash' => Hash::make($otp),
                'expires_at' => now()->addMinutes(5),
                'last_sent_at' => now(),
                'attempts' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        try {
            Mail::raw(
                "Halo {$user->name},\n\n"
                . "Kode OTP untuk mengatur ulang password RAPI-Sekolah adalah:\n\n"
                . "{$otp}\n\n"
                . "Kode ini berlaku selama 5 menit dan hanya dapat digunakan satu kali.\n"
                . "Jika Anda tidak meminta reset password, abaikan email ini.",
                function ($message) use ($user) {
                    $message->to($user->email)
                        ->subject('Kode OTP Reset Password RAPI-Sekolah');
                }
            );
        } catch (\Throwable $e) {
            report($e);

            DB::table('password_reset_otps')
                ->where('user_id', $user->id)
                ->delete();

            return response()->json([
                'success' => false,
                'message' => 'Email OTP gagal dikirim. Silakan coba kembali.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Jika email terdaftar, kode OTP akan dikirim.',
        ]);
    }

    // Memeriksa OTP
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp' => 'required|digits:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau OTP tidak valid.',
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return $this->invalidOtp();
        }

        $record = DB::table('password_reset_otps')
            ->where('user_id', $user->id)
            ->first();

        if (!$record || $record->attempts >= 5) {
            return $this->invalidOtp();
        }

        if (now()->greaterThan($record->expires_at)) {
            DB::table('password_reset_otps')
                ->where('user_id', $user->id)
                ->delete();

            return $this->invalidOtp('OTP sudah kedaluwarsa. Silakan minta kode baru.');
        }

        if (!Hash::check($request->otp, $record->otp_hash)) {
            DB::table('password_reset_otps')
                ->where('user_id', $user->id)
                ->increment('attempts');

            return $this->invalidOtp();
        }

        return response()->json([
            'success' => true,
            'message' => 'OTP berhasil diverifikasi.',
        ]);
    }

    // Mengatur password baru
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp' => 'required|digits:6',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return $this->invalidOtp();
        }

        $record = DB::table('password_reset_otps')
            ->where('user_id', $user->id)
            ->first();

        if (
            !$record ||
            $record->attempts >= 5 ||
            now()->greaterThan($record->expires_at) ||
            !Hash::check($request->otp, $record->otp_hash)
        ) {
            if ($record && $record->attempts < 5) {
                DB::table('password_reset_otps')
                    ->where('user_id', $user->id)
                    ->increment('attempts');
            }

            return $this->invalidOtp();
        }

        $user->password = $request->password;
        $user->save();

        DB::table('password_reset_otps')
            ->where('user_id', $user->id)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil diperbarui. Silakan login kembali.',
        ]);
    }

    private function invalidOtp($message = 'OTP salah atau tidak valid.')
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], 422);
    }
}