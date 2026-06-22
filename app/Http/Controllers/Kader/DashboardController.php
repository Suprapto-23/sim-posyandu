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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    private array $kategoriAktif = ['balita', 'remaja', 'lansia'];

    /**
     * Tampilkan dashboard Kader dengan data teroptimasi.
     */
    public function index()
    {
        Carbon::setLocale('id');

        // Ambil kategori aktif sebagai variabel lokal untuk closure cache
        $kategoriAktif = $this->kategoriAktif;

        // --- CACHE STATISTIK (5 menit) ---
        $stats = Cache::remember('kader_dashboard_stats', 300, function () use ($kategoriAktif) {
            $today = Carbon::today('Asia/Jakarta');
            $now = Carbon::now('Asia/Jakarta');

            // Total sasaran per kategori
            $totalBalita = Balita::count();
            $totalRemaja = Remaja::count();
            $totalLansia = Lansia::count();
            $totalSasaran = $totalBalita + $totalRemaja + $totalLansia;

            // Absensi hari ini
            $absensiHariIni = AbsensiDetail::whereHas('absensi', function ($query) use ($today) {
                $query->whereDate('tanggal_posyandu', $today->toDateString());
            });
            $totalAbsensiHariIni = (clone $absensiHariIni)->count();
            $hadirHariIni = (clone $absensiHariIni)->where('hadir', true)->count();
            $targetAbsensiHariIni = $totalAbsensiHariIni > 0 ? $totalAbsensiHariIni : $totalSasaran;
            $persentaseHadir = $targetAbsensiHariIni > 0
                ? round(($hadirHariIni / $targetAbsensiHariIni) * 100, 1)
                : 0;

            // Pemeriksaan pending (menunggu review)
            $hasStatusVerifikasi = Schema::hasColumn('pemeriksaans', 'status_verifikasi');
            $pemeriksaanBase = Pemeriksaan::whereIn('kategori_pasien', $kategoriAktif);

            $pengukuranPending = $hasStatusVerifikasi
                ? (clone $pemeriksaanBase)
                    ->where(function ($query) {
                        $query->whereNull('status_verifikasi')
                            ->orWhereIn('status_verifikasi', ['pending', 'menunggu', 'belum_divalidasi']);
                    })
                    ->count()
                : 0;

            // Ringkasan (untuk breakdown)
            return [
                'total_sasaran'       => $totalSasaran,
                'total_balita'        => $totalBalita,
                'total_remaja'        => $totalRemaja,
                'total_lansia'        => $totalLansia,
                'hadir_hari_ini'      => $hadirHariIni,
                'persentase_hari_ini' => $persentaseHadir,
                'pengukuran_pending'  => $pengukuranPending,
            ];
        });

        // --- DATA DINAMIS (tidak di-cache, selalu fresh) ---
        $today = Carbon::today('Asia/Jakarta');

        // Jadwal hari ini
        $jadwalHariIni = JadwalPosyandu::whereDate('tanggal', $today->toDateString())
            ->where('status', 'aktif')
            ->orderBy('waktu_mulai')
            ->first();

        // 4 jadwal mendatang
        $jadwalMendatang = JadwalPosyandu::whereDate('tanggal', '>=', $today->toDateString())
            ->where('status', 'aktif')
            ->orderBy('tanggal')
            ->orderBy('waktu_mulai')
            ->take(4)
            ->get();

        // --- CACHE TREND (5 menit) ---
        $trend = Cache::remember('kader_dashboard_trend', 300, function () {
            return $this->buildTrendData(7);
        });

        // --- CACHE LAPORAN BULANAN (5 menit) ---
        $laporanBulanan = Cache::remember('kader_dashboard_laporan_bulanan', 300, function () {
            $now = Carbon::now('Asia/Jakarta');
            $hasTanggalPeriksa = Schema::hasColumn('pemeriksaans', 'tanggal_periksa');

            $pengukuranBulanIni = Pemeriksaan::whereIn('kategori_pasien', $this->kategoriAktif)
                ->when($hasTanggalPeriksa, function ($query) use ($now) {
                    $query->whereMonth('tanggal_periksa', $now->month)
                        ->whereYear('tanggal_periksa', $now->year);
                })
                ->count();

            return [
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
        });

        // Data sasaran terbaru (union query, tidak di-cache agar selalu update)
        $sasaranBaru = $this->getSasaranTerbaru();

        // Data pengukuran terbaru (langsung, tidak di-cache)
        $pengukuranTerbaru = $this->getPengukuranTerbaru();

        return view('kader.dashboard', [
            'stats'              => $stats,
            'trend'              => $trend,
            'jadwalHariIni'      => $jadwalHariIni,
            'jadwalMendatang'    => $jadwalMendatang,
            'sasaranBaru'        => $sasaranBaru,
            'pengukuranTerbaru'  => $pengukuranTerbaru,
            'laporanBulanan'     => $laporanBulanan,
        ]);
    }

    /**
     * Endpoint AJAX untuk memperbarui grafik trend (range 7/14/30 hari).
     */
    public function trend(Request $request): JsonResponse
    {
        $range = (int) $request->get('range', 7);
        if (!in_array($range, [7, 14, 30], true)) {
            $range = 7;
        }

        // Bisa di-cache per range jika perlu, tapi cukup fresh
        $data = $this->buildTrendData($range);

        return response()->json($data);
    }

    /**
     * Build data trend kehadiran dan pengukuran per hari.
     * Dioptimasi dengan GROUP BY dan JOIN.
     */
    private function buildTrendData(int $range = 7): array
    {
        Carbon::setLocale('id');

        // Siapkan array tanggal
        $dates = [];
        for ($i = $range - 1; $i >= 0; $i--) {
            $dates[] = Carbon::today('Asia/Jakarta')->subDays($i)->toDateString();
        }

        $hasTanggalPeriksa = Schema::hasColumn('pemeriksaans', 'tanggal_periksa');

        // --- QUERY KEHADIRAN (1 query) ---
        $hadirData = DB::table('absensi_detail as ad')
            ->selectRaw('DATE(ap.tanggal_posyandu) as date, COUNT(*) as total')
            ->join('absensi_posyandu as ap', 'ad.absensi_id', '=', 'ap.id')
            ->where('ad.hadir', true)
            ->whereIn('ap.tanggal_posyandu', $dates)
            ->groupBy('date')
            ->pluck('total', 'date');

        // --- QUERY PENGUKURAN (1 query) ---
        $pengukuranData = Pemeriksaan::selectRaw('DATE(tanggal_periksa) as date, COUNT(*) as total')
            ->whereIn('kategori_pasien', $this->kategoriAktif)
            ->where(function ($query) use ($dates, $hasTanggalPeriksa) {
                if ($hasTanggalPeriksa) {
                    $query->whereIn('tanggal_periksa', $dates);
                } else {
                    $query->whereIn('created_at', $dates);
                }
            })
            ->groupBy('date')
            ->pluck('total', 'date');

        // Mapping
        $labels = [];
        $hadir = [];
        $pengukuran = [];

        foreach ($dates as $date) {
            $labels[] = Carbon::parse($date)->translatedFormat('d M');
            $hadir[] = $hadirData->get($date, 0);
            $pengukuran[] = $pengukuranData->get($date, 0);
        }

        return [
            'labels' => $labels,
            'series' => [
                ['name' => 'Kehadiran', 'data' => $hadir],
                ['name' => 'Pengukuran', 'data' => $pengukuran],
            ],
            'summary' => [
                'total_hadir' => array_sum($hadir),
                'total_pengukuran' => array_sum($pengukuran),
                'range' => $range,
                'last_update' => Carbon::now('Asia/Jakarta')->translatedFormat('d M Y H:i'),
            ],
        ];
    }

    /**
     * Data sasaran terbaru (union 3 tabel) – lebih cepat dari 3 query terpisah.
     */
    private function getSasaranTerbaru(): Collection
    {
        $balita = Balita::select(
            'id',
            'nama_lengkap as nama',
            DB::raw("'Balita' as kategori"),
            'created_at'
        )->latest()->limit(5);

        $remaja = Remaja::select(
            'id',
            'nama_lengkap as nama',
            DB::raw("'Remaja' as kategori"),
            'created_at'
        )->latest()->limit(5);

        $lansia = Lansia::select(
            'id',
            'nama_lengkap as nama',
            DB::raw("'Lansia' as kategori"),
            'created_at'
        )->latest()->limit(5);

        return $balita->union($remaja)
            ->union($lansia)
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get()
            ->map(function ($item) {
                return (object) [
                    'nama'      => $item->nama,
                    'kategori'  => $item->kategori,
                    'created_at' => $item->created_at,
                    'icon' => match ($item->kategori) {
                        'Balita' => 'fa-child-reaching',
                        'Remaja' => 'fa-user-graduate',
                        'Lansia' => 'fa-person-cane',
                        default => 'fa-user',
                    },
                    'tone' => match ($item->kategori) {
                        'Balita' => 'sky',
                        'Remaja' => 'violet',
                        'Lansia' => 'emerald',
                        default => 'slate',
                    },
                ];
            });
    }

    /**
     * Data pengukuran terbaru (5 data terakhir).
     */
    private function getPengukuranTerbaru(): Collection
    {
        return Pemeriksaan::with(['kunjungan.pasien'])
            ->whereIn('kategori_pasien', $this->kategoriAktif)
            ->where(function ($query) {
                // Hanya tampilkan yang sudah diverifikasi (agar tidak menampilkan pending)
                if (Schema::hasColumn('pemeriksaans', 'status_verifikasi')) {
                    $query->whereIn('status_verifikasi', [
                        'verified', 'terverifikasi', 'valid', 'disetujui', 'approved', 'tervalidasi'
                    ]);
                }
            })
            ->latest('tanggal_periksa')
            ->latest('created_at')
            ->take(5)
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
                    'parameter' => $this->extractParameter($item, $item->kategori_pasien ?? ''),
                ];
            });
    }

    /**
     * Helper untuk mengambil parameter kunci dari pemeriksaan.
     */
    private function extractParameter($pemeriksaan, string $kategori): array
    {
        if ($kategori === 'lansia') {
            return [
                'Tensi' => $pemeriksaan->tekanan_darah ?? '-',
                'Gula' => $pemeriksaan->gula_darah ? $pemeriksaan->gula_darah . ' mg/dL' : '-',
                'Kolesterol' => $pemeriksaan->kolesterol ? $pemeriksaan->kolesterol . ' mg/dL' : '-',
            ];
        }

        if ($kategori === 'remaja') {
            return [
                'BB' => $pemeriksaan->berat_badan ? $pemeriksaan->berat_badan . ' kg' : '-',
                'TB' => $pemeriksaan->tinggi_badan ? $pemeriksaan->tinggi_badan . ' cm' : '-',
                'IMT' => $pemeriksaan->imt ?? '-',
            ];
        }

        // Balita
        return [
            'BB' => $pemeriksaan->berat_badan ? $pemeriksaan->berat_badan . ' kg' : '-',
            'TB' => $pemeriksaan->tinggi_badan ? $pemeriksaan->tinggi_badan . ' cm' : '-',
            'LK' => $pemeriksaan->lingkar_kepala ? $pemeriksaan->lingkar_kepala . ' cm' : '-',
            'Gizi' => $pemeriksaan->status_gizi ?? '-',
        ];
    }

    /**
     * Flush cache ketika ada perubahan data (opsional, panggil di controller store/update/delete).
     * Contoh: dipanggil setelah menyimpan absensi, pemeriksaan, atau data sasaran baru.
     */
    public static function flushCache()
    {
        Cache::forget('kader_dashboard_stats');
        Cache::forget('kader_dashboard_trend');
        Cache::forget('kader_dashboard_laporan_bulanan');
    }
}