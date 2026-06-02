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
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    private array $kategoriAktif = ['balita', 'remaja', 'lansia'];

    public function index()
    {
        Carbon::setLocale('id');

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

        $hasStatusVerifikasi = Schema::hasColumn('pemeriksaans', 'status_verifikasi');
        $hasTanggalPeriksa = Schema::hasColumn('pemeriksaans', 'tanggal_periksa');

        $pemeriksaanBase = Pemeriksaan::whereIn('kategori_pasien', $this->kategoriAktif);

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
                        ->orWhereIn('status_verifikasi', [
                            'pending',
                            'menunggu',
                            'belum_divalidasi',
                        ]);
                })
                ->count()
            : 0;

        $pengukuranTervalidasi = $hasStatusVerifikasi
            ? (clone $pemeriksaanBase)
                ->whereIn('status_verifikasi', [
                    'verified',
                    'terverifikasi',
                    'valid',
                    'disetujui',
                    'approved',
                    'tervalidasi',
                ])
                ->when($hasTanggalPeriksa, function ($query) use ($now) {
                    $query->whereMonth('tanggal_periksa', $now->month)
                        ->whereYear('tanggal_periksa', $now->year);
                })
                ->count()
            : 0;

        $pengukuranRevisi = $hasStatusVerifikasi
            ? (clone $pemeriksaanBase)
                ->whereIn('status_verifikasi', [
                    'rejected',
                    'ditolak',
                    'revisi',
                    'perlu_revisi',
                ])
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

        $trend = $this->buildTrendData(7);

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
            'pengukuran_revisi' => $pengukuranRevisi,
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
            'jumlah_tervalidasi' => $pengukuranTervalidasi,
        ];

        return view('kader.dashboard', [
            'stats' => $stats,
            'trend' => $trend,
            'jadwalHariIni' => $jadwalHariIni,
            'jadwalMendatang' => $jadwalMendatang,
            'sasaranBaru' => $this->getSasaranTerbaru(),
            'pengukuranTerbaru' => $this->getPengukuranTerbaru(),
            'laporanBulanan' => $laporanBulanan,
        ]);
    }

    public function trend(Request $request): JsonResponse
    {
        $range = (int) $request->get('range', 7);

        if (!in_array($range, [7, 14, 30], true)) {
            $range = 7;
        }

        return response()->json($this->buildTrendData($range));
    }

    private function buildTrendData(int $range = 7): array
    {
        Carbon::setLocale('id');

        $labels = [];
        $hadir = [];
        $pengukuran = [];

        $hasTanggalPeriksa = Schema::hasColumn('pemeriksaans', 'tanggal_periksa');

        for ($i = $range - 1; $i >= 0; $i--) {
            $date = Carbon::today('Asia/Jakarta')->subDays($i);

            $labels[] = $date->translatedFormat('d M');

            $hadir[] = AbsensiDetail::where('hadir', true)
                ->whereHas('absensi', function ($query) use ($date) {
                    $query->whereDate('tanggal_posyandu', $date->toDateString());
                })
                ->count();

            $pengukuran[] = Pemeriksaan::whereIn('kategori_pasien', $this->kategoriAktif)
                ->when($hasTanggalPeriksa, function ($query) use ($date) {
                    $query->whereDate('tanggal_periksa', $date->toDateString());
                })
                ->when(!$hasTanggalPeriksa, function ($query) use ($date) {
                    $query->whereDate('created_at', $date->toDateString());
                })
                ->count();
        }

        return [
            'labels' => $labels,
            'series' => [
                [
                    'name' => 'Kehadiran',
                    'data' => $hadir,
                ],
                [
                    'name' => 'Pengukuran',
                    'data' => $pengukuran,
                ],
            ],
            'summary' => [
                'total_hadir' => array_sum($hadir),
                'total_pengukuran' => array_sum($pengukuran),
                'range' => $range,
                'last_update' => Carbon::now('Asia/Jakarta')->translatedFormat('d M Y H:i'),
            ],
        ];
    }

    private function getSasaranTerbaru(): Collection
    {
        $balita = Balita::latest()
            ->take(5)
            ->get()
            ->map(fn ($item) => (object) [
                'nama' => $item->nama_lengkap ?? $item->nama ?? 'Tanpa Nama',
                'kategori' => 'Balita',
                'created_at' => $item->created_at,
                'icon' => 'fa-child-reaching',
                'tone' => 'sky',
            ]);

        $remaja = Remaja::latest()
            ->take(5)
            ->get()
            ->map(fn ($item) => (object) [
                'nama' => $item->nama_lengkap ?? $item->nama ?? 'Tanpa Nama',
                'kategori' => 'Remaja',
                'created_at' => $item->created_at,
                'icon' => 'fa-user-graduate',
                'tone' => 'violet',
            ]);

        $lansia = Lansia::latest()
            ->take(5)
            ->get()
            ->map(fn ($item) => (object) [
                'nama' => $item->nama_lengkap ?? $item->nama ?? 'Tanpa Nama',
                'kategori' => 'Lansia',
                'created_at' => $item->created_at,
                'icon' => 'fa-person-cane',
                'tone' => 'emerald',
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
        return Pemeriksaan::with(['kunjungan.pasien'])
            ->whereIn('kategori_pasien', $this->kategoriAktif)
            ->latest('created_at')
            ->take(6)
            ->get()
            ->map(function ($item) {
                $status = $item->status_verifikasi ?? null;

                $statusText = match ($status) {
                    'verified', 'terverifikasi', 'valid', 'disetujui', 'approved', 'tervalidasi' => 'Tervalidasi',
                    'rejected', 'ditolak', 'revisi', 'perlu_revisi' => 'Perlu Revisi',
                    default => 'Menunggu',
                };

                $badge = match ($statusText) {
                    'Tervalidasi' => 'emerald',
                    'Perlu Revisi' => 'rose',
                    default => 'amber',
                };

                return (object) [
                    'nama' => $item->nama_pasien
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