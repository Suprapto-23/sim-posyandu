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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DashboardController extends Controller
{
    use ResolvesUserHealthContext;

    public function index(): View
    {
        $user = auth()->user();

        $context = $this->getUserContext($user);

        $dataAnak = $context['balitas'];
        $dataRemaja = $context['remaja'];
        $dataLansia = $context['lansia'];

        $grafikData = $this->getGrafikBalita($dataAnak->first()?->id);
        $jadwalTerdekat = $this->getJadwalTerdekat($context['targets']);
        $latestPemeriksaan = $this->getLatestPemeriksaan($context);

        [$notifikasiTerbaru, $totalNotifikasiBelumDibaca] = $this->getNotifikasiRingkas((int) $user->id);

        $summary = [
            'total_sasaran' => $context['total_sasaran'],
            'total_balita' => $context['balitas']->count(),
            'total_remaja' => $context['remajas']->count(),
            'total_lansia' => $context['lansias']->count(),
            'total_jadwal' => $jadwalTerdekat->count(),
            'total_notifikasi' => $totalNotifikasiBelumDibaca,
            'total_pemeriksaan' => $latestPemeriksaan->count(),
        ];

        $pesanError = $this->buildPesanError($context);

        return view('user.dashboard', [
            'user' => $user,

            // Variable lama tetap dikirim agar Blade lama tidak langsung rusak.
            'peranUser' => $context['peran'],
            'nikUser' => $context['nik'],
            'dataAnak' => $dataAnak,
            'dataRemaja' => $dataRemaja,
            'dataLansia' => $dataLansia,
            'grafikData' => $grafikData,
            'jadwalTerdekat' => $jadwalTerdekat,
            'notifikasiTerbaru' => $notifikasiTerbaru,
            'totalNotifikasiBelumDibaca' => $totalNotifikasiBelumDibaca,
            'pesanError' => $pesanError,

            // Variable baru untuk dashboard Nexus Premium.
            'context' => $context,
            'summary' => $summary,
            'latestPemeriksaan' => $latestPemeriksaan,
        ]);
    }

    public function getStats(): JsonResponse
    {
        try {
            $user = auth()->user();

            if (! $user) {
                return response()->json([
                    'status' => 'unauthenticated',
                    'unread_count' => 0,
                    'total_sasaran' => 0,
                    'total_jadwal' => 0,
                ]);
            }

            $context = $this->getUserContext($user);
            $jadwalTerdekat = $this->getJadwalTerdekat($context['targets']);

            return response()->json([
                'status' => 'success',
                'unread_count' => $this->countUnreadNotifications((int) $user->id),
                'total_sasaran' => $context['total_sasaran'],
                'total_jadwal' => $jadwalTerdekat->count(),
                'updated_at' => now('Asia/Jakarta')->format('H:i'),
            ]);
        } catch (\Throwable $e) {
            Log::warning('User dashboard stats error', [
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'success',
                'unread_count' => 0,
                'total_sasaran' => 0,
                'total_jadwal' => 0,
                'updated_at' => now('Asia/Jakarta')->format('H:i'),
            ]);
        }
    }

    private function getJadwalTerdekat(array $targets): Collection
    {
        try {
            if (! class_exists(\App\Models\JadwalPosyandu::class)) {
                return collect();
            }

            return $this->buildUserJadwalQuery($targets)
                ->whereDate('tanggal', '>=', now('Asia/Jakarta')->toDateString())
                ->limit(5)
                ->get();
        } catch (\Throwable $e) {
            Log::warning('User dashboard jadwal error', [
                'message' => $e->getMessage(),
            ]);

            return collect();
        }
    }

    private function getNotifikasiRingkas(int $userId): array
    {
        try {
            if (! Schema::hasTable('notifikasis')) {
                return [collect(), 0];
            }

            $items = Notifikasi::query()
                ->where('user_id', $userId)
                ->latest()
                ->limit(5)
                ->get()
                ->map(fn (Notifikasi $item) => $this->formatNotifikasi($item));

            return [
                $items,
                $this->countUnreadNotifications($userId),
            ];
        } catch (\Throwable $e) {
            Log::warning('User dashboard notifikasi error', [
                'message' => $e->getMessage(),
            ]);

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
            'waktu' => $item->created_at
                ? $item->created_at->diffForHumans()
                : '-',
        ];
    }

    private function countUnreadNotifications(int $userId): int
    {
        try {
            if (! Schema::hasTable('notifikasis')) {
                return 0;
            }

            $query = Notifikasi::query()->where('user_id', $userId);

            if (Schema::hasColumn('notifikasis', 'is_read')) {
                return (clone $query)
                    ->where('is_read', false)
                    ->count();
            }

            if (Schema::hasColumn('notifikasis', 'read_at')) {
                return (clone $query)
                    ->whereNull('read_at')
                    ->count();
            }

            return 0;
        } catch (\Throwable $e) {
            Log::warning('User dashboard unread notification error', [
                'message' => $e->getMessage(),
            ]);

            return 0;
        }
    }

    private function getGrafikBalita(?int $balitaId): array
    {
        if (! $balitaId || ! Schema::hasTable('pemeriksaans')) {
            return [];
        }

        try {
            $query = Pemeriksaan::query()
                ->select([
                    'id',
                    'tanggal_periksa',
                    'berat_badan',
                    'tinggi_badan',
                    'created_at',
                ]);

            $this->constrainPemeriksaanPatient($query, 'balita', $balitaId);
            $this->applyVerifiedFilter($query);

            $items = $query
                ->orderByDesc('tanggal_periksa')
                ->orderByDesc('created_at')
                ->limit(12)
                ->get()
                ->sortBy(fn ($item) => $item->tanggal_periksa ?? $item->created_at)
                ->values();

            if ($items->isEmpty()) {
                return [];
            }

            return [
                'labels' => $items
                    ->map(fn ($item) => Carbon::parse($item->tanggal_periksa ?? $item->created_at)->translatedFormat('M y'))
                    ->values()
                    ->toArray(),

                'berat' => $items
                    ->map(fn ($item) => filled($item->berat_badan) ? (float) $item->berat_badan : null)
                    ->values()
                    ->toArray(),

                'tinggi' => $items
                    ->map(fn ($item) => filled($item->tinggi_badan) ? (float) $item->tinggi_badan : null)
                    ->values()
                    ->toArray(),
            ];
        } catch (\Throwable $e) {
            Log::warning('User dashboard grafik balita error', [
                'message' => $e->getMessage(),
                'balita_id' => $balitaId,
            ]);

            return [];
        }
    }

    private function getLatestPemeriksaan(array $context): Collection
    {
        if (! Schema::hasTable('pemeriksaans')) {
            return collect();
        }

        try {
            $balitaIds = $context['balitas']->pluck('id')->filter()->values();
            $remajaIds = $context['remajas']->pluck('id')->filter()->values();
            $lansiaIds = $context['lansias']->pluck('id')->filter()->values();

            if ($balitaIds->isEmpty() && $remajaIds->isEmpty() && $lansiaIds->isEmpty()) {
                return collect();
            }

            $query = Pemeriksaan::query()
                ->with([
                    'balita:id,nama_lengkap,nik',
                    'remaja:id,nama_lengkap,nik',
                    'lansia:id,nama_lengkap,nik',
                ])
                ->where(function (Builder $outer) use ($balitaIds, $remajaIds, $lansiaIds) {
                    $this->addKategoriPatientCondition($outer, 'balita', $balitaIds);
                    $this->addKategoriPatientCondition($outer, 'remaja', $remajaIds);
                    $this->addKategoriPatientCondition($outer, 'lansia', $lansiaIds);

                    if (Schema::hasColumn('pemeriksaans', 'balita_id') && $balitaIds->isNotEmpty()) {
                        $outer->orWhereIn('balita_id', $balitaIds);
                    }

                    if (Schema::hasColumn('pemeriksaans', 'remaja_id') && $remajaIds->isNotEmpty()) {
                        $outer->orWhereIn('remaja_id', $remajaIds);
                    }

                    if (Schema::hasColumn('pemeriksaans', 'lansia_id') && $lansiaIds->isNotEmpty()) {
                        $outer->orWhereIn('lansia_id', $lansiaIds);
                    }
                });

            $this->applyVerifiedFilter($query);

            return $query
                ->orderByDesc('tanggal_periksa')
                ->orderByDesc('created_at')
                ->limit(5)
                ->get();
        } catch (\Throwable $e) {
            Log::warning('User dashboard latest pemeriksaan error', [
                'message' => $e->getMessage(),
            ]);

            return collect();
        }
    }

    private function addKategoriPatientCondition(Builder $query, string $kategori, Collection $ids): void
    {
        if (
            $ids->isEmpty()
            || ! Schema::hasColumn('pemeriksaans', 'kategori_pasien')
            || ! Schema::hasColumn('pemeriksaans', 'pasien_id')
        ) {
            return;
        }

        $query->orWhere(function (Builder $q) use ($kategori, $ids) {
            $q->where('kategori_pasien', $kategori)
                ->whereIn('pasien_id', $ids);
        });
    }

    private function constrainPemeriksaanPatient(Builder $query, string $kategori, int $id): void
    {
        $query->where(function (Builder $q) use ($kategori, $id) {
            $hasKategoriPasien = Schema::hasColumn('pemeriksaans', 'kategori_pasien')
                && Schema::hasColumn('pemeriksaans', 'pasien_id');

            if ($hasKategoriPasien) {
                $q->where(function (Builder $inner) use ($kategori, $id) {
                    $inner->where('kategori_pasien', $kategori)
                        ->where('pasien_id', $id);
                });
            }

            $foreignKey = $kategori . '_id';

            if (Schema::hasColumn('pemeriksaans', $foreignKey)) {
                $hasKategoriPasien
                    ? $q->orWhere($foreignKey, $id)
                    : $q->where($foreignKey, $id);
            }
        });
    }

    private function applyVerifiedFilter(Builder $query): void
    {
        if (! Schema::hasColumn('pemeriksaans', 'status_verifikasi')) {
            return;
        }

        $query->whereIn('status_verifikasi', [
            'tervalidasi',
            'verified',
            'approved',
        ]);
    }

    private function buildPesanError(array $context): ?string
    {
        if (! in_array('umum', $context['peran'], true)) {
            return null;
        }

        if (blank($context['nik'])) {
            return 'NIK belum diisi. Lengkapi profil agar data kesehatan dari Posyandu dapat ditampilkan.';
        }

        return 'NIK ' . $context['nik'] . ' belum terdaftar pada data sasaran Posyandu. Hubungi Kader untuk sinkronisasi data.';
    }
}