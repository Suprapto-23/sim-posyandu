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
use Illuminate\Support\Facades\Schema;

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

        $absensiHariIni = AbsensiDetail::whereHas('absensi', function ($query) use ($today) {
            $query->whereDate('tanggal_posyandu', $today->toDateString());
        });

        $totalAbsensiHariIni = (clone $absensiHariIni)->count();
        $hadirHariIni = (clone $absensiHariIni)->where('hadir', true)->count();

        $targetAbsensiHariIni = $totalAbsensiHariIni > 0
            ? $totalAbsensiHariIni
            : $totalSasaran;

        $persentaseHadir = $targetAbsensiHariIni > 0
            ? round(($hadirHariIni / $targetAbsensiHariIni) * 100, 1)
            : 0;

        $pemeriksaanBase = Pemeriksaan::whereIn('kategori_pasien', $this->kategoriAktif);

        $hasStatusVerifikasi = Schema::hasColumn('pemeriksaans', 'status_verifikasi');
        $hasTanggalPeriksa = Schema::hasColumn('pemeriksaans', 'tanggal_periksa');

        $pengukuranBulanIni = (clone $pemeriksaanBase)
            ->when($hasTanggalPeriksa, function ($query) use ($now) {
                $query->whereMonth('tanggal_periksa', $now->month)
                    ->whereYear('tanggal_periksa', $now->year);
            })
            ->count();

        $pengukuranPending = $hasStatusVerifikasi
            ? (clone $pemeriksaanBase)
                ->where(function ($query) {
                    $query->whereNull('status_verifikasi')
                        ->orWhereIn('status_verifikasi', ['pending', 'menunggu', 'belum_divalidasi']);
                })
                ->count()
            : 0;

        $pengukuranTervalidasi = $hasStatusVerifikasi
            ? (clone $pemeriksaanBase)
                ->whereIn('status_verifikasi', ['verified', 'terverifikasi', 'valid', 'disetujui'])
                ->when($hasTanggalPeriksa, function ($query) use ($now) {
                    $query->whereMonth('tanggal_periksa', $now->month)
                        ->whereYear('tanggal_periksa', $now->year);
                })
                ->count()
            : 0;

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
                'nama' => $item->nama_lengkap ?? $item->nama ?? 'Tanpa Nama',
                'kategori' => 'Balita / Anak',
                'created_at' => $item->created_at,
                'icon' => 'fa-child-reaching',
            ]);

        $remaja = Remaja::latest()
            ->take(5)
            ->get()
            ->map(fn ($item) => (object) [
                'nama' => $item->nama_lengkap ?? $item->nama ?? 'Tanpa Nama',
                'kategori' => 'Remaja',
                'created_at' => $item->created_at,
                'icon' => 'fa-user-graduate',
            ]);

        $lansia = Lansia::latest()
            ->take(5)
            ->get()
            ->map(fn ($item) => (object) [
                'nama' => $item->nama_lengkap ?? $item->nama ?? 'Tanpa Nama',
                'kategori' => 'Lansia',
                'created_at' => $item->created_at,
                'icon' => 'fa-person-cane',
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
        $query = Pemeriksaan::whereIn('kategori_pasien', $this->kategoriAktif)
            ->latest('created_at')
            ->take(5)
            ->get();

        return $query->map(function ($item) {
            $status = $item->status_verifikasi ?? null;

            $statusText = match ($status) {
                'verified', 'terverifikasi', 'valid', 'disetujui' => 'Tervalidasi',
                'rejected', 'ditolak' => 'Ditolak',
                default => 'Menunggu',
            };

            $badge = match ($statusText) {
                'Tervalidasi' => 'emerald',
                'Ditolak' => 'rose',
                default => 'amber',
            };

            return (object) [
                'nama' => $item->nama_pasien
                    ?? $item->pasien?->nama_lengkap
                    ?? $item->kunjungan?->pasien?->nama_lengkap
                    ?? 'Data sasaran',
                'kategori' => ucfirst(str_replace('_', ' ', $item->kategori_pasien ?? '-')),
                'tanggal' => $item->tanggal_periksa ?? $item->created_at,
                'status' => $statusText,
                'badge' => $badge,
            ];
        });
    }
}