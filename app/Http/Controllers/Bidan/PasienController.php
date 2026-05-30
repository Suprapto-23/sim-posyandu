<?php

namespace App\Http\Controllers\Bidan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Balita;
use App\Models\Remaja;
use App\Models\Lansia;
use App\Models\Pemeriksaan;

class PasienController extends Controller
{
    // ========================================================================
    // 1. DATABASE BALITA
    // ========================================================================
    public function balita(Request $request)
    {
        $query = Balita::with('pemeriksaan_terakhir');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%")
                  ->orWhere('nama_ibu', 'like', "%{$search}%");
            });
        }

        // withQueryString() memastikan filter tidak hilang saat pindah halaman (Next/Prev)
        $balitas = $query->latest()->paginate(10)->withQueryString();

        return view('bidan.pasien.balita', compact('balitas'));
    }



    // ========================================================================
    // 3. DATABASE REMAJA
    // ========================================================================
    public function remaja(Request $request)
    {
        $query = Remaja::with('pemeriksaan_terakhir');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%")
                  ->orWhere('sekolah', 'like', "%{$search}%");
            });
        }

        $remajas = $query->latest()->paginate(10)->withQueryString();

        return view('bidan.pasien.remaja', compact('remajas'));
    }


    // ========================================================================
    // 4. DATABASE LANSIA (GERIATRI) - DENGAN LOGIKA FILTER KOMPLEKS
    // ========================================================================
    public function lansia(Request $request)
    {
        $query = Lansia::with('pemeriksaan_terakhir')->latest();

        // Logika Pencarian Menyeluruh (Termasuk mencari dari isi Diagnosa)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%")
                  ->orWhere('alamat', 'like', "%{$search}%")
                  ->orWhereHas('pemeriksaan_terakhir', function($sq) use ($search) {
                      $sq->where('diagnosa', 'like', "%{$search}%");
                  });
            });
        }

        // Logika Filter Status Kesehatan
        if ($request->filled('status')) {
            $status = $request->status;
            
            $query->whereHas('pemeriksaan_terakhir', function($q) use ($status) {
                if ($status == 'hipertensi') {
                    $q->whereRaw("CAST(SUBSTRING_INDEX(tekanan_darah, '/', 1) AS UNSIGNED) >= 140")
                      ->whereRaw("CAST(SUBSTRING_INDEX(tekanan_darah, '/', 1) AS UNSIGNED) < 180")
                      ->orWhere('diagnosa', 'like', '%hipertensi%');
                } elseif ($status == 'diabetes') { // Di tampilan labelnya adalah "Kritis (>180)"
                    $q->whereRaw("CAST(SUBSTRING_INDEX(tekanan_darah, '/', 1) AS UNSIGNED) >= 180")
                      ->orWhere('gula_darah', '>=', 200)
                      ->orWhere('diagnosa', 'like', '%diabetes%');
                } elseif ($status == 'normal') {
                    $q->whereRaw("CAST(SUBSTRING_INDEX(tekanan_darah, '/', 1) AS UNSIGNED) < 140");
                }
            });
        }

        $lansias = $query->paginate(10)->withQueryString();

        // --- Algoritma Statistik Cerdas ---
        // Mengambil pemeriksaan paling akhir untuk setiap lansia
        $allPemeriksaan = Pemeriksaan::where('kategori_pasien', 'lansia')
            ->whereIn('id', function($q) {
                $q->selectRaw('MAX(id)')->from('pemeriksaans')->groupBy('pasien_id');
            })->get();

        $statistik = (object) [
            'normal' => $allPemeriksaan->filter(function($p) {
                $tensi = intval(explode('/', $p->tekanan_darah)[0] ?? 0);
                return $tensi > 0 && $tensi < 140;
            })->count(),

            'hipertensi' => $allPemeriksaan->filter(function($p) {
                $tensi = intval(explode('/', $p->tekanan_darah)[0] ?? 0);
                $textDiagnosa = strtolower($p->diagnosa ?? '');
                return ($tensi >= 140 && $tensi < 180) || str_contains($textDiagnosa, 'hipertensi');
            })->count(),

            'kritis' => $allPemeriksaan->filter(function($p) {
                $tensi = intval(explode('/', $p->tekanan_darah)[0] ?? 0);
                $textDiagnosa = strtolower($p->diagnosa ?? '');
                return $tensi >= 180 || $p->gula_darah >= 200 || str_contains($textDiagnosa, 'diabetes');
            })->count(),
        ];

        return view('bidan.pasien.lansia', compact('lansias', 'statistik'));
    }
}