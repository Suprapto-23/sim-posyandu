<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\LoginLog;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

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
        // Rate limiting
        $this->ensureIsNotRateLimited($request);

        $login = trim((string) ($request->input('login') ?? ''));
        $request->merge(['login' => $login]);

        $request->validate([
            'login'    => ['required', 'string', 'max:191'],
            'password' => ['required', 'string'],
        ], [
            'login.required'    => 'Email atau NIK wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $loginType = $this->getLoginType($login);
        if (!$loginType) {
            RateLimiter::hit($this->throttleKey($request));
            return back()
                ->withErrors(['login' => 'Format tidak valid. Gunakan email atau NIK 16 digit.'])
                ->withInput();
        }

        $user = $this->findUserByLogin($login, $loginType);
        if (!$user) {
            RateLimiter::hit($this->throttleKey($request));
            return back()
                ->withErrors(['login' => 'Akun tidak ditemukan.'])
                ->withInput();
        }

        if ($user->status !== 'active') {
            return back()
                ->withErrors(['login' => 'Akun tidak aktif. Hubungi admin.'])
                ->withInput();
        }

        if (!Hash::check($request->password, $user->password)) {
            RateLimiter::hit($this->throttleKey($request));
            $this->logLogin($user->id, $request, 'failed');
            return back()
                ->withErrors(['password' => 'Password salah.'])
                ->withInput();
        }

        // Login sukses
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();
        $request->session()->put('login_role', $user->role);
        $request->session()->put('login_user_id', $user->id);

        $this->updateLastLogin($user);
        $this->logLogin($user->id, $request, 'success');
        RateLimiter::clear($this->throttleKey($request));

        // Redirect langsung
        return redirect()->to($this->getRedirectUrl($user->role));
    }

    public function logout(Request $request)
{
    Auth::logout();

    // Hapus sesi lama dan buat ulang token keamanan
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    // Wajib menambahkan ->with('success', '...') agar terbaca oleh SweetAlert
    return redirect('/login')->with('success', 'Anda telah berhasil keluar dari sistem dengan aman.');
}

    // ========== PRIVATE HELPERS ==========

    private function getLoginType(string $login): ?string
    {
        if (filter_var($login, FILTER_VALIDATE_EMAIL)) return 'email';
        if (preg_match('/^\d{16}$/', $login)) return 'nik';
        return null;
    }

    private function findUserByLogin(string $login, string $loginType): ?User
    {
        if ($loginType === 'email') {
            return User::where('email', $login)->first();
        }
        return $this->findUserByNik($login);
    }

    private function findUserByNik(string $nik): ?User
    {
        $user = User::where('nik', $nik)->first();
        if ($user) return $user;

        try {
            $profile = Profile::where('nik', $nik)->first();
            return $profile?->user;
        } catch (\Throwable) {
            return null;
        }
    }

    private function getRedirectUrl(string $role): string
    {
        return match (strtolower($role)) {
            'admin' => '/admin/dashboard',
            'bidan' => '/bidan/dashboard',
            'kader' => '/kader/dashboard',
            'user'  => '/user/dashboard',
            default => '/home',
        };
    }

    private function redirectBasedOnRole(string $role)
    {
        return redirect()->to($this->getRedirectUrl($role));
    }

    private function updateLastLogin(User $user): void
    {
        try {
            $user->forceFill(['last_login_at' => now()])->save();
        } catch (\Throwable) {
            // Abaikan jika kolom tidak ada
        }
    }

    private function logLogin(int $userId, Request $request, string $status): void
    {
        try {
            if (!class_exists(LoginLog::class)) return;
            \DB::table('login_logs')->insert([
                'user_id'     => $userId,
                'ip_address'  => $request->ip(),
                'user_agent'  => $request->userAgent(),
                'login_at'    => now(),
                'status'      => $status,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        } catch (\Throwable) {
            // Silent
        }
    }

    // === Rate Limiting ===
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