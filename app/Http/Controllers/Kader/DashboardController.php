<?php

namespace App\Http\Controllers\Kader;

use App\Http\Controllers\Controller;
use App\Models\Balita;
use App\Models\Remaja;
use App\Models\Lansia;
use App\Models\IbuHamil;
use App\Models\Kunjungan;
use App\Models\JadwalPosyandu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            // 1. LOGIKA CERDAS: Pemisahan Umur Bayi (<1 Tahun) & Balita (1-5 Tahun)
            $batasBayi = Carbon::now()->subYear(1);

            $totalBayi   = Balita::whereDate('tanggal_lahir', '>=', $batasBayi)->count();
            $totalBalita = Balita::whereDate('tanggal_lahir', '<', $batasBayi)->count();

            // 2. KUMPULAN METRIK UTAMA (Untuk 6 Kartu Statistik di Atas)
            $stats = [
                'total_bayi'         => $totalBayi,
                'total_balita'       => $totalBalita,
                'total_remaja'       => Remaja::count(),
                'total_lansia'       => Lansia::count(),
                'total_ibu_hamil'    => IbuHamil::where('status', 'aktif')->count(), // Pastikan hanya menghitung bumil aktif
                'kehadiran_hari_ini' => Kunjungan::whereDate('tanggal_kunjungan', today())->count(),
                'jadwal_hari_ini'    => JadwalPosyandu::whereDate('tanggal', today())
                                        ->where('status', 'aktif')
                                        ->count(),
            ];

            // 3. DATA GRAFIK KUNJUNGAN (7 Hari Terakhir)
            $trendAbsensi = $this->getAbsensi7Hari();
            $chartLabels  = $trendAbsensi['labels'];
            $chartData    = $trendAbsensi['data'];

            // 4. PENDAFTARAN WARGA BARU BULAN INI
            $pendaftaran_bulan_ini = [
                'bayi_balita' => Balita::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
                'remaja'      => Remaja::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
                'lansia'      => Lansia::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
                'ibu_hamil'   => IbuHamil::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
            ];

            // 5. LIST AKTIVITAS TERKINI (Balita yang baru didaftarkan)
            $balita_baru = Balita::latest()->take(5)->get();

            // 6. LIST AGENDA MENDATANG
            $jadwal_mendatang = JadwalPosyandu::where('tanggal', '>=', today())
                ->where('status', 'aktif')
                ->orderBy('tanggal', 'asc')
                ->take(4)
                ->get();

            // 7. RENDER KE VIEW
            return view('kader.dashboard', compact(
                'stats',
                'chartLabels',
                'chartData',
                'pendaftaran_bulan_ini',
                'balita_baru',
                'jadwal_mendatang'
            ));

        } catch (\Exception $e) {
            // Jika terjadi error database, catat di log dan kembalikan dengan aman
            Log::error('KADER_DASHBOARD_ERROR: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan sistem saat memuat data dashboard.');
        }
    }

    /**
     * HELPER: Mengambil total kehadiran/kunjungan selama 7 hari terakhir secara mundur.
     */
    private function getAbsensi7Hari()
    {
        $labels = [];
        $data   = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $labels[] = $date->translatedFormat('d M'); // Contoh: 10 Mei
            
            $count = Kunjungan::whereDate('tanggal_kunjungan', $date->format('Y-m-d'))->count();
            $data[] = $count;
        }

        return ['labels' => $labels, 'data' => $data];
    }
}