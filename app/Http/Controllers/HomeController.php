<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\DetectsUserPeran;

/**
 * HomeController
 *
 * Titik tengah setelah login. Controller ini HANYA bertugas redirect,
 * tidak merender view apapun.
 *
 * ALUR:
 *   Login berhasil → HomeController@index
 *     ↓
 *     Cek role dari users.role (admin/bidan/kader/user)
 *     ↓
 *     Admin  → /admin/dashboard
 *     Bidan  → /bidan/dashboard
 *     Kader  → /kader/dashboard
 *     User (warga) → DetectsUserPeran → redirect ke halaman spesifik:
 *       orang_tua → /user/balita   (KMS Anak)
 *       remaja    → /user/remaja
 *       lansia    → /user/lansia
 *       bumil     → /user/dashboard (ibu hamil sementara di dashboard)
 *       multi-peran → /user/dashboard
 *       umum      → /user/dashboard (dengan pesan minta isi NIK)
 *
 * CATATAN UNTUK SIDANG:
 * Ini adalah implementasi Role-Based Access Control (RBAC) sesuai
 * dengan metodologi RAD — role dideteksi otomatis dari data, bukan
 * dikonfigurasi manual oleh admin.
 */
class HomeController extends Controller
{
    use DetectsUserPeran;

    /**
     * Smart redirect setelah login berhasil.
     * TIDAK merender view — hanya redirect sesuai role.
     */
    public function index()
    {
        $user = Auth::user();

        // Redirect berdasarkan role sistem (admin/bidan/kader)
        switch ($user->role) {
            case 'admin':
                return redirect()->route('admin.dashboard');

            case 'bidan':
                return redirect()->route('bidan.dashboard');

            case 'kader':
                return redirect()->route('kader.dashboard');

            case 'user':
            default:
                return $this->redirectWarga($user);
        }
    }

    /**
     * Redirect warga ke halaman yang sesuai berdasarkan data mereka.
     * Menggunakan DetectsUserPeran untuk deteksi otomatis.
     */
   private function redirectWarga($user)
{
    $ctx = $this->getUserContext($user);

    // Jika punya banyak peran (misal: punya balita sekaligus lansia)
    if ($ctx['is_multi_peran']) {
        return redirect()->route('user.dashboard');
    }

    $peranUtama = $ctx['peran'][0] ?? 'umum';

    switch ($peranUtama) {
        case 'orang_tua':
            return redirect()->route('user.balita.index'); // Langsung ke KMS
        case 'remaja':
            return redirect()->route('user.remaja.index');
        case 'lansia':
            return redirect()->route('user.lansia.index');
        default:
            return redirect()->route('user.dashboard'); // Baru ke dashboard jika 'umum'
    }
}
}