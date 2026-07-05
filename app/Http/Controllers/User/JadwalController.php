<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Controllers\User\Concerns\ResolvesUserHealthContext;
use App\Models\JadwalPosyandu;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
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

    // Menyimpan daftar kolom tabel agar tidak memanggil Schema berulang kali
    private array $tableColumns = [];

    public function index(Request $request): View
    {
        try {
            $user = auth()->user();

            // 1. Ambil Context User (Sasaran)
            $contextKey = 'user_dashboard_context_' . $user->id;
            $context = Cache::remember($contextKey, 300, function () use ($user) {
                return $this->getUserContext($user);
            });

            $hakAkses = $this->resolveHakAkses($context);

            // 2. Load struktur kolom database (Sangat Aman)
            $this->tableColumns = Schema::getColumnListing((new JadwalPosyandu())->getTable());

            $filters = [
                'search' => trim((string) $request->input('search', '')),
                'filter' => $this->normalizeFilter($request->input('filter', 'semua')),
                'periode' => $this->normalizePeriode($request->input('periode', 'semua')),
            ];

            // 3. Bangun Query Utama
            $query = JadwalPosyandu::query();
            $this->applyBaseFilters($query, $hakAkses);
            $this->applyTargetFilter($query, $filters['filter']);
            $this->applyPeriodeFilter($query, $filters['periode']);
            $this->applySearchFilter($query, $filters['search']);
            $this->applySmartOrdering($query);

            $jadwalKegiatan = $query->paginate(8)->withQueryString();

            // 4. Bangun UI Data
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
            Log::error('User JadwalController@index error: ' . $e->getMessage() . ' on line ' . $e->getLine());

            return view('user.jadwal.index', [
                'context' => $this->emptyUserContext(),
                'hakAkses' => ['semua'],
                'filters' => ['search' => '', 'filter' => 'semua', 'periode' => 'semua'],
                'filterTarget' => 'semua',
                'summary' => $this->emptySummary(),
                'jadwalUtama' => null,
                'jadwalKegiatan' => $this->emptyPaginator($request),
                'jadwalCards' => collect(),
                'loadError' => 'Terjadi kesalahan sistem saat memuat jadwal.',
            ]);
        }
    }

    public function show(Request $request, int $id): View|RedirectResponse
    {
        $user = auth()->user();

        $contextKey = 'user_dashboard_context_' . $user->id;
        $context = Cache::remember($contextKey, 300, function () use ($user) {
            return $this->getUserContext($user);
        });

        $hakAkses = $this->resolveHakAkses($context);
        $this->tableColumns = Schema::getColumnListing((new JadwalPosyandu())->getTable());

        $query = JadwalPosyandu::query();
        $this->applyBaseFilters($query, $hakAkses);
        $jadwal = $query->find($id);

        if (! $jadwal) {
            return redirect()
                ->route('user.jadwal.index')
                ->withErrors(['jadwal' => 'Jadwal tidak ditemukan atau bukan untuk kategori Anda.']);
        }

        return view('user.jadwal.show', [
            'context' => $context,
            'jadwal' => $jadwal,
            'card' => $this->buildCard($jadwal),
        ]);
    }

    private function resolveHakAkses(array $context): array
    {
        $targets = $context['targets'] ?? [];
        if (!is_array($targets)) {
            $targets = [];
        }
        
        // Memastikan jadwal "semua" dan "umum" selalu ikut terbaca
        $targets[] = 'semua';
        $targets[] = 'umum';
        
        return array_values(array_unique(array_filter($targets)));
    }

    private function getTargetColumnName(): ?string
    {
        $possibleCols = ['sasaran', 'target_peserta', 'target', 'kategori_sasaran'];
        foreach ($possibleCols as $col) {
            if (in_array($col, $this->tableColumns)) return $col;
        }
        return null;
    }

    private function applyBaseFilters(Builder $query, array $hakAkses): void
    {
        // 1. Filter Status (Aman untuk nilai NULL)
        if (in_array('status', $this->tableColumns)) {
            $query->where(function (Builder $q) {
                $q->whereNull('status')
                  ->orWhereNotIn('status', ['draft', 'batal', 'nonaktif', '0']);
            });
        }

        // 2. Filter Hak Akses Sasaran (Aman & Fleksibel)
        $targetCol = $this->getTargetColumnName();
        if ($targetCol) {
            $query->where(function (Builder $q) use ($targetCol, $hakAkses) {
                // Looping semua hak akses user (termasuk 'semua' dan 'umum')
                foreach ($hakAkses as $akses) {
                    $q->orWhere($targetCol, 'LIKE', '%' . $akses . '%');
                }
                // Jika admin lupa mengisi sasaran (NULL), asumsikan itu untuk semua orang
                $q->orWhereNull($targetCol);
                $q->orWhere($targetCol, '=', ''); 
            });
        }
    }

    private function applyTargetFilter(Builder $query, string $filter): void
    {
        if ($filter === 'semua') return;

        $targetCol = $this->getTargetColumnName();
        if (!$targetCol) return;

        $query->where(function (Builder $q) use ($targetCol, $filter) {
            $q->where($targetCol, 'LIKE', '%' . $filter . '%')
              ->orWhere($targetCol, 'LIKE', '%semua%')
              ->orWhere($targetCol, 'LIKE', '%umum%');
        });
    }

    private function applyPeriodeFilter(Builder $query, string $periode): void
    {
        if (!in_array('tanggal', $this->tableColumns)) return;

        $today = now('Asia/Jakarta')->toDateString();

        if ($periode === 'hari_ini') {
            $query->whereDate('tanggal', $today);
        } elseif ($periode === 'mendatang') {
            $query->whereDate('tanggal', '>=', $today);
        } elseif ($periode === 'bulan_ini') {
            $query->whereBetween('tanggal', [
                now('Asia/Jakarta')->startOfMonth()->toDateString(),
                now('Asia/Jakarta')->endOfMonth()->toDateString(),
            ]);
        }
    }

    private function applySearchFilter(Builder $query, string $search): void
    {
        if ($search === '') return;

        $possibleCols = collect(['judul', 'lokasi', 'deskripsi', 'keterangan', 'kategori', 'sasaran', 'target_peserta', 'target'])
            ->filter(fn ($col) => in_array($col, $this->tableColumns));

        if ($possibleCols->isEmpty()) return;

        $query->where(function (Builder $q) use ($possibleCols, $search) {
            foreach ($possibleCols as $column) {
                $q->orWhere($column, 'LIKE', '%' . $search . '%');
            }
        });
    }

    private function applySmartOrdering(Builder $query): void
    {
        if (!in_array('tanggal', $this->tableColumns)) {
            $query->latest();
            return;
        }

        $today = now('Asia/Jakarta')->toDateString();

        $query->orderByRaw("CASE WHEN tanggal >= '{$today}' THEN 0 ELSE 1 END ASC");
        $query->orderByRaw("CASE WHEN tanggal >= '{$today}' THEN tanggal ELSE NULL END ASC");
        $query->orderByRaw("CASE WHEN tanggal < '{$today}' THEN tanggal ELSE NULL END DESC");

        if (in_array('waktu_mulai', $this->tableColumns)) {
            $query->orderBy('waktu_mulai');
        }
    }

    private function buildSummary(array $hakAkses): array
    {
        try {
            $targetCol = $this->getTargetColumnName();
            $today = now('Asia/Jakarta')->toDateString();

            // Bangun Query Dasar Baru Untuk Summary
            $baseQuery = JadwalPosyandu::query();
            $this->applyBaseFilters($baseQuery, $hakAkses);

            return [
                'semua' => (clone $baseQuery)->count(),
                'balita' => $targetCol && in_array('balita', $hakAkses) 
                    ? (clone $baseQuery)->where(function($q) use ($targetCol) {
                        $q->where($targetCol, 'LIKE', '%balita%')->orWhere($targetCol, 'LIKE', '%semua%')->orWhere($targetCol, 'LIKE', '%umum%');
                      })->count() : 0,
                'remaja' => $targetCol && in_array('remaja', $hakAkses) 
                    ? (clone $baseQuery)->where(function($q) use ($targetCol) {
                        $q->where($targetCol, 'LIKE', '%remaja%')->orWhere($targetCol, 'LIKE', '%semua%')->orWhere($targetCol, 'LIKE', '%umum%');
                      })->count() : 0,
                'lansia' => $targetCol && in_array('lansia', $hakAkses) 
                    ? (clone $baseQuery)->where(function($q) use ($targetCol) {
                        $q->where($targetCol, 'LIKE', '%lansia%')->orWhere($targetCol, 'LIKE', '%semua%')->orWhere($targetCol, 'LIKE', '%umum%');
                      })->count() : 0,
                'hari_ini' => (clone $baseQuery)->whereDate('tanggal', $today)->count(),
                'mendatang' => (clone $baseQuery)->whereDate('tanggal', '>=', $today)->count(),
            ];
        } catch (\Throwable $e) {
            Log::warning('User jadwal summary error: ' . $e->getMessage());
            return $this->emptySummary();
        }
    }

    private function emptySummary(): array
    {
        return ['semua' => 0, 'balita' => 0, 'remaja' => 0, 'lansia' => 0, 'hari_ini' => 0, 'mendatang' => 0];
    }

    private function getJadwalTerdekat(array $hakAkses): ?JadwalPosyandu
    {
        try {
            $query = JadwalPosyandu::query();
            $this->applyBaseFilters($query, $hakAkses);
            
            $today = now('Asia/Jakarta')->toDateString();
            
            if (in_array('tanggal', $this->tableColumns)) {
                $query->whereDate('tanggal', '>=', $today)->orderBy('tanggal');
            }
            if (in_array('waktu_mulai', $this->tableColumns)) {
                $query->orderBy('waktu_mulai');
            }
            
            return $query->first();
        } catch (\Throwable $e) {
            Log::warning('User jadwal terdekat error: ' . $e->getMessage());
            return null;
        }
    }

    private function normalizeFilter(?string $value): string
    {
        $value = $value ?: 'semua';
        return in_array($value, ['semua', 'balita', 'remaja', 'lansia'], true) ? $value : 'semua';
    }

    private function normalizePeriode(?string $value): string
    {
        $value = $value ?: 'semua';
        return in_array($value, ['semua', 'hari_ini', 'mendatang', 'bulan_ini'], true) ? $value : 'semua';
    }

    private function buildCards(Collection $items): Collection
    {
        return $items->map(fn (JadwalPosyandu $jadwal) => $this->buildCard($jadwal))->values();
    }

    private function buildCard(JadwalPosyandu $jadwal): array
    {
        $tanggal = filled($jadwal->tanggal ?? null) ? Carbon::parse($jadwal->tanggal) : null;
        $isToday = $tanggal ? $tanggal->isSameDay(now('Asia/Jakarta')) : false;
        $isTomorrow = $tanggal ? $tanggal->isSameDay(now('Asia/Jakarta')->addDay()) : false;
        $isPast = $tanggal ? $tanggal->lt(now('Asia/Jakarta')->startOfDay()) : false;

        $target = $jadwal->sasaran ?? $jadwal->target_peserta ?? $jadwal->target ?? 'semua';

        return [
            'id' => $jadwal->id,
            'judul' => $jadwal->judul ?? 'Agenda Posyandu',
            'deskripsi' => $jadwal->deskripsi ?? $jadwal->keterangan ?? 'Tidak ada catatan tambahan.',
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
        $mulai = filled($jadwal->waktu_mulai ?? null) ? Carbon::parse($jadwal->waktu_mulai)->format('H:i') : '-';
        $selesai = filled($jadwal->waktu_selesai ?? null) ? Carbon::parse($jadwal->waktu_selesai)->format('H:i') : 'Selesai';
        return $mulai . ' sampai ' . $selesai . ' WIB';
    }

    private function targetLabel(?string $target): string
    {
        $targetLower = strtolower((string) $target);
        if (str_contains($targetLower, 'balita')) return 'Posyandu Balita';
        if (str_contains($targetLower, 'remaja')) return 'Posyandu Remaja';
        if (str_contains($targetLower, 'lansia')) return 'Posyandu Lansia';
        return 'Semua Sasaran';
    }

    private function targetTone(?string $target): string
    {
        $targetLower = strtolower((string) $target);
        if (str_contains($targetLower, 'balita')) return 'rose';
        if (str_contains($targetLower, 'remaja')) return 'sky';
        if (str_contains($targetLower, 'lansia')) return 'amber';
        return 'emerald';
    }

    private function kategoriLabel(?string $kategori): string
    {
        return match (strtolower((string) $kategori)) {
            'posyandu' => 'Posyandu Rutin',
            'imunisasi' => 'Imunisasi',
            'pemeriksaan' => 'Pemeriksaan',
            'lainnya' => 'Kegiatan Lainnya',
            default => 'Agenda Posyandu',
        };
    }

    private function statusLabel(bool $isToday, bool $isTomorrow, bool $isPast): string
    {
        if ($isToday) return 'Hari Ini';
        if ($isTomorrow) return 'Besok';
        if ($isPast) return 'Selesai';
        return 'Mendatang';
    }

    private function statusTone(bool $isToday, bool $isTomorrow, bool $isPast): string
    {
        if ($isToday) return 'emerald';
        if ($isTomorrow) return 'sky';
        if ($isPast) return 'slate';
        return 'amber';
    }

    private function emptyPaginator(Request $request): LengthAwarePaginator
    {
        return new LengthAwarePaginator([], 0, 8, LengthAwarePaginator::resolveCurrentPage(), [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);
    }
}