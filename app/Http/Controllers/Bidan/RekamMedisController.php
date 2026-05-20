<?php

namespace App\Http\Controllers\Bidan;

use App\Http\Controllers\Controller;
use App\Models\Pemeriksaan;
use App\Models\Imunisasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RekamMedisController extends Controller
{
    /**
     * =========================================================================
     * 1. INDEX: Menampilkan Buku Induk EMR berdasarkan Kategori
     * =========================================================================
     */
    public function index(Request $request)
    {
        try {
            // Default ke balita jika tidak ada parameter
            $type = $request->get('type', 'balita');
            
            // Keamanan: Mencegah injeksi array pada parameter search
            $search = is_array($request->get('search')) ? '' : $request->get('search');

            // Mapping Model Polimorfik
            $models = [
                'balita'    => \App\Models\Balita::class,
                'lansia'    => \App\Models\Lansia::class,
                'remaja'    => \App\Models\Remaja::class,
            ];

            $modelClass = $models[$type] ?? $models['balita'];
            $query = $modelClass::query();

            // Logika Pencarian Maksimal & Anti-Crash
            if (!empty($search)) {
                $query->where(function($q) use ($search, $type) {
                    $q->where('nama_lengkap', 'like', "%{$search}%")
                      ->orWhere('nik', 'like', "%{$search}%");
                    
                    // Khusus balita, Bidan sering mencari menggunakan nama Ibu
                    if($type === 'balita') {
                        $q->orWhere('nama_ibu', 'like', "%{$search}%");
                    }
                });
            }

            // Menggunakan id desc lebih aman dari latest() jika timestamp DB tidak konsisten
            $data = $query->orderBy('id', 'desc')->paginate(12)->withQueryString();

            return view('bidan.rekam-medis.index', compact('data', 'type', 'search'));

        } catch (\Exception $e) {
            Log::error('EMR_INDEX_ERROR: ' . $e->getMessage());
            abort(500, 'Gagal memuat Buku Induk Rekam Medis.');
        }
    }

    /**
     * =========================================================================
     * 2. SHOW: Menampilkan Rekam Medis Elektronik (Detail Pasien)
     * =========================================================================
     */
    public function show($pasien_type, $pasien_id)
    {
        try {
            $models = [
                'balita'    => \App\Models\Balita::class,
                'lansia'    => \App\Models\Lansia::class,
                'remaja'    => \App\Models\Remaja::class,
            ];
            
            $modelClass = $models[$pasien_type] ?? abort(404, 'Kategori Pasien Tidak Valid');
            $pasien = $modelClass::findOrFail($pasien_id);

            // =================================================================
            // PERBAIKAN MUTLAK (THE REAL BUG FIX): 
            // Menarik Pemeriksaan & Imunisasi via Relasi Kunjungan (whereHas)
            // =================================================================
            
            // 1. Riwayat Medis (Meja 5)
            $riwayatMedis = Pemeriksaan::with(['verifikator', 'pemeriksa'])
                ->whereHas('kunjungan', function($q) use ($pasien_id, $modelClass) {
                    $q->where('pasien_id', $pasien_id)
                      ->where('pasien_type', $modelClass);
                })
                ->where('status_verifikasi', 'verified') // HANYA tampilkan yang sudah sah!
                ->latest('created_at')
                ->get();

            // 2. Riwayat Imunisasi / Vaksin
            $riwayatImunisasi = Imunisasi::with(['kunjungan.petugas'])
                ->whereHas('kunjungan', function($q) use ($pasien_id, $modelClass) {
                    $q->where('pasien_id', $pasien_id)
                      ->where('pasien_type', $modelClass);
                })
                ->latest('tanggal_imunisasi')
                ->get();

            // 3. Data Grafik (Ambil 7 data terakhir untuk tren pertumbuhan/kesehatan)
            $chartData = $riwayatMedis->take(7)->reverse()->values();

            return view('bidan.rekam-medis.show', compact('pasien', 'pasien_type', 'riwayatMedis', 'riwayatImunisasi', 'chartData'));

        } catch (\Exception $e) {
            Log::error('EMR_SHOW_ERROR: ' . $e->getMessage());
            return redirect()->route('bidan.rekam-medis.index')->with('error', 'Gagal memuat Rekam Medis: ' . $e->getMessage());
        }
    }
}