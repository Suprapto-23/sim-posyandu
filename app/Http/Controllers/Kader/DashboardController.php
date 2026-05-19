<?php

namespace App\Http\Controllers\Kader;

use App\Http\Controllers\Controller;
use App\Models\Balita;
use App\Models\Remaja;
use App\Models\Lansia;
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
            // 1. FILTERING AKURAT MEDIS (Balita wajib 12-59 Bulan)
            $totalBalita = Balita::whereRaw('TIMESTAMPDIFF(MONTH, tanggal_lahir, CURDATE()) BETWEEN 12 AND 59')->count();
            $totalRemaja = Remaja::count();
            $totalLansia = Lansia::count();

            // 2. KALKULASI CAKUPAN / PERSENTASE KEHADIRAN (Disukai Dosen)
            $totalWargaSistem = $totalBalita + $totalRemaja + $totalLansia;
            $kehadiranHariIni = Kunjungan::whereDate('tanggal_kunjungan', today())->count();
            
            $persentaseKehadiran = $totalWargaSistem > 0 ? round(($kehadiranHariIni / $totalWargaSistem) * 100, 1) : 0;

            $stats = [
                'total_balita'       => $totalBalita,
                'total_remaja'       => $totalRemaja,
                'total_lansia'       => $totalLansia,
                'kehadiran_hari_ini' => $kehadiranHariIni,
                'persentase_hari_ini'=> $persentaseKehadiran,
                'total_warga'        => $totalWargaSistem
            ];

            // 3. GENERATE GRAFIK KUNJUNGAN (7 Hari Mundur)
            [$chartLabels, $chartData] = $this->getAbsensi7Hari();

            // 4. REGISTRASI SASARAN TERBARU (5 Data Terakhir)
            $sasaran_baru = Balita::latest()->take(5)->get();

            // 5. AGENDA POSYANDU MENDATANG
            $jadwal_mendatang = JadwalPosyandu::where('tanggal', '>=', today())
                ->where('status', 'aktif')
                ->orderBy('tanggal', 'asc')
                ->take(4)
                ->get();

            return view('kader.dashboard', compact(
                'stats',
                'chartLabels',
                'chartData',
                'sasaran_baru',
                'jadwal_mendatang'
            ));

        } catch (\Exception $e) {
            Log::error('KADER_DASHBOARD_CRASH: ' . $e->getMessage());
            // Memberikan pesan error yang jelas dan tidak membuat blank putih
            return response()->view('errors.500', ['message' => 'Terjadi kesalahan saat memuat analitik dashboard. Pastikan tabel terhubung.'], 500);
        }
    }

    /**
     * Mesin Analitik: Trafik Kehadiran 7 Hari Terakhir
     */
    private function getAbsensi7Hari()
    {
        $labels = [];
        $data   = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $labels[] = $date->translatedFormat('d M'); 
            $data[] = Kunjungan::whereDate('tanggal_kunjungan', $date->format('Y-m-d'))->count();
        }

        return [$labels, $data];
    }
}