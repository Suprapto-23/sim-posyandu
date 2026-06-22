<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Controllers\User\Concerns\ResolvesUserHealthContext;
use App\Models\Notifikasi;
use App\Models\Pemeriksaan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
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
     * Tampilkan dashboard User dengan data teroptimasi.
     */
    public function index(): View
    {
        $user = auth()->user();

        // --- CACHE CONTEXT USER (5 menit) ---
        $contextKey = 'user_dashboard_context_' . $user->id;
        $context = Cache::remember($contextKey, 300, function () use ($user) {
            return $this->getUserContext($user);
        });

        // --- CACHE GRAFIK BALITA (5 menit) ---
        $balitaId = collect($context['balitas'] ?? [])->first()?->id;
        $grafikData = $balitaId
            ? Cache::remember('user_dashboard_grafik_' . $balitaId, 300, function () use ($balitaId) {
                return $this->getGrafikBalita($balitaId);
            })
            : [];

        // --- CACHE JADWAL (5 menit) ---
        $jadwalTerdekat = Cache::remember('user_dashboard_jadwal_' . $user->id, 300, function () use ($context) {
            return $this->getJadwalTerdekat($context['targets'] ?? []);
        });

        // --- CACHE NOTIFIKASI (2 menit, lebih sering update) ---
        $notifData = Cache::remember('user_dashboard_notif_' . $user->id, 120, function () use ($user) {
            return $this->getNotifikasiRingkas((int) $user->id);
        });
        [$notifikasiTerbaru, $totalNotifikasiBelumDibaca] = $notifData;

        // --- DATA DINAMIS (tidak di-cache) ---
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
            // PERBAIKAN BUG KUNCI: Pastikan memanggil 'remajas' dan 'lansias' menggunakan 's'
            'dataAnak' => $context['balitas'] ?? collect(),
            'dataRemaja' => $context['remajas'] ?? collect(),
            'dataLansia' => $context['lansias'] ?? collect(),
            'grafikData' => $grafikData,
            'jadwalTerdekat' => $jadwalTerdekat,
            'notifikasiTerbaru' => $notifikasiTerbaru,
            'totalNotifikasiBelumDibaca' => $totalNotifikasiBelumDibaca,
            'pesanError' => $pesanError,
            'latestPemeriksaan' => $latestPemeriksaan,
        ]);
    }

    /**
     * Endpoint AJAX untuk polling statistik cepat.
     */
    public function getStats(): JsonResponse
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json(['status' => 'unauthenticated', 'unread_count' => 0]);
            }

            // Baca dari cache untuk kecepatan
            $unreadCount = Cache::remember('user_unread_notif_' . $user->id, 60, function () use ($user) {
                return $this->countUnreadNotifications((int) $user->id);
            });

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
                'updated_at' => now('Asia/Jakarta')->format('H:i'),
            ]);
        } catch (\Throwable $e) {
            Log::warning('User dashboard stats error', ['message' => $e->getMessage()]);
            return response()->json([
                'status' => 'success',
                'unread_count' => 0,
                'total_sasaran' => 0,
                'total_jadwal' => 0,
                'updated_at' => now('Asia/Jakarta')->format('H:i'),
            ]);
        }
    }

    // ========== PRIVATE HELPERS (dengan cache tambahan) ==========

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

    private function getGrafikBalita(?int $balitaId): array
    {
        if (!$balitaId || !Schema::hasTable('pemeriksaans')) return [];

        try {
            $query = Pemeriksaan::query()
                ->select(['id', 'tanggal_periksa', 'berat_badan', 'tinggi_badan', 'created_at']);
            $this->constrainPemeriksaanPatient($query, 'balita', $balitaId);
            $this->applyVerifiedFilter($query);

            $items = $query
                ->orderByDesc('tanggal_periksa')
                ->orderByDesc('created_at')
                ->limit(12)
                ->get()
                ->sortBy(fn ($item) => $item->tanggal_periksa ?? $item->created_at)
                ->values();

            if ($items->isEmpty()) return [];

            return [
                'labels' => $items->map(fn ($item) => Carbon::parse($item->tanggal_periksa ?? $item->created_at)->translatedFormat('M y'))->toArray(),
                'berat' => $items->map(fn ($item) => filled($item->berat_badan) ? (float) $item->berat_badan : null)->toArray(),
                'tinggi' => $items->map(fn ($item) => filled($item->tinggi_badan) ? (float) $item->tinggi_badan : null)->toArray(),
            ];
        } catch (\Throwable $e) {
            return [];
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