<?php

namespace App\Http\Controllers\Bidan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

// Pemanggilan Seluruh Model Terkait
use App\Models\Balita;
use App\Models\Remaja;
use App\Models\Lansia;
use App\Models\Pemeriksaan;
use App\Models\JadwalPosyandu;

class DashboardController extends Controller
{
    /**
     * Menampilkan Halaman Command Center Utama Bidan
     */
    public function index()
    {
        // 1. STATISTIK UTAMA (Beban Kerja Hari Ini & Total Cakupan)
        $stats = [
            'total_pasien' => Balita::count() + Remaja::count() + Lansia::count(),
            'menunggu_validasi' => Pemeriksaan::where('status_verifikasi', 'pending')->count(),
            'selesai_divalidasi' => Pemeriksaan::where('status_verifikasi', 'verified')
                                        ->whereDate('tanggal_periksa', Carbon::today())
                                        ->count(),
            'jadwal_hari_ini' => JadwalPosyandu::whereDate('tanggal', Carbon::today())
                                        ->where('status', 'aktif')
                                        ->count(),
        ];

        // 2. ALERT RISIKO KRITIS (Deteksi Dini Pasien Berisiko Tinggi Hari Ini)
        // GANTI MENJADI INI:
$pemeriksaanHariIni = Pemeriksaan::whereDate('created_at', Carbon::today())->get();

        $alertRisiko = [
            
            // Lansia dengan Gula Darah >= 200 (Indikasi Diabetes)
            'lansia_metabolik' => $pemeriksaanHariIni->filter(function($p) {
                if (strtolower($p->kategori_pasien) !== 'lansia') return false;
                return (int)($p->gula_darah ?? 0) >= 200;
            })->count(),

            // Balita dengan Gizi Kurang/Buruk (Berdasarkan IMT sementara)
            'balita_waspada' => $pemeriksaanHariIni->filter(function($p) {
                if (strtolower($p->kategori_pasien) !== 'balita') return false;
                $imt = (float) $p->imt;
                return $imt > 0 && $imt < 13.5;
            })->count(),
        ];

        // 3. ANTREAN LIVE MEJA 5 (5 Pasien Terakhir)
        // Memakai kunjungan.pasien agar model polimorfik terpanggil dengan benar
        $antrianLive = Pemeriksaan::with(['kunjungan.pasien', 'pemeriksa'])
                            ->where('status_verifikasi', 'pending')
                            ->latest('created_at')
                            ->take(5)
                            ->get();

        // 4. DATA GRAFIK KINERJA (Tren Penyelesaian 7 Hari Terakhir)
        $trend = $this->getTrend7Hari();
        $chartLabels = $trend['labels'];
        $chartData = $trend['data'];

        // 5. DATA GRAFIK DEMOGRAFI (Donut Chart)
        $demografi = [
            'balita'    => Balita::count(),
            'remaja'    => Remaja::count(),
            'lansia'    => Lansia::count(),
        ];

        return view('bidan.dashboard', compact(
            'stats', 
            'alertRisiko', 
            'antrianLive', 
            'chartLabels', 
            'chartData',
            'demografi'
        ));
    }

    /**
     * FUNGSI BANTUAN: Menghitung Jumlah Pasien Selesai Periksa per Hari (7 Hari Terakhir)
     */
    private function getTrend7Hari()
    {
        $labels = [];
        $data = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $labels[] = $date->translatedFormat('d M'); 
            
            $count = Pemeriksaan::where('status_verifikasi', 'verified')
                                ->whereDate('tanggal_periksa', $date->format('Y-m-d'))
                                ->count();
            $data[] = $count;
        }

        return [
            'labels' => $labels,
            'data'   => $data
        ];
    }
}