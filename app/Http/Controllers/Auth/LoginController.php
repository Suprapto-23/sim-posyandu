<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\LoginLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    /* ─────────────────────────────────────────
     |  SHOW FORM
     ───────────────────────────────────────── */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user()->role);
        }

        return view('auth.login');
    }

    /* ─────────────────────────────────────────
     |  PROCESS LOGIN
     ───────────────────────────────────────── */
    public function login(Request $request)
{
    // Ambil identitas login dari beberapa kemungkinan nama input.
    // Ini bikin controller tahan banting kalau form pakai name="login", "email", atau "username".
    $identifier = $request->input('login')
        ?? $request->input('email')
        ?? $request->input('username')
        ?? $request->input('identifier');

    $request->merge([
        'login' => trim((string) $identifier),
    ]);

    $request->validate([
        'login'    => 'required|string',
        'password' => 'required|string',
    ], [
        'login.required'    => 'Email atau username wajib diisi.',
        'password.required' => 'Password wajib diisi.',
    ]);

    // 1. Validasi format identitas
    $loginType = $this->getLoginType($request->login);

    if (! $loginType) {
        return back()->withErrors([
            'login' => 'Format tidak valid. Gunakan email, username, atau NIK 16 digit angka.',
        ])->withInput($request->only('login'));
    }

    // 2. Cari user di database
    $user = $this->findUserByLogin($request->login, $loginType);

    if (! $user) {
        return back()->withErrors([
            'login' => 'Akun tidak ditemukan. Identitas yang Anda masukkan belum terdaftar di sistem.',
        ])->withInput($request->only('login'));
    }

    // 3. Cek status akun
    if ($user->status !== 'active') {
        return back()->withErrors([
            'login' => 'Akun Anda tidak aktif. Hubungi admin Posyandu untuk mengaktifkan akun.',
        ])->withInput($request->only('login'));
    }

    // 4. Verifikasi password
    if (! Hash::check($request->password, $user->password)) {
        $this->writeLoginLog($user->id, $request, 'failed');

        return back()->withErrors([
            'password' => 'Password salah.',
        ])->withInput($request->only('login'));
    }

    // 5. Login berhasil
    Auth::login($user, $request->boolean('remember'));

    $request->session()->regenerate();
    $request->session()->put('login_role', $user->role);
    $request->session()->put('login_user_id', $user->id);
    $request->session()->save();

    $this->writeLoginLog($user->id, $request, 'success');
    $this->updateLastLogin($user);

    return redirect()->to($this->getRedirectUrl($user->role));
}

    /* ─────────────────────────────────────────
     |  LOGOUT
     ───────────────────────────────────────── */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('info', 'Anda telah berhasil keluar dari sistem.');
    }

    /* ─────────────────────────────────────────
     |  PRIVATE HELPERS
     ───────────────────────────────────────── */

    /**
     * Deteksi tipe login: email | nik | username | null
     */
    private function getLoginType(string $login): ?string
    {
        if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
            return 'email';
        }

        if (preg_match('/^\d{16}$/', $login)) {
            return 'nik';
        }

        if (preg_match('/^[a-zA-Z0-9_]{3,}$/', $login)) {
            return 'username';
        }

        return null;
    }

    /**
     * Cari user berdasarkan tipe identitas.
     */
    private function findUserByLogin(string $login, string $loginType)
    {
        return match ($loginType) {
            'email'    => \App\Models\User::where('email', $login)->first(),
            'username' => \App\Models\User::where('username', $login)->first(),
            'nik'      => $this->findUserByNik($login),
            default    => null,
        };
    }

    /**
     * Cari user via NIK — di tabel users dulu, lalu di profiles.
     */
    private function findUserByNik(string $nik)
    {
        $user = \App\Models\User::where('nik', $nik)->first();

        if (! $user) {
            $profile = \App\Models\Profile::where('nik', $nik)->first();
            $user    = $profile?->user;
        }

        return $user;
    }

    /**
     * Redirect URL setelah login berhasil.
     */
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

    /**
     * Redirect response (untuk user yang sudah login).
     */
    private function redirectBasedOnRole(string $role)
    {
        return match (strtolower($role)) {
            'admin' => redirect()->route('admin.dashboard'),
            'bidan' => redirect()->route('bidan.dashboard'),
            'kader' => redirect()->route('kader.dashboard'),
            'user'  => redirect()->route('user.dashboard'),
            default => redirect('/home'),
        };
    }

    /**
     * Tulis log login (gagal maupun sukses).
     */
    private function writeLoginLog(int $userId, Request $request, string $status): void
{
    try {
        if (! class_exists(\App\Models\LoginLog::class)) {
            return;
        }

        \App\Models\LoginLog::create([
            'user_id'    => $userId,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'login_at'   => now(),
            'status'     => $status,
        ]);
    } catch (\Throwable $e) {
        // Silent, jangan ganggu proses login
    }
}

    /**
     * Perbarui kolom last_login_at.
     */
    private function updateLastLogin($user): void
    {
        try {
            $user->update(['last_login_at' => now()]);
        } catch (\Exception) {
            // Silent
        }
    }
}