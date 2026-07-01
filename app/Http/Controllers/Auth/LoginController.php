<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\LoginLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user()->role);
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        // 1. Pengecekan Rate Limiting (Mencegah Bruteforce)
        $this->ensureIsNotRateLimited($request);

        $loginField = trim((string) ($request->input('login') ?? ''));
        $request->merge(['login' => $loginField]);

        $request->validate([
            'login'    => ['required', 'string', 'max:191'],
            'password' => ['required', 'string'],
        ], [
            'login.required'    => 'Email atau NIK wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        // 2. Deteksi Pintar: Email vs NIK
        $loginType = $this->getLoginType($loginField);
        if (!$loginType) {
            RateLimiter::hit($this->throttleKey($request));
            return back()
                ->withErrors(['login' => 'Format tidak valid. Gunakan email atau NIK 16 digit.'])
                ->withInput();
        }

        // 3. PERBAIKAN BUG KEAMANAN: Single Source of Truth
        // Hanya mencari di tabel 'users' untuk mencegah bypass jika ada perbedaan data NIK dengan profil
        $user = null;
        if ($loginType === 'email') {
            $user = User::where('email', $loginField)->first();
        } else {
            $user = User::where('nik', $loginField)->first();
        }

        // 4. Verifikasi Akun & Password
        if (!$user || !Hash::check($request->input('password'), $user->password)) {
            RateLimiter::hit($this->throttleKey($request));
            
            if ($user) {
                $this->logLogin($user->id, $request, 'failed_password');
            }

            return back()
                ->withErrors(['login' => 'Kredensial tidak ditemukan atau password salah.'])
                ->withInput();
        }

        // 5. Verifikasi Status Aktif
        if ($user->status !== 'active' && $user->status !== null) {
            $this->logLogin($user->id, $request, 'failed_inactive');
            return back()->withErrors(['login' => 'Akun Anda tidak aktif. Hubungi admin.'])->withInput();
        }

        // 6. Login Sukses
        Auth::login($user, $request->boolean('remember'));
        RateLimiter::clear($this->throttleKey($request));

        $this->updateLastLogin($user);
        $this->logLogin($user->id, $request, 'success');

        $request->session()->regenerate();

        // 7. PERBAIKAN BUG UX: Menggunakan intended()
        // Mengembalikan user ke URL yang mau dia buka sebelum kena potong halaman login
        return redirect()->intended($this->getRedirectUrl($user->role));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        // PERBAIKAN: Kirimkan pesan sukses saat diarahkan kembali ke halaman login
        return redirect('/login')->with('success', 'Anda telah berhasil keluar dari sistem.');
    }
    // === Fungsi Pendukung (Helpers) ===

    private function getLoginType(string $login): ?string
    {
        if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
            return 'email';
        }
        
        if (is_numeric($login) && strlen($login) === 16) {
            return 'nik';
        }
        
        return null;
    }

    private function getRedirectUrl(?string $role): string
    {
        return match ($role) {
            'admin' => '/admin/dashboard',
            'bidan' => '/bidan/dashboard',
            'kader' => '/kader/dashboard',
            'user'  => '/user/dashboard',
            default => '/',
        };
    }

    private function redirectBasedOnRole(?string $role)
    {
        return redirect()->to($this->getRedirectUrl($role));
    }

    private function updateLastLogin(User $user): void
    {
        try {
            $user->forceFill(['last_login_at' => now()])->save();
        } catch (\Throwable) {
            // Abaikan secara logis jika kolom tidak ada
        }
    }

    private function logLogin(int $userId, Request $request, string $status): void
    {
        try {
            if (!class_exists(LoginLog::class)) return;
            
            DB::table('login_logs')->insert([
                'user_id'     => $userId,
                'ip_address'  => $request->ip(),
                'user_agent'  => $request->userAgent(),
                'login_at'    => now(),
                'status'      => $status,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        } catch (\Throwable) {
            // Silent error fail-safe
        }
    }

    // === Rate Limiting (Keamanan Anti-Spam) ===

    private function throttleKey(Request $request): string
    {
        return strtolower($request->input('login')) . '|' . $request->ip();
    }

    private function ensureIsNotRateLimited(Request $request): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey($request));
        
        throw ValidationException::withMessages([
            'login' => "Terlalu banyak percobaan. Coba lagi dalam {$seconds} detik.",
        ]);
    }
}