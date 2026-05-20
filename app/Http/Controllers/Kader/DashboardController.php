<?php

namespace App\Http\Controllers\Kader;

use App\Http\Controllers\Controller;
use App\Models\AbsensiDetail;
use App\Models\Balita;
use App\Models\JadwalPosyandu;
use App\Models\Lansia;
use App\Models\Pemeriksaan;
use App\Models\Remaja;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    private array $kategoriAktif = ['balita', 'remaja', 'lansia'];

    public function index()
    {
        $today = Carbon::today('Asia/Jakarta');
        $now = Carbon::now('Asia/Jakarta');

        $totalBalita = Balita::count();
        $totalRemaja = Remaja::count();
        $totalLansia = Lansia::count();
        $totalSasaran = $totalBalita + $totalRemaja + $totalLansia;

        $absensiQueryHariIni = AbsensiDetail::whereHas('absensi', function ($query) use ($today) {
            $query->whereDate('tanggal_posyandu', $today->toDateString());
        });

        $totalAbsensiTercatat = (clone $absensiQueryHariIni)->count();
        $hadirHariIni = (clone $absensiQueryHariIni)->where('hadir', true)->count();

        $targetAbsensiHariIni = $totalAbsensiTercatat > 0
            ? $totalAbsensiTercatat
            : $totalSasaran;

        $persentaseHadir = $targetAbsensiHariIni > 0
            ? round(($hadirHariIni / $targetAbsensiHariIni) * 100, 1)
            : 0;

        $pengukuranPending = Pemeriksaan::whereIn('kategori_pasien', $this->kategoriAktif)
            ->pending()
            ->count();

        $pengukuranBulanIni = Pemeriksaan::whereIn('kategori_pasien', $this->kategoriAktif)
            ->bulanIni()
            ->count();

        $pengukuranTervalidasi = Pemeriksaan::whereIn('kategori_pasien', $this->kategoriAktif)
            ->verified()
            ->bulanIni()
            ->count();

        $jadwalHariIni = JadwalPosyandu::whereDate('tanggal', $today->toDateString())
            ->where('status', 'aktif')
            ->orderBy('waktu_mulai')
            ->first();

        $jadwalMendatang = JadwalPosyandu::whereDate('tanggal', '>=', $today->toDateString())
            ->where('status', 'aktif')
            ->orderBy('tanggal')
            ->orderBy('waktu_mulai')
            ->take(4)
            ->get();

        [$chartLabels, $chartData] = $this->getAbsensi7Hari();

        $stats = [
            'total_sasaran' => $totalSasaran,
            'total_balita' => $totalBalita,
            'total_remaja' => $totalRemaja,
            'total_lansia' => $totalLansia,

            'hadir_hari_ini' => $hadirHariIni,
            'target_absensi_hari_ini' => $targetAbsensiHariIni,
            'persentase_hari_ini' => $persentaseHadir,

            'pengukuran_pending' => $pengukuranPending,
            'pengukuran_bulan_ini' => $pengukuranBulanIni,
            'pengukuran_tervalidasi' => $pengukuranTervalidasi,
        ];

        $laporanBulanan = [
            'periode' => $now->translatedFormat('F Y'),
            'jumlah_jadwal' => JadwalPosyandu::whereMonth('tanggal', $now->month)
                ->whereYear('tanggal', $now->year)
                ->count(),
            'jumlah_hadir' => AbsensiDetail::where('hadir', true)
                ->whereHas('absensi', function ($query) use ($now) {
                    $query->whereMonth('tanggal_posyandu', $now->month)
                        ->whereYear('tanggal_posyandu', $now->year);
                })
                ->count(),
            'jumlah_pengukuran' => $pengukuranBulanIni,
        ];

        return view('kader.dashboard', [
            'stats' => $stats,
            'chartLabels' => $chartLabels,
            'chartData' => $chartData,
            'jadwalHariIni' => $jadwalHariIni,
            'jadwalMendatang' => $jadwalMendatang,
            'sasaranBaru' => $this->getSasaranTerbaru(),
            'pengukuranTerbaru' => $this->getPengukuranTerbaru(),
            'laporanBulanan' => $laporanBulanan,
        ]);
    }

    private function getAbsensi7Hari(): array
    {
        $labels = [];
        $data = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today('Asia/Jakarta')->subDays($i);

            $labels[] = $date->translatedFormat('d M');

            $data[] = AbsensiDetail::where('hadir', true)
                ->whereHas('absensi', function ($query) use ($date) {
                    $query->whereDate('tanggal_posyandu', $date->toDateString());
                })
                ->count();
        }

        return [$labels, $data];
    }

    private function getSasaranTerbaru(): Collection
    {
        $balita = Balita::latest()
            ->take(5)
            ->get()
            ->map(fn ($item) => (object) [
                'nama' => $item->nama_lengkap,
                'kategori' => 'Balita / Anak',
                'created_at' => $item->created_at,
                'icon' => 'fa-child-reaching',
                'color' => 'emerald',
            ]);

        $remaja = Remaja::latest()
            ->take(5)
            ->get()
            ->map(fn ($item) => (object) [
                'nama' => $item->nama_lengkap,
                'kategori' => 'Remaja',
                'created_at' => $item->created_at,
                'icon' => 'fa-user-graduate',
                'color' => 'indigo',
            ]);

        $lansia = Lansia::latest()
            ->take(5)
            ->get()
            ->map(fn ($item) => (object) [
                'nama' => $item->nama_lengkap,
                'kategori' => 'Lansia',
                'created_at' => $item->created_at,
                'icon' => 'fa-person-cane',
                'color' => 'amber',
            ]);

        return $balita
            ->merge($remaja)
            ->merge($lansia)
            ->sortByDesc('created_at')
            ->take(6)
            ->values();
    }

    private function getPengukuranTerbaru(): Collection
    {
        return Pemeriksaan::with(['kunjungan.pasien', 'pemeriksa'])
            ->whereIn('kategori_pasien', $this->kategoriAktif)
            ->latest('created_at')
            ->take(5)
            ->get()
            ->map(fn ($item) => (object) [
                'nama' => $item->nama_pasien,
                'kategori' => ucfirst($item->kategori_pasien ?? '-'),
                'tanggal' => $item->tanggal_periksa,
                'status' => $item->status_verifikasi_text,
                'badge' => $item->status_verifikasi_badge,
            ]);
    }
}