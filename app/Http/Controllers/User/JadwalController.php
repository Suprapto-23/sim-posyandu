<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Controllers\User\Concerns\ResolvesUserHealthContext;
use App\Models\JadwalPosyandu;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class JadwalController extends Controller
{
    use ResolvesUserHealthContext;

    public function index(Request $request): View
    {
        try {
            $user = auth()->user();
            
            // SINKRONISASI CACHE AGAR SMART FILTERING BEKERJA CEPAT
            $contextKey = 'user_dashboard_context_' . $user->id;
            $context = Cache::remember($contextKey, 300, function () use ($user) {
                return $this->getUserContext($user);
            });

            $hakAkses = $this->resolveHakAkses($context);

            $filters = [
                'search' => trim((string) $request->input('search', '')),
                'filter' => $this->normalizeFilter($request->input('filter', 'semua')),
                'periode' => $this->normalizePeriode($request->input('periode', 'semua')),
            ];

            $query = $this->baseQuery($hakAkses);

            $this->applyTargetFilter($query, $hakAkses, $filters['filter']);
            $this->applyPeriodeFilter($query, $filters['periode']);
            $this->applySearchFilter($query, $filters['search']);
            $this->applySmartOrdering($query);

            $jadwalKegiatan = $query
                ->paginate(8)
                ->withQueryString();

            $summary = $this->buildSummary($hakAkses);
            $jadwalCards = $this->buildCards(collect($jadwalKegiatan->items()));
            $jadwalUtama = $this->getJadwalTerdekat($hakAkses);

            return view('user.jadwal.index', [
                'context' => $context,
                'hakAkses' => $hakAkses,

                'filters' => $filters,
                'filterTarget' => $filters['filter'],

                'summary' => $summary,
                'jadwalUtama' => $jadwalUtama,
                'jadwalKegiatan' => $jadwalKegiatan,
                'jadwalCards' => $jadwalCards,
            ]);
        } catch (\Throwable $e) {
            Log::error('User JadwalController@index error', [
                'message' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return view('user.jadwal.index', [
                'context' => $this->emptyUserContext(),
                'hakAkses' => ['semua'],
                'filters' => [
                    'search' => '',
                    'filter' => 'semua',
                    'periode' => 'semua',
                ],
                'filterTarget' => 'semua',
                'summary' => $this->emptySummary(),
                'jadwalUtama' => null,
                'jadwalKegiatan' => $this->emptyPaginator($request),
                'jadwalCards' => collect(),
                'loadError' => 'Jadwal Posyandu belum dapat dimuat.',
            ]);
        }
    }

    private function resolveHakAkses(array $context): array
    {
        $targets = $context['targets'] ?? ['semua'];

        if (! is_array($targets)) {
            $targets = ['semua'];
        }

        $targets[] = 'semua';

        return collect($targets)
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }

    private function normalizeFilter(?string $value): string
    {
        $value = $value ?: 'semua';

        return in_array($value, ['semua', 'balita', 'remaja', 'lansia'], true)
            ? $value
            : 'semua';
    }

    private function normalizePeriode(?string $value): string
    {
        $value = $value ?: 'semua';

        return in_array($value, ['semua', 'hari_ini', 'mendatang', 'bulan_ini'], true)
            ? $value
            : 'semua';
    }

    private function baseQuery(array $hakAkses): Builder
    {
        $table = $this->tableName();

        $query = JadwalPosyandu::query();

        if (Schema::hasColumn($table, 'status')) {
            $query->where('status', 'aktif');
        }

        if (Schema::hasColumn($table, 'target_peserta')) {
            $query->whereIn('target_peserta', $hakAkses);
        }

        return $query;
    }

    private function applyTargetFilter(Builder $query, array $hakAkses, string $filter): void
    {
        $table = $this->tableName();

        if ($filter === 'semua' || ! Schema::hasColumn($table, 'target_peserta')) {
            return;
        }

        if (! in_array($filter, $hakAkses, true)) {
            $query->whereRaw('1 = 0');
            return;
        }

        $query->where('target_peserta', $filter);
    }

    private function applyPeriodeFilter(Builder $query, string $periode): void
    {
        $table = $this->tableName();

        if (! Schema::hasColumn($table, 'tanggal')) {
            return;
        }

        $today = now('Asia/Jakarta')->toDateString();

        if ($periode === 'hari_ini') {
            $query->whereDate('tanggal', $today);
            return;
        }

        if ($periode === 'mendatang') {
            $query->whereDate('tanggal', '>=', $today);
            return;
        }

        if ($periode === 'bulan_ini') {
            $query->whereBetween('tanggal', [
                now('Asia/Jakarta')->startOfMonth()->toDateString(),
                now('Asia/Jakarta')->endOfMonth()->toDateString(),
            ]);
        }
    }

    private function applySearchFilter(Builder $query, string $search): void
    {
        $table = $this->tableName();

        if ($search === '') {
            return;
        }

        $columns = collect([
            'judul',
            'lokasi',
            'deskripsi',
            'keterangan',
            'kategori',
            'target_peserta',
        ])->filter(fn ($column) => Schema::hasColumn($table, $column));

        if ($columns->isEmpty()) {
            return;
        }

        $query->where(function (Builder $q) use ($columns, $search) {
            foreach ($columns as $column) {
                $q->orWhere($column, 'like', '%' . $search . '%');
            }
        });
    }

    private function applySmartOrdering(Builder $query): void
    {
        $table = $this->tableName();

        if (! Schema::hasColumn($table, 'tanggal')) {
            $query->latest();
            return;
        }

        $query->orderByRaw("
            CASE
                WHEN tanggal >= CURDATE() THEN 0
                ELSE 1
            END ASC
        ");

        $query->orderByRaw("
            CASE
                WHEN tanggal >= CURDATE() THEN tanggal
                ELSE NULL
            END ASC
        ");

        $query->orderByRaw("
            CASE
                WHEN tanggal < CURDATE() THEN tanggal
                ELSE NULL
            END DESC
        ");

        if (Schema::hasColumn($table, 'waktu_mulai')) {
            $query->orderBy('waktu_mulai');
        }
    }

    private function buildSummary(array $hakAkses): array
    {
        try {
            $base = $this->baseQuery($hakAkses);

            return [
                'semua' => (clone $base)->count(),
                'balita' => in_array('balita', $hakAkses, true)
                    ? (clone $base)->where('target_peserta', 'balita')->count()
                    : 0,
                'remaja' => in_array('remaja', $hakAkses, true)
                    ? (clone $base)->where('target_peserta', 'remaja')->count()
                    : 0,
                'lansia' => in_array('lansia', $hakAkses, true)
                    ? (clone $base)->where('target_peserta', 'lansia')->count()
                    : 0,
                'hari_ini' => (clone $base)->whereDate('tanggal', now('Asia/Jakarta')->toDateString())->count(),
                'mendatang' => (clone $base)->whereDate('tanggal', '>=', now('Asia/Jakarta')->toDateString())->count(),
            ];
        } catch (\Throwable $e) {
            Log::warning('User jadwal summary skipped', [
                'message' => $e->getMessage(),
            ]);

            return $this->emptySummary();
        }
    }

    private function emptySummary(): array
    {
        return [
            'semua' => 0,
            'balita' => 0,
            'remaja' => 0,
            'lansia' => 0,
            'hari_ini' => 0,
            'mendatang' => 0,
        ];
    }

    private function getJadwalTerdekat(array $hakAkses): ?JadwalPosyandu
    {
        try {
            $table = $this->tableName();

            $query = $this->baseQuery($hakAkses);

            if (Schema::hasColumn($table, 'tanggal')) {
                $query->whereDate('tanggal', '>=', now('Asia/Jakarta')->toDateString())
                    ->orderBy('tanggal');
            }

            if (Schema::hasColumn($table, 'waktu_mulai')) {
                $query->orderBy('waktu_mulai');
            }

            return $query->first();
        } catch (\Throwable $e) {
            Log::warning('User jadwal terdekat skipped', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function buildCards(Collection $items): Collection
    {
        return $items
            ->map(fn (JadwalPosyandu $jadwal) => $this->buildCard($jadwal))
            ->values();
    }

    private function buildCard(JadwalPosyandu $jadwal): array
    {
        $tanggal = filled($jadwal->tanggal ?? null)
            ? Carbon::parse($jadwal->tanggal)
            : null;

        $isToday = $tanggal?->isToday() ?? false;
        $isTomorrow = $tanggal?->isTomorrow() ?? false;
        $isPast = $tanggal ? $tanggal->lt(now('Asia/Jakarta')->startOfDay()) : false;

        $target = $jadwal->target_peserta ?? 'semua';

        return [
            'id' => $jadwal->id,
            'judul' => $jadwal->judul ?? 'Agenda Posyandu',
            'deskripsi' => $jadwal->deskripsi
                ?? $jadwal->keterangan
                ?? 'Tidak ada catatan tambahan.',
            'tanggal' => $tanggal ? $tanggal->translatedFormat('l, d F Y') : '-',
            'tanggal_short' => $tanggal ? $tanggal->translatedFormat('d M Y') : '-',
            'bulan' => $tanggal ? $tanggal->translatedFormat('M') : '-',
            'hari' => $tanggal ? $tanggal->format('d') : '-',
            'waktu' => $this->timeRange($jadwal),
            'lokasi' => $jadwal->lokasi ?? '-',
            'target' => $target,
            'target_label' => $this->targetLabel($target),
            'target_tone' => $this->targetTone($target),
            'status_label' => $this->statusLabel($isToday, $isTomorrow, $isPast),
            'status_tone' => $this->statusTone($isToday, $isTomorrow, $isPast),
            'kategori' => $this->kategoriLabel($jadwal->kategori ?? null),
            'is_today' => $isToday,
            'is_past' => $isPast,
        ];
    }

    private function timeRange(JadwalPosyandu $jadwal): string
    {
        $mulai = filled($jadwal->waktu_mulai ?? null)
            ? Carbon::parse($jadwal->waktu_mulai)->format('H:i')
            : '-';

        $selesai = filled($jadwal->waktu_selesai ?? null)
            ? Carbon::parse($jadwal->waktu_selesai)->format('H:i')
            : 'Selesai';

        return $mulai . ' sampai ' . $selesai . ' WIB';
    }

    private function targetLabel(?string $target): string
    {
        return match ($target) {
            'balita' => 'Posyandu Balita',
            'remaja' => 'Posyandu Remaja',
            'lansia' => 'Posyandu Lansia',
            'semua' => 'Semua Sasaran',
            default => 'Agenda Umum',
        };
    }

    private function targetTone(?string $target): string
    {
        return match ($target) {
            'balita' => 'rose',
            'remaja' => 'sky',
            'lansia' => 'amber',
            default => 'emerald',
        };
    }

    private function kategoriLabel(?string $kategori): string
    {
        return match ($kategori) {
            'posyandu' => 'Posyandu Rutin',
            'imunisasi' => 'Imunisasi',
            'pemeriksaan' => 'Pemeriksaan',
            'lainnya' => 'Kegiatan Lainnya',
            default => 'Agenda Posyandu',
        };
    }

    private function statusLabel(bool $isToday, bool $isTomorrow, bool $isPast): string
    {
        if ($isToday) {
            return 'Hari Ini';
        }

        if ($isTomorrow) {
            return 'Besok';
        }

        if ($isPast) {
            return 'Selesai';
        }

        return 'Mendatang';
    }

    private function statusTone(bool $isToday, bool $isTomorrow, bool $isPast): string
    {
        if ($isToday) {
            return 'emerald';
        }

        if ($isTomorrow) {
            return 'sky';
        }

        if ($isPast) {
            return 'slate';
        }

        return 'amber';
    }

    private function tableName(): string
    {
        return (new JadwalPosyandu())->getTable();
    }

    private function emptyPaginator(Request $request): LengthAwarePaginator
    {
        return new LengthAwarePaginator(
            [],
            0,
            8,
            LengthAwarePaginator::resolveCurrentPage(),
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );
    }
}