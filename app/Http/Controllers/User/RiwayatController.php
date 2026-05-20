<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Pemeriksaan;
use App\Traits\DetectsUserPeran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RiwayatController extends Controller
{
    use DetectsUserPeran;

    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Memanggil Trait Cerdas kita
        $ctx = $this->getUserContext($user);
        
        $targets = [];

        // 1. Cek dirinya sendiri (Jika dia terdaftar sebagai Lansia/Remaja/Bumil)
        if ($ctx['lansia']) {
            $targets[] = ['id' => $ctx['lansia']->id, 'kat' => 'lansia', 'nama' => $ctx['lansia']->nama_lengkap ?? $ctx['lansia']->nama];
        }
        
        if ($ctx['remaja']) {
            $targets[] = ['id' => $ctx['remaja']->id, 'kat' => 'remaja', 'nama' => $ctx['remaja']->nama_lengkap ?? $ctx['remaja']->nama];
        }
        

        // 2. Cek anak-anaknya (Balita/Bayi)
        if ($ctx['balitas']->isNotEmpty()) {
            foreach ($ctx['balitas'] as $balita) {
                $targets[] = ['id' => $balita->id, 'kat' => 'balita', 'nama' => $balita->nama_lengkap];
            }
        }

        // Jika user belum punya NIK atau tidak terhubung data apapun (Kosong)
        if (empty($targets)) {
            $riwayat = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10);
            return view('user.riwayat.index', compact('riwayat', 'targets'));
        }

        // 3. INTEGRASI BIDAN: Tarik semua riwayat yang statusnya SUDAH VERIFIED
        $riwayat = Pemeriksaan::where('status_verifikasi', 'verified')
            ->where(function($query) use ($targets) {
                foreach ($targets as $target) {
                    $query->orWhere(function($q) use ($target) {
                        $q->where('pasien_id', $target['id'])
                          ->where('kategori_pasien', $target['kat']);
                    });
                }
            })
            ->orderBy('tanggal_periksa', 'desc')
            ->paginate(15);

        return view('user.riwayat.index', compact('riwayat', 'targets'));
    }
}