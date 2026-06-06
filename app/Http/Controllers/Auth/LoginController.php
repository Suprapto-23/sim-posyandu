<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\LoginLog;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    /**
     * Menampilkan halaman login.
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user()->role);
        }

        return view('auth.login');
    }

    /**
     * Proses login.
     *
     * Identitas login yang didukung:
     * - Email untuk Admin, Bidan, Kader, atau User
     * - NIK 16 digit untuk User/Warga
     *
     * Catatan:
     * - Field input "username" tetap dibaca agar form lama tidak error.
     * - Namun sistem tidak lagi mencari kolom username di database.
     */
    public function login(Request $request)
    {
        $identifier = $request->input('login')
            ?? $request->input('email')
            ?? $request->input('username')
            ?? $request->input('identifier');

        $login = trim((string) $identifier);

        $request->merge([
            'login' => $login,
        ]);

        $request->validate(
            [
                'login' => ['required', 'string', 'max:191'],
                'password' => ['required', 'string'],
            ],
            [
                'login.required' => 'Email atau NIK wajib diisi.',
                'password.required' => 'Password wajib diisi.',
            ]
        );

        $loginType = $this->getLoginType($login);

        if (! $loginType) {
            return back()
                ->withErrors([
                    'login' => 'Format tidak valid. Gunakan email atau NIK 16 digit angka.',
                ])
                ->withInput($request->only('login'));
        }

        $user = $this->findUserByLogin($login, $loginType);

        if (! $user) {
            return back()
                ->withErrors([
                    'login' => 'Akun tidak ditemukan. Identitas yang Anda masukkan belum terdaftar di sistem.',
                ])
                ->withInput($request->only('login'));
        }

        if (($user->status ?? null) !== 'active') {
            return back()
                ->withErrors([
                    'login' => 'Akun Anda tidak aktif. Hubungi admin Posyandu untuk mengaktifkan akun.',
                ])
                ->withInput($request->only('login'));
        }

        if (! Hash::check($request->password, $user->password)) {
            $this->writeLoginLog($user->id, $request, 'failed');

            return back()
                ->withErrors([
                    'password' => 'Password salah.',
                ])
                ->withInput($request->only('login'));
        }

        Auth::login($user, $request->boolean('remember'));

        $request->session()->regenerate();
        $request->session()->put('login_role', $user->role);
        $request->session()->put('login_user_id', $user->id);
        $request->session()->save();

        $this->writeLoginLog($user->id, $request, 'success');
        $this->updateLastLogin($user);

        return redirect()->to($this->getRedirectUrl($user->role));
    }

    /**
     * Logout akun.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('info', 'Anda telah berhasil keluar dari sistem.');
    }

    /**
     * Deteksi tipe login.
     *
     * Hanya mendukung:
     * - email
     * - nik 16 digit
     */
    private function getLoginType(string $login): ?string
    {
        if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
            return 'email';
        }

        if (preg_match('/^\d{16}$/', $login)) {
            return 'nik';
        }

        return null;
    }

    /**
     * Cari user berdasarkan email atau NIK.
     */
    private function findUserByLogin(string $login, string $loginType): ?User
    {
        return match ($loginType) {
            'email' => User::where('email', $login)->first(),
            'nik' => $this->findUserByNik($login),
            default => null,
        };
    }

    /**
     * Cari user berdasarkan NIK.
     *
     * Urutan:
     * 1. Cek kolom nik di tabel users.
     * 2. Jika tidak ada, cek tabel profiles.
     */
    private function findUserByNik(string $nik): ?User
    {
        $user = User::where('nik', $nik)->first();

        if ($user) {
            return $user;
        }

        try {
            $profile = Profile::where('nik', $nik)->first();

            return $profile?->user;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * URL redirect setelah login berhasil.
     */
    private function getRedirectUrl(string $role): string
    {
        return match (strtolower($role)) {
            'admin' => '/admin/dashboard',
            'bidan' => '/bidan/dashboard',
            'kader' => '/kader/dashboard',
            'user' => '/user/dashboard',
            default => '/home',
        };
    }

    /**
     * Redirect user yang sudah login.
     */
    private function redirectBasedOnRole(string $role)
    {
        return match (strtolower($role)) {
            'admin' => redirect()->route('admin.dashboard'),
            'bidan' => redirect()->route('bidan.dashboard'),
            'kader' => redirect()->route('kader.dashboard'),
            'user' => redirect()->route('user.dashboard'),
            default => redirect('/home'),
        };
    }

    /**
     * Simpan log login.
     *
     * Jika tabel atau model log bermasalah, proses login tetap berjalan.
     */
    private function writeLoginLog(int $userId, Request $request, string $status): void
    {
        try {
            if (! class_exists(LoginLog::class)) {
                return;
            }

            LoginLog::create([
                'user_id' => $userId,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'login_at' => now(),
                'status' => $status,
            ]);
        } catch (\Throwable $e) {
            // Jangan ganggu proses login hanya karena log gagal tersimpan.
        }
    }

    /**
     * Update waktu login terakhir.
     *
     * Jika kolom last_login_at tidak tersedia, login tetap aman.
     */
    private function updateLastLogin(User $user): void
    {
        try {
            $user->forceFill([
                'last_login_at' => now(),
            ])->save();
        } catch (\Throwable $e) {
            // Silent agar login tidak gagal karena kolom opsional.
        }
    }
}