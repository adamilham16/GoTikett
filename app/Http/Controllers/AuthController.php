<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\LoginLog;
use App\Models\PasswordResetToken;
use App\Mail\PasswordResetMail;

class AuthController extends Controller
{
    // Maksimal gagal login sebelum di-lock
    private const MAX_ATTEMPTS  = 5;
    // Durasi lock (menit)
    private const LOCKOUT_MINS  = 15;
    // Window penghitungan gagal (menit)
    private const FAIL_WINDOW   = 10;

    public function showLogin()
    {
        if (session('user_id')) return redirect()->route('dashboard');
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $ip = $request->ip();

        // Cek apakah IP sedang di-lock
        if (Cache::has("login_lock_{$ip}")) {
            $remaining = (int) ceil(Cache::get("login_lock_ttl_{$ip}", now()->addMinutes(self::LOCKOUT_MINS))->diffInMinutes(now()));
            LoginLog::create([
                'username'   => $request->username,
                'ip_address' => $ip,
                'status'     => 'locked',
                'user_agent' => substr($request->userAgent() ?? '', 0, 255),
            ]);
            return back()
                ->withErrors(['auth' => "Terlalu banyak percobaan login. Coba lagi dalam {$remaining} menit."])
                ->withInput(['username' => $request->username]);
        }

        $user = User::where('username', $request->username)->first();

        if (!$user || !password_verify($request->password, $user->password)) {
            $this->recordFailedAttempt($ip, $request);
            return back()
                ->withErrors(['auth' => 'Username atau password salah.'])
                ->withInput(['username' => $request->username]);
        }

        if (!$user->is_active) {
            LoginLog::create([
                'username'   => $request->username,
                'ip_address' => $ip,
                'status'     => 'failed',
                'user_agent' => substr($request->userAgent() ?? '', 0, 255),
            ]);
            return back()
                ->withErrors(['auth' => 'Akun Anda telah dinonaktifkan.'])
                ->withInput(['username' => $request->username]);
        }

        // Login sukses — bersihkan counter gagal
        Cache::forget("login_fails_{$ip}");
        Cache::forget("login_lock_{$ip}");

        LoginLog::create([
            'username'   => $user->username,
            'ip_address' => $ip,
            'status'     => 'success',
            'user_agent' => substr($request->userAgent() ?? '', 0, 255),
        ]);

        session()->regenerate();
        session([
            'user_id'   => $user->id,
            'user_name' => $user->name,
            'user_type' => $user->type,
        ]);

        return redirect()->route('dashboard');
    }

    public function logout()
    {
        session()->flush();
        return redirect()->route('login');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        $user = User::findOrFail(session('user_id'));

        if (!password_verify($request->old_password, $user->password)) {
            return response()->json(['success' => false, 'message' => 'Password lama salah.']);
        }

        $user->update(['password' => bcrypt($request->new_password)]);

        return response()->json(['success' => true, 'message' => 'Password berhasil diubah.']);
    }

    // ── Forgot Password ────────────────────────────────────────────────────────

    public function showForgotPassword()
    {
        if (session('user_id')) return redirect()->route('dashboard');
        return view('auth.forgot-password');
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['username' => 'required|string']);

        $user = User::where('username', $request->username)->where('is_active', true)->first();

        if ($user) {
            // Hapus token lama yang belum dipakai
            PasswordResetToken::where('user_id', $user->id)->where('used', false)->delete();

            $record = PasswordResetToken::create([
                'user_id'    => $user->id,
                'token'      => Str::random(64),
                'expires_at' => now()->addHours(24),
            ]);

            // Kirim email jika user punya alamat email dan mailer dikonfigurasi
            if ($user->email) {
                try {
                    Mail::to($user->email)->send(new PasswordResetMail($record->load('user')));
                } catch (\Throwable $e) {
                    Log::error('Gagal kirim email reset password', [
                        'user_id' => $user->id,
                        'error'   => $e->getMessage(),
                    ]);
                }
            }
        }

        // Selalu tampilkan pesan yang sama (hindari user enumeration)
        $msg = 'Jika username terdaftar, permintaan reset telah diproses.';
        $msg .= $user?->email
            ? ' Cek email Anda untuk mendapatkan link reset password.'
            : ' Hubungi admin IT untuk mendapatkan link reset password Anda.';

        return back()->with('status', $msg);
    }

    public function showResetPassword(string $token)
    {
        $record = PasswordResetToken::with('user')->where('token', $token)->first();

        if (!$record || !$record->isValid()) {
            return redirect()->route('login')->withErrors(['auth' => 'Link reset password tidak valid atau sudah kedaluwarsa.']);
        }

        return view('auth.reset-password', compact('token'));
    }

    public function resetPassword(Request $request, string $token)
    {
        $request->validate([
            'password' => 'required|min:8|confirmed',
        ]);

        $record = PasswordResetToken::with('user')->where('token', $token)->first();

        if (!$record || !$record->isValid()) {
            return redirect()->route('login')->withErrors(['auth' => 'Link reset password tidak valid atau sudah kedaluwarsa.']);
        }

        $record->user->update(['password' => bcrypt($request->password)]);
        $record->update(['used' => true]);

        return redirect()->route('login')->with('status', 'Password berhasil direset. Silakan login dengan password baru Anda.');
    }

    // ── Private Helpers ────────────────────────────────────────────────────────

    private function recordFailedAttempt(string $ip, Request $request): void
    {
        LoginLog::create([
            'username'   => $request->username,
            'ip_address' => $ip,
            'status'     => 'failed',
            'user_agent' => substr($request->userAgent() ?? '', 0, 255),
        ]);

        $key   = "login_fails_{$ip}";
        $count = Cache::get($key, 0) + 1;
        Cache::put($key, $count, now()->addMinutes(self::FAIL_WINDOW));

        if ($count >= self::MAX_ATTEMPTS) {
            $lockUntil = now()->addMinutes(self::LOCKOUT_MINS);
            Cache::put("login_lock_{$ip}", true, $lockUntil);
            Cache::put("login_lock_ttl_{$ip}", $lockUntil, $lockUntil);
            Cache::forget($key);
        }
    }
}
