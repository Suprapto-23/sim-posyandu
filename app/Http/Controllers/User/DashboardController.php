<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Controllers\User\Concerns\ResolvesUserHealthContext;
use App\Models\Notifikasi;
use App\Models\Pemeriksaan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DashboardController extends Controller
{
    use ResolvesUserHealthContext;

    /**
     * Tampilkan dashboard User dengan data Real-time untuk Grafik dan Notifikasi.
     */
    public function index(): View
    {
        $user = auth()->user();

        // --- CACHE CONTEXT USER (5 menit) ---
        $contextKey = 'user_dashboard_context_' . $user->id;
        $context = Cache::remember($contextKey, 300, function () use ($user) {
            return $this->getUserContext($user);
        });

        // ========================================================================
        // 1. DETEKSI DEMOGRAFI DINAMIS
        // ========================================================================
        $sasaranList = $this->buildSasaranList($context);
        $sasaranAktif = $sasaranList->first();

        $kategoriGrafik = $sasaranAktif['kategori'] ?? null;
        $pasienIdGrafik = $sasaranAktif['id'] ?? null;

        // ========================================================================
        // 2. PEMBARUAN REAL-TIME GRAFIK PERTUMBUHAN
        // ========================================================================
        $grafikPeriode = 'bulanan';
        $grafikData = ($kategoriGrafik && $pasienIdGrafik)
            ? $this->getGrafikPertumbuhan($kategoriGrafik, $pasienIdGrafik, $grafikPeriode)
            : [];

        // --- CACHE JADWAL (5 menit) ---
        $jadwalTerdekat = Cache::remember('user_dashboard_jadwal_' . $user->id, 300, function () use ($context) {
            return $this->getJadwalTerdekat($context['targets'] ?? []);
        });

        // ========================================================================
        // 3. PEMBARUAN REAL-TIME: Notifikasi dan Pemeriksaan
        // ========================================================================
        $notifData = $this->getNotifikasiRingkas((int) $user->id);
        [$notifikasiTerbaru, $totalNotifikasiBelumDibaca] = $notifData;

        $latestPemeriksaan = $this->getLatestPemeriksaan($context);

        $summary = [
            'total_sasaran' => $context['total_sasaran'] ?? 0,
            'total_balita'  => collect($context['balitas'] ?? [])->count(),
            'total_remaja'  => collect($context['remajas'] ?? [])->count(),
            'total_lansia'  => collect($context['lansias'] ?? [])->count(),
            'total_jadwal'  => $jadwalTerdekat->count(),
            'total_notifikasi' => $totalNotifikasiBelumDibaca,
            'total_pemeriksaan' => $latestPemeriksaan->count(),
        ];

        $pesanError = $this->buildPesanError($context);

        return view('user.dashboard', [
            'user' => $user,
            'context' => $context,
            'summary' => $summary,
            'dataAnak' => $context['balitas'] ?? collect(),
            'dataRemaja' => $context['remajas'] ?? collect(),
            'dataLansia' => $context['lansias'] ?? collect(),
            'grafikData' => $grafikData,
            'grafikPeriode' => $grafikPeriode,
            'sasaranList' => $sasaranList,
            'sasaranAktif' => $sasaranAktif,
            'jadwalTerdekat' => $jadwalTerdekat,
            'notifikasiTerbaru' => $notifikasiTerbaru,
            'totalNotifikasiBelumDibaca' => $totalNotifikasiBelumDibaca,
            'pesanError' => $pesanError,
            'latestPemeriksaan' => $latestPemeriksaan,
        ]);
    }

    public function getStats(): JsonResponse
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json(['status' => 'unauthenticated', 'unread_count' => 0]);
            }

            $unreadCount = $this->countUnreadNotifications((int) $user->id);

            $contextKey = 'user_dashboard_context_' . $user->id;
            $context = Cache::get($contextKey);
            if (!$context) {
                $context = $this->getUserContext($user);
                Cache::put($contextKey, $context, 300);
            }

            $totalSasaran = $context['total_sasaran'] ?? 0;
            $jadwalCount = Cache::remember('user_jadwal_count_' . $user->id, 300, function () use ($context) {
                return $this->getJadwalTerdekat($context['targets'] ?? [])->count();
            });

            return response()->json([
                'status' => 'success',
                'unread_count' => $unreadCount,
                'total_sasaran' => $totalSasaran,
                'total_jadwal' => $jadwalCount,
                'updated_at' => now('Asia/Jakarta')->format('H:i:s'),
            ]);
        } catch (\Throwable $e) {
            Log::warning('User dashboard stats error', ['message' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'unread_count' => 0,
                'total_sasaran' => 0,
                'total_jadwal' => 0,
                'updated_at' => now('Asia/Jakarta')->format('H:i:s'),
            ]);
        }
    }

    public function chartData(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json(['status' => 'unauthenticated'], 401);
            }

            $periode = $request->query('periode', 'bulanan');
            $periode = in_array($periode, ['bulanan', 'tahunan'], true) ? $periode : 'bulanan';

            $tahunParam = $request->query('tahun');
            $tahun = (is_numeric($tahunParam) && (int) $tahunParam >= 2000 && (int) $tahunParam <= 2100)
                ? (int) $tahunParam
                : null;

            $contextKey = 'user_dashboard_context_' . $user->id;
            $context = Cache::get($contextKey);
            if (!$context) {
                $context = $this->getUserContext($user);
                Cache::put($contextKey, $context, 300);
            }

            $sasaranList = $this->buildSasaranList($context);

            $requestedKategori = $request->query('kategori');
            $requestedId = $request->query('pasien_id');

            $selected = null;
            if ($requestedKategori && $requestedId) {
                $selected = $sasaranList->first(fn ($item) => $item['kategori'] === $requestedKategori && (string) $item['id'] === (string) $requestedId
                );
            }
            if (!$selected) {
                $selected = $sasaranList->first();
            }

            if (!$selected) {
                return response()->json([
                    'status' => 'empty',
                    'message' => 'Belum ada data sasaran kesehatan untuk ditampilkan.',
                    'sasaran' => [],
                    'updated_at' => now('Asia/Jakarta')->format('H:i:s'),
                ]);
            }

            $grafik = $this->getGrafikPertumbuhan($selected['kategori'], (int) $selected['id'], $periode, $tahun);

            if (empty($grafik['labels'] ?? [])) {
                return response()->json([
                    'status' => 'empty',
                    'message' => 'Data pemeriksaan belum tersedia atau masih menunggu validasi Bidan.',
                    'kategori' => $selected['kategori'],
                    'pasien_id' => $selected['id'],
                    'nama' => $selected['nama'],
                    'periode' => $periode,
                    'tahun' => $grafik['tahun'] ?? $tahun,
                    'available_years' => $grafik['available_years'] ?? [],
                    'sasaran' => $sasaranList->values(),
                    'updated_at' => now('Asia/Jakarta')->format('H:i:s'),
                ]);
            }

            return response()->json([
                'status' => 'success',
                'periode' => $periode,
                'tahun' => $grafik['tahun'] ?? $tahun,
                'available_years' => $grafik['available_years'] ?? [],
                'kosong_tahun_ini' => $grafik['kosong_tahun_ini'] ?? false,
                'kategori' => $selected['kategori'],
                'pasien_id' => $selected['id'],
                'nama' => $selected['nama'],
                'labels' => $grafik['labels'],
                'berat' => $grafik['berat'],
                'tinggi' => $grafik['tinggi'],
                'ringkasan' => $grafik['ringkasan'] ?? null,
                'sasaran' => $sasaranList->values(),
                'updated_at' => now('Asia/Jakarta')->format('H:i:s'),
            ]);
        } catch (\Throwable $e) {
            Log::error('Gagal memuat data grafik dashboard user', ['message' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kendala saat memuat grafik. Coba lagi sebentar.',
                'updated_at' => now('Asia/Jakarta')->format('H:i:s'),
            ], 500);
        }
    }

    // ========== PRIVATE HELPERS ==========

    private function buildSasaranList(array $context): Collection
    {
        $resolveNama = fn ($item) => data_get($item, 'nama_lengkap')
            ?: data_get($item, 'nama_remaja')
            ?: data_get($item, 'nama_balita')
            ?: data_get($item, 'nama_lansia')
            ?: data_get($item, 'nama')
            ?: 'Tanpa Nama';

        $list = collect();

        foreach (collect($context['balitas'] ?? []) as $item) {
            $list->push(['kategori' => 'balita', 'id' => data_get($item, 'id'), 'nama' => $resolveNama($item), 'label' => 'Balita']);
        }
        foreach (collect($context['remajas'] ?? []) as $item) {
            $list->push(['kategori' => 'remaja', 'id' => data_get($item, 'id'), 'nama' => $resolveNama($item), 'label' => 'Remaja']);
        }
        foreach (collect($context['lansias'] ?? []) as $item) {
            $list->push(['kategori' => 'lansia', 'id' => data_get($item, 'id'), 'nama' => $resolveNama($item), 'label' => 'Lansia']);
        }

        return $list->filter(fn ($item) => filled($item['id']))->values();
    }

    private function getGrafikPertumbuhan(?string $kategori, ?int $id, string $periode = 'bulanan', ?int $tahun = null): array
    {
        if (!$id || !$kategori || !Schema::hasTable('pemeriksaans')) return [];

        $periode = in_array($periode, ['bulanan', 'tahunan'], true) ? $periode : 'bulanan';

        try {
            $availableYears = $this->getAvailableYears($kategori, $id);
            $ringkasan = $this->getRingkasanTerakhir($kategori, $id);

            if (empty($availableYears) && !$ringkasan) return [];

            $dateExpr = $this->tanggalExpr();
            $query = Pemeriksaan::query();
            $this->constrainPemeriksaanPatient($query, $kategori, $id);
            $this->applyVerifiedFilter($query);

            if ($periode === 'tahunan') {
                $rows = $query->selectRaw("YEAR($dateExpr) as label_waktu, AVG(berat_badan) as avg_berat, AVG(tinggi_badan) as avg_tinggi")
                    ->groupBy('label_waktu')
                    ->orderBy('label_waktu')
                    ->get();
                
                if ($rows->isEmpty()) return [];
                
                return [
                    'labels' => $rows->pluck('label_waktu')->toArray(),
                    'berat' => $rows->map(fn($r) => is_null($r->avg_berat) ? null : round((float) $r->avg_berat, 1))->toArray(),
                    'tinggi' => $rows->map(fn($r) => is_null($r->avg_tinggi) ? null : round((float) $r->avg_tinggi, 1))->toArray(),
                    'ringkasan' => $ringkasan,
                    'tahun' => null,
                    'available_years' => $availableYears,
                ];
            }

            // --- MODE BULANAN ---
            // Membiarkan controller menerima tahun berapapun yang diminta UI tanpa paksaan fallback.
            $tahunDipakai = $tahun ?: ($availableYears[0] ?? (int) now('Asia/Jakarta')->format('Y'));

            // Ambil rata-rata per bulan pada tahun yang dipilih
            $rows = $query->selectRaw("MONTH($dateExpr) as bulan, AVG(berat_badan) as avg_berat, AVG(tinggi_badan) as avg_tinggi")
                ->whereRaw("YEAR($dateExpr) = ?", [$tahunDipakai])
                ->groupBy('bulan')
                ->get()
                ->keyBy('bulan');

            $labels = [];
            $berat = [];
            $tinggi = [];

            $currentYear = (int) now('Asia/Jakarta')->format('Y');
            $currentMonth = (int) now('Asia/Jakarta')->format('n');

            for ($bulanKe = 1; $bulanKe <= 12; $bulanKe++) {
                $labels[] = Carbon::createFromDate($tahunDipakai, $bulanKe, 1)->translatedFormat('M');
                
                // Jangan tampilkan bulan masa depan di tahun berjalan
                if ($tahunDipakai === $currentYear && $bulanKe > $currentMonth) {
                    break;
                }

                if ($rows->has($bulanKe)) {
                    $berat[] = round((float) $rows->get($bulanKe)->avg_berat, 1);
                    $tinggi[] = round((float) $rows->get($bulanKe)->avg_tinggi, 1);
                } else {
                    // Biarkan null agar Chart.js menggambar garis bersambung (spanGaps)
                    $berat[] = null;
                    $tinggi[] = null;
                }
            }

            $adaDataTahunIni = collect($berat)->merge($tinggi)->filter(fn($v) => !is_null($v))->isNotEmpty();

            return [
                'labels' => $labels,
                'berat' => $berat,
                'tinggi' => $tinggi,
                'ringkasan' => $ringkasan,
                'tahun' => $tahunDipakai,
                'available_years' => $availableYears,
                'kosong_tahun_ini' => !$adaDataTahunIni,
            ];
        } catch (\Throwable $e) {
            Log::error("Gagal memuat grafik pertumbuhan: " . $e->getMessage());
            return [];
        }
    }

    private function tanggalExpr(): string
    {
        return Schema::hasColumn('pemeriksaans', 'tanggal_periksa')
            ? 'COALESCE(tanggal_periksa, created_at)'
            : 'created_at';
    }

    private function getAvailableYears(string $kategori, int $id): array
    {
        if (!Schema::hasTable('pemeriksaans')) return [];

        try {
            $dateExpr = $this->tanggalExpr();
            $query = Pemeriksaan::query()->selectRaw("DISTINCT YEAR($dateExpr) as tahun");
            $this->constrainPemeriksaanPatient($query, $kategori, $id);
            $this->applyVerifiedFilter($query);

            return $query->orderByDesc('tahun')
                ->pluck('tahun')
                ->filter(fn ($t) => filled($t))
                ->map(fn ($t) => (int) $t)
                ->values()
                ->all();
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function getRingkasanTerakhir(string $kategori, int $id): ?array
    {
        if (!Schema::hasTable('pemeriksaans')) return null;

        try {
            $countQuery = Pemeriksaan::query();
            $this->constrainPemeriksaanPatient($countQuery, $kategori, $id);
            $this->applyVerifiedFilter($countQuery);
            $jumlahTotal = $countQuery->count();

            if ($jumlahTotal === 0) return null;

            $dataQuery = Pemeriksaan::query()
                ->select(['id', 'tanggal_periksa', 'berat_badan', 'tinggi_badan', 'created_at']);
            $this->constrainPemeriksaanPatient($dataQuery, $kategori, $id);
            $this->applyVerifiedFilter($dataQuery);

            $duaTerakhir = $dataQuery
                ->orderByDesc('tanggal_periksa')
                ->orderByDesc('created_at')
                ->limit(2)
                ->get()
                ->map(function ($item) {
                    $item->tanggal_efektif = Carbon::parse($item->tanggal_periksa ?? $item->created_at);
                    return $item;
                });

            if ($duaTerakhir->isEmpty()) return null;

            $terakhir = $duaTerakhir->first();
            $sebelum = $duaTerakhir->count() > 1 ? $duaTerakhir->get(1) : null;

            return [
                'berat_terakhir'   => filled($terakhir->berat_badan) ? (float) $terakhir->berat_badan : null,
                'tinggi_terakhir'  => filled($terakhir->tinggi_badan) ? (float) $terakhir->tinggi_badan : null,
                'tanggal_terakhir' => $terakhir->tanggal_efektif->translatedFormat('d M Y'),
                'tren_berat'       => $this->hitungTren($sebelum->berat_badan ?? null, $terakhir->berat_badan),
                'tren_tinggi'      => $this->hitungTren($sebelum->tinggi_badan ?? null, $terakhir->tinggi_badan),
                'jumlah_data'      => $jumlahTotal,
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function hitungTren($sebelum, $sekarang): ?string
    {
        if (!filled($sebelum) || !filled($sekarang)) return null;

        $selisih = (float) $sekarang - (float) $sebelum;
        if (abs($selisih) < 0.05) return 'stabil';

        return $selisih > 0 ? 'naik' : 'turun';
    }

    private function getJadwalTerdekat(array $targets): Collection
    {
        try {
            if (!class_exists(\App\Models\JadwalPosyandu::class)) {
                return collect();
            }
            return $this->buildUserJadwalQuery($targets)
                ->whereDate('tanggal', '>=', now('Asia/Jakarta')->toDateString())
                ->limit(5)
                ->get();
        } catch (\Throwable $e) {
            return collect();
        }
    }

    private function getNotifikasiRingkas(int $userId): array
    {
        try {
            if (!Schema::hasTable('notifikasis')) {
                return [collect(), 0];
            }

            $items = Notifikasi::query()
                ->where('user_id', $userId)
                ->latest()
                ->limit(5)
                ->get()
                ->map(fn ($item) => $this->formatNotifikasi($item));

            return [$items, $this->countUnreadNotifications($userId)];
        } catch (\Throwable $e) {
            return [collect(), 0];
        }
    }

    private function formatNotifikasi(Notifikasi $item): array
    {
        return [
            'id' => $item->id,
            'judul' => $item->judul ?: 'Pemberitahuan',
            'pesan' => Str::limit((string) ($item->pesan ?? ''), 90),
            'tipe' => $item->tipe ?? 'info',
            'label' => $item->tipe_label ?? 'Pemberitahuan',
            'color' => $item->tipe_color ?? 'emerald',
            'link' => $item->link ?: '#',
            'is_read' => (bool) ($item->is_read ?? false),
            'waktu' => $item->created_at ? $item->created_at->diffForHumans() : '-',
        ];
    }

    private function countUnreadNotifications(int $userId): int
    {
        try {
            if (!Schema::hasTable('notifikasis')) return 0;
            $query = Notifikasi::query()->where('user_id', $userId);
            if (Schema::hasColumn('notifikasis', 'is_read')) {
                return (clone $query)->where('is_read', false)->count();
            }
            if (Schema::hasColumn('notifikasis', 'read_at')) {
                return (clone $query)->whereNull('read_at')->count();
            }
            return 0;
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function getLatestPemeriksaan(array $context): Collection
    {
        if (!Schema::hasTable('pemeriksaans')) return collect();

        try {
            $balitaIds = collect($context['balitas'] ?? [])->pluck('id')->filter()->values();
            $remajaIds = collect($context['remajas'] ?? [])->pluck('id')->filter()->values();
            $lansiaIds = collect($context['lansias'] ?? [])->pluck('id')->filter()->values();

            if ($balitaIds->isEmpty() && $remajaIds->isEmpty() && $lansiaIds->isEmpty()) {
                return collect();
            }

            $query = Pemeriksaan::query()
                ->with(['balita:id,nama_lengkap,nik', 'remaja:id,nama_lengkap,nik', 'lansia:id,nama_lengkap,nik'])
                ->where(function (Builder $outer) use ($balitaIds, $remajaIds, $lansiaIds) {
                    $this->addKategoriPatientCondition($outer, 'balita', $balitaIds);
                    $this->addKategoriPatientCondition($outer, 'remaja', $remajaIds);
                    $this->addKategoriPatientCondition($outer, 'lansia', $lansiaIds);
                    if (Schema::hasColumn('pemeriksaans', 'balita_id') && $balitaIds->isNotEmpty())
                        $outer->orWhereIn('balita_id', $balitaIds);
                    if (Schema::hasColumn('pemeriksaans', 'remaja_id') && $remajaIds->isNotEmpty())
                        $outer->orWhereIn('remaja_id', $remajaIds);
                    if (Schema::hasColumn('pemeriksaans', 'lansia_id') && $lansiaIds->isNotEmpty())
                        $outer->orWhereIn('lansia_id', $lansiaIds);
                });

            $this->applyVerifiedFilter($query);

            return $query
                ->orderByDesc('tanggal_periksa')
                ->orderByDesc('created_at')
                ->limit(5)
                ->get();
        } catch (\Throwable $e) {
            return collect();
        }
    }

    private function addKategoriPatientCondition(Builder $query, string $kategori, Collection $ids): void
    {
        if ($ids->isEmpty() || !Schema::hasColumn('pemeriksaans', 'kategori_pasien') || !Schema::hasColumn('pemeriksaans', 'pasien_id')) {
            return;
        }
        $query->orWhere(function (Builder $q) use ($kategori, $ids) {
            $q->where('kategori_pasien', $kategori)->whereIn('pasien_id', $ids);
        });
    }

    private function constrainPemeriksaanPatient(Builder $query, string $kategori, int $id): void
    {
        $query->where(function (Builder $q) use ($kategori, $id) {
            $hasKategoriPasien = Schema::hasColumn('pemeriksaans', 'kategori_pasien') && Schema::hasColumn('pemeriksaans', 'pasien_id');
            if ($hasKategoriPasien) {
                $q->where(function (Builder $inner) use ($kategori, $id) {
                    $inner->where('kategori_pasien', $kategori)->where('pasien_id', $id);
                });
            }
            $foreignKey = $kategori . '_id';
            if (Schema::hasColumn('pemeriksaans', $foreignKey)) {
                $hasKategoriPasien ? $q->orWhere($foreignKey, $id) : $q->where($foreignKey, $id);
            }
        });
    }

    private function applyVerifiedFilter(Builder $query): void
    {
        if (!Schema::hasColumn('pemeriksaans', 'status_verifikasi')) return;
        $query->whereIn('status_verifikasi', ['tervalidasi', 'verified', 'approved']);
    }

    private function buildPesanError(array $context): ?string
    {
        if (!in_array('umum', $context['peran'] ?? ['umum'], true)) return null;
        if (blank($context['nik'] ?? null)) {
            return 'NIK belum diisi. Lengkapi profil agar data kesehatan dari Posyandu dapat ditampilkan.';
        }
        return 'NIK ' . $context['nik'] . ' belum terdaftar pada data sasaran Posyandu. Hubungi Kader untuk sinkronisasi data.';
    }

    public static function flushCache(int $userId)
    {
        Cache::forget('user_dashboard_context_' . $userId);
        Cache::forget('user_dashboard_jadwal_' . $userId);
        Cache::forget('user_dashboard_notif_' . $userId);
        Cache::forget('user_unread_notif_' . $userId);
        Cache::forget('user_jadwal_count_' . $userId);
    }
}