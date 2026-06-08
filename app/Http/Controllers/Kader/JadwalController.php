<?php

namespace App\Http\Controllers\Kader;

use App\Http\Controllers\Controller;
use App\Models\JadwalPosyandu;
use App\Models\Notifikasi;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class JadwalController extends Controller
{
    private string $timezone = 'Asia/Jakarta';

    public function index(Request $request): View
    {
        $this->syncExpiredSchedules();

        $filters = $this->filters($request);
        $stats = $this->stats();
        $initialPayload = $this->buildLivePayload($filters);

        return view('kader.jadwal.index', compact(
            'filters',
            'stats',
            'initialPayload'
        ));
    }

    public function show(JadwalPosyandu $jadwal): View|RedirectResponse
    {
        try {
            $this->syncSingleScheduleStatus($jadwal);

            $jadwal->refresh();

            $initialItem = $this->formatItem($jadwal);

            return view('kader.jadwal.show', compact('jadwal', 'initialItem'));
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->route('kader.jadwal.index')
                ->with('error', 'Detail jadwal Posyandu tidak ditemukan.');
        }
    }

    public function live(Request $request): JsonResponse
    {
        $this->syncExpiredSchedules();

        $filters = $this->filters($request);

        return response()->json($this->buildLivePayload($filters));
    }

    public function liveShow(JadwalPosyandu $jadwal): JsonResponse
    {
        $this->syncSingleScheduleStatus($jadwal);

        $jadwal->refresh();

        $item = $this->formatItem($jadwal);

        return response()->json([
            'ok' => true,
            'item' => $item,
            'hash' => md5(json_encode($item)),
            'server_time' => now($this->timezone)->format('H:i:s'),
            'unread_jadwal_notifikasi' => $this->unreadJadwalNotificationCount(),
        ]);
    }

    private function buildLivePayload(array $filters): array
    {
        $items = $this->baseQuery($filters)
            ->limit(120)
            ->get()
            ->map(fn (JadwalPosyandu $jadwal) => $this->formatItem($jadwal))
            ->values();

        $stats = $this->stats();

        $latestId = (int) ($items->max('id') ?? 0);

        $hashSource = $items
            ->map(fn ($item) => $item['id'] . '-' . $item['updated_at'] . '-' . $item['status'])
            ->implode('|');

        $unread = $this->unreadJadwalNotificationCount();

        return [
            'ok' => true,
            'items' => $items,
            'stats' => $stats,
            'latest_id' => $latestId,
            'hash' => md5($hashSource . '|notif:' . $unread),
            'server_time' => now($this->timezone)->format('H:i:s'),
            'unread_jadwal_notifikasi' => $unread,
        ];
    }

    private function baseQuery(array $filters)
    {
        $query = JadwalPosyandu::query();

        $search = trim((string) ($filters['search'] ?? ''));
        $kategori = $filters['kategori'] ?? 'semua';
        $periode = $filters['periode'] ?? 'semua';

        if ($search !== '') {
            $query->where(function ($subQuery) use ($search) {
                foreach (['judul', 'lokasi', 'deskripsi', 'kategori', 'target_peserta'] as $column) {
                    if ($this->hasJadwalColumn($column)) {
                        $subQuery->orWhere($column, 'like', '%' . $search . '%');
                    }
                }
            });
        }

        if ($kategori !== 'semua') {
            $query->where(function ($subQuery) use ($kategori) {
                if ($this->hasJadwalColumn('kategori')) {
                    $subQuery->where('kategori', $kategori);
                }

                if ($this->hasJadwalColumn('target_peserta')) {
                    $subQuery->orWhere('target_peserta', $kategori);
                }
            });
        }

        $today = now($this->timezone);

        match ($periode) {
            'hari_ini' => $query->whereDate('tanggal', $today->toDateString()),
            'minggu_ini' => $query->whereBetween('tanggal', [
                $today->copy()->startOfWeek()->toDateString(),
                $today->copy()->endOfWeek()->toDateString(),
            ]),
            'bulan_ini' => $query
                ->whereMonth('tanggal', $today->month)
                ->whereYear('tanggal', $today->year),
            'mendatang' => $query->whereDate('tanggal', '>=', $today->toDateString()),
            'selesai' => $query->where(function ($subQuery) use ($today) {
                $subQuery->whereDate('tanggal', '<', $today->toDateString());

                if ($this->hasJadwalColumn('status')) {
                    $subQuery->orWhere('status', 'selesai');
                }
            }),
            default => null,
        };

        return $query
            ->orderByRaw(
                'CASE WHEN tanggal >= ? THEN 0 ELSE 1 END',
                [$today->toDateString()]
            )
            ->orderBy('tanggal')
            ->orderBy('waktu_mulai')
            ->orderByDesc('id');
    }

    private function filters(Request $request): array
    {
        return [
            'search' => trim((string) $request->query('search', '')),
            'kategori' => $request->query('kategori', 'semua'),
            'periode' => $request->query('periode', 'semua'),
        ];
    }

    private function stats(): array
    {
        $today = now($this->timezone);

        return [
            'semua' => JadwalPosyandu::count(),
            'aktif' => JadwalPosyandu::query()
                ->when($this->hasJadwalColumn('status'), fn ($query) => $query->where('status', 'aktif'))
                ->count(),
            'mendatang' => JadwalPosyandu::whereDate('tanggal', '>=', $today->toDateString())->count(),
            'selesai' => JadwalPosyandu::query()
                ->where(function ($query) use ($today) {
                    $query->whereDate('tanggal', '<', $today->toDateString());

                    if ($this->hasJadwalColumn('status')) {
                        $query->orWhere('status', 'selesai');
                    }
                })
                ->count(),
        ];
    }

    private function formatItem(JadwalPosyandu $jadwal): array
    {
        $tanggalRaw = $jadwal->tanggal ? Carbon::parse($jadwal->tanggal, $this->timezone) : null;

        $waktuMulai = $this->formatTime($jadwal->waktu_mulai ?? null);
        $waktuSelesai = $this->formatTime($jadwal->waktu_selesai ?? null);

        $waktuLabel = trim(($waktuMulai ?: '') . (($waktuMulai && $waktuSelesai) ? ' - ' : '') . ($waktuSelesai ?: ''));

        if ($waktuLabel === '') {
            $waktuLabel = '-';
        }

        $kategori = strtolower((string) ($jadwal->kategori ?? 'posyandu'));
        $status = strtolower((string) ($jadwal->status ?: $this->detectStatus($jadwal)));

        return [
            'id' => $jadwal->id,
            'judul' => $jadwal->judul ?: 'Jadwal Posyandu',
            'deskripsi' => $jadwal->deskripsi ?: 'Agenda pelayanan Posyandu untuk persiapan kegiatan Kader.',
            'catatan' => $jadwal->catatan ?: 'Tidak ada catatan tambahan.',
            'tanggal_raw' => $tanggalRaw?->toDateString(),
            'tanggal_label' => $tanggalRaw?->locale('id')->translatedFormat('d F Y') ?: '-',
            'tanggal_detail' => $tanggalRaw?->locale('id')->translatedFormat('l, d F Y') ?: '-',
            'waktu_label' => $waktuLabel,
            'lokasi' => $jadwal->lokasi ?: 'Lokasi belum ditentukan',
            'kategori' => $kategori,
            'kategori_label' => $this->kategoriLabel($kategori),
            'target' => $jadwal->target_peserta ?: 'semua',
            'target_label' => $this->targetLabel($jadwal->target_peserta ?: 'semua'),
            'status' => $status,
            'status_label' => $this->statusLabel($status),
            'created_by_name' => data_get($jadwal, 'creator.name') ?: data_get($jadwal, 'dibuatOleh.name') ?: 'Bidan Posyandu',
            'show_url' => route('kader.jadwal.show', $jadwal),
            'updated_at' => optional($jadwal->updated_at)->timestamp ?: 0,
            'created_at' => optional($jadwal->created_at)->timestamp ?: 0,
        ];
    }

    private function formatTime($value): ?string
    {
        if (!$value) {
            return null;
        }

        try {
            return Carbon::parse((string) $value)->format('H:i');
        } catch (\Throwable) {
            $value = (string) $value;

            return strlen($value) >= 5 ? substr($value, 0, 5) : $value;
        }
    }

    private function detectStatus(JadwalPosyandu $jadwal): string
    {
        if (!$jadwal->tanggal) {
            return 'terjadwal';
        }

        $today = now($this->timezone)->toDateString();

        if ($jadwal->tanggal < $today) {
            return 'selesai';
        }

        return 'aktif';
    }

    private function syncExpiredSchedules(): void
    {
        if (!$this->hasJadwalColumn('status')) {
            return;
        }

        JadwalPosyandu::query()
            ->whereDate('tanggal', '<', now($this->timezone)->toDateString())
            ->whereNotIn('status', ['selesai', 'dibatalkan'])
            ->update(['status' => 'selesai']);
    }

    private function syncSingleScheduleStatus(JadwalPosyandu $jadwal): void
    {
        if (!$this->hasJadwalColumn('status')) {
            return;
        }

        if (!$jadwal->tanggal || in_array($jadwal->status, ['selesai', 'dibatalkan'], true)) {
            return;
        }

        if ($jadwal->tanggal < now($this->timezone)->toDateString()) {
            $jadwal->update(['status' => 'selesai']);
        }
    }

    private function unreadJadwalNotificationCount(): int
    {
        if (!Auth::check()) {
            return 0;
        }

        try {
            return Notifikasi::query()
                ->where('user_id', Auth::id())
                ->where('tipe', 'jadwal')
                ->where('is_read', 0)
                ->count();
        } catch (\Throwable) {
            return 0;
        }
    }

    private function kategoriLabel(string $kategori): string
    {
        return match ($kategori) {
            'balita' => 'Balita',
            'remaja' => 'Remaja',
            'lansia' => 'Lansia',
            'imunisasi' => 'Imunisasi',
            'pemeriksaan' => 'Pemeriksaan',
            'lainnya' => 'Lainnya',
            default => 'Posyandu Rutin',
        };
    }

    private function targetLabel(string $target): string
    {
        return match ($target) {
            'balita' => 'Balita',
            'remaja' => 'Remaja',
            'lansia' => 'Lansia',
            'semua' => 'Semua Sasaran',
            default => Str::title(str_replace('_', ' ', $target)),
        };
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'aktif' => 'Aktif',
            'selesai' => 'Selesai',
            'dibatalkan' => 'Dibatalkan',
            default => 'Terjadwal',
        };
    }

    private function hasJadwalColumn(string $column): bool
    {
        try {
            return Schema::hasColumn((new JadwalPosyandu())->getTable(), $column);
        } catch (\Throwable) {
            return false;
        }
    }
}