<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Controllers\User\Concerns\ResolvesUserHealthContext;
use App\Models\Pemeriksaan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class RiwayatController extends Controller
{
    use ResolvesUserHealthContext;

    public function index(Request $request): View
    {
        try {
            $user = auth()->user();

            // SINKRONISASI CACHE DENGAN DASHBOARD
            $contextKey = 'user_dashboard_context_' . $user->id;
            $context = Cache::remember($contextKey, 300, function () use ($user) {
                return $this->getUserContext($user);
            });

            $filters = [
                'search' => trim((string) $request->input('search', '')),
                'kategori' => $request->input('kategori', 'semua'),
                'periode' => $request->input('periode', 'semua'),
            ];

            $targets = $this->resolveTargets($context);

            if ($targets->isEmpty() || ! Schema::hasTable('pemeriksaans')) {
                return view('user.riwayat.index', [
                    'context' => $context,
                    'targets' => $targets,
                    'filters' => $filters,
                    'counts' => $this->emptyCounts(),
                    'riwayat' => $this->emptyPaginator($request),
                    'riwayatCards' => collect(),
                ]);
            }

            $filteredTargets = $this->filterTargetsByKategori($targets, $filters['kategori']);
            $query = $this->buildRiwayatQuery($filteredTargets);

            $this->applyVerifiedFilter($query);
            $this->applyPeriodeFilter($query, $filters['periode']);
            $this->applySearchFilter($query, $targets, $filters['search']);

            $dateColumn = $this->dateColumn();

            $riwayat = $query
                ->orderByDesc($dateColumn)
                ->orderByDesc('created_at')
                ->paginate(5)
                ->withQueryString();

            $riwayatCards = collect($riwayat->items())
                ->map(fn (Pemeriksaan $item) => $this->buildCard($item, $targets))
                ->filter()
                ->values();

            return view('user.riwayat.index', [
                'context' => $context,
                'targets' => $targets,
                'filters' => $filters,
                'counts' => $this->buildCounts($targets, $riwayat),
                'riwayat' => $riwayat,
                'riwayatCards' => $riwayatCards,
            ]);
        } catch (\Throwable $e) {
            Log::error('User RiwayatController@index error', [
                'message' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return view('user.riwayat.index', [
                'context' => $this->emptyUserContext(),
                'targets' => collect(),
                'filters' => [
                    'search' => '',
                    'kategori' => 'semua',
                    'periode' => 'semua',
                ],
                'counts' => $this->emptyCounts(),
                'riwayat' => $this->emptyPaginator($request),
                'riwayatCards' => collect(),
                'loadError' => 'Riwayat rekam medis belum dapat dimuat.',
            ]);
        }
    }

    private function resolveTargets(array $context): Collection
    {
        $targets = collect();

        foreach (($context['balitas'] ?? collect()) as $balita) {
            $targets->push([
                'key' => 'balita:' . $balita->id,
                'id' => (int) $balita->id,
                'kategori' => 'balita',
                'label' => 'Balita',
                'nama' => $balita->nama_lengkap ?? '-',
                'nik' => $balita->nik ?? null,
                'tone' => 'rose',
                'icon' => 'fa-child',
                'route' => route('user.balita.show', $balita->id),
            ]);
        }

        foreach (($context['remajas'] ?? collect()) as $remaja) {
            $targets->push([
                'key' => 'remaja:' . $remaja->id,
                'id' => (int) $remaja->id,
                'kategori' => 'remaja',
                'label' => 'Remaja',
                'nama' => $remaja->nama_lengkap ?? '-',
                'nik' => $remaja->nik ?? null,
                'tone' => 'sky',
                'icon' => 'fa-user-graduate',
                'route' => route('user.remaja.show', $remaja->id),
            ]);
        }

        foreach (($context['lansias'] ?? collect()) as $lansia) {
            $targets->push([
                'key' => 'lansia:' . $lansia->id,
                'id' => (int) $lansia->id,
                'kategori' => 'lansia',
                'label' => 'Lansia',
                'nama' => $lansia->nama_lengkap ?? '-',
                'nik' => $lansia->nik ?? null,
                'tone' => 'amber',
                'icon' => 'fa-heart-pulse',
                'route' => route('user.lansia.show', $lansia->id),
            ]);
        }

        return $targets->values();
    }

    private function filterTargetsByKategori(Collection $targets, string $kategori): Collection
    {
        if ($kategori === 'semua') {
            return $targets;
        }

        return $targets
            ->where('kategori', $kategori)
            ->values();
    }

    private function buildRiwayatQuery(Collection $targets): Builder
    {
        $query = Pemeriksaan::query();

        if ($targets->isEmpty()) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function (Builder $outer) use ($targets) {
            foreach ($targets as $target) {
                $this->addTargetCondition($outer, $target);
            }
        });
    }

    private function addTargetCondition(Builder $query, array $target): void
    {
        $query->orWhere(function (Builder $q) use ($target) {
            $hasCondition = false;

            if (
                Schema::hasColumn('pemeriksaans', 'kategori_pasien')
                && Schema::hasColumn('pemeriksaans', 'pasien_id')
            ) {
                $q->where(function (Builder $inner) use ($target) {
                    $inner->where('kategori_pasien', $target['kategori'])
                        ->where('pasien_id', $target['id']);
                });

                $hasCondition = true;
            }

            $foreignKey = $target['kategori'] . '_id';

            if (Schema::hasColumn('pemeriksaans', $foreignKey)) {
                $hasCondition
                    ? $q->orWhere($foreignKey, $target['id'])
                    : $q->where($foreignKey, $target['id']);

                $hasCondition = true;
            }

            if (! $hasCondition) {
                $q->whereRaw('1 = 0');
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
            'valid',
            'selesai',
        ]);
    }

    private function applyPeriodeFilter(Builder $query, string $periode): void
    {
        if ($periode === 'semua') {
            return;
        }

        $dateColumn = $this->dateColumn();
        $now = now('Asia/Jakarta');

        $startDate = match ($periode) {
            'bulan_ini' => $now->copy()->startOfMonth(),
            '3_bulan' => $now->copy()->subMonths(3)->startOfDay(),
            '6_bulan' => $now->copy()->subMonths(6)->startOfDay(),
            'tahun_ini' => $now->copy()->startOfYear(),
            default => null,
        };

        if ($startDate) {
            $query->whereDate($dateColumn, '>=', $startDate->toDateString());
        }
    }

    private function applySearchFilter(Builder $query, Collection $targets, string $search): void
    {
        if ($search === '') {
            return;
        }

        $keyword = mb_strtolower($search);

        $matchedTargets = $targets->filter(function ($target) use ($keyword) {
            return str_contains(mb_strtolower($target['nama'] ?? ''), $keyword)
                || str_contains(mb_strtolower($target['nik'] ?? ''), $keyword)
                || str_contains(mb_strtolower($target['kategori'] ?? ''), $keyword)
                || str_contains(mb_strtolower($target['label'] ?? ''), $keyword);
        });

        $query->where(function (Builder $outer) use ($matchedTargets, $search) {
            $hasCondition = false;

            foreach ($matchedTargets as $target) {
                $this->addTargetCondition($outer, $target);
                $hasCondition = true;
            }

            foreach (['keluhan', 'keterangan', 'edukasi', 'status_gizi', 'status_bbu', 'status_tbu', 'status_bbtb'] as $column) {
                if (Schema::hasColumn('pemeriksaans', $column)) {
                    $outer->orWhere($column, 'like', '%' . $search . '%');
                    $hasCondition = true;
                }
            }

            if (! $hasCondition) {
                $outer->whereRaw('1 = 0');
            }
        });
    }

    private function buildCard(Pemeriksaan $item, Collection $targets): ?array
    {
        $target = $this->resolveTargetForPemeriksaan($item, $targets);

        if (! $target) {
            return null;
        }

        $date = $item->tanggal_periksa ?? $item->created_at;
        $kategori = $target['kategori'];

        return [
            'id' => $item->id,
            'tanggal' => $date ? Carbon::parse($date)->translatedFormat('d F Y') : '-',
            'tanggal_short' => $date ? Carbon::parse($date)->translatedFormat('d M Y') : '-',
            'nama' => $target['nama'],
            'nik' => $target['nik'],
            'kategori' => $kategori,
            'kategori_label' => $target['label'],
            'tone' => $target['tone'],
            'icon' => $target['icon'],
            'route' => $target['route'],
            'status' => $item->status_verifikasi_text
                ?? $this->statusLabel($item->status_verifikasi ?? null),
            'catatan' => $item->keterangan
                ?? $item->keluhan
                ?? $item->edukasi
                ?? 'Tidak ada catatan tambahan.',
            'metrics' => $this->buildMetricsForCard($item, $kategori),
        ];
    }

    private function resolveTargetForPemeriksaan(Pemeriksaan $item, Collection $targets): ?array
    {
        $kategori = $item->kategori_pasien ?? null;
        $pasienId = $item->pasien_id ?? null;

        if ($kategori && $pasienId) {
            $target = $targets->first(fn ($target) => $target['kategori'] === $kategori && (int) $target['id'] === (int) $pasienId);

            if ($target) {
                return $target;
            }
        }

        foreach (['balita', 'remaja', 'lansia'] as $type) {
            $foreignKey = $type . '_id';

            if (filled($item->{$foreignKey} ?? null)) {
                $target = $targets->first(fn ($target) => $target['kategori'] === $type && (int) $target['id'] === (int) $item->{$foreignKey});

                if ($target) {
                    return $target;
                }
            }
        }

        return null;
    }

    private function buildMetricsForCard(Pemeriksaan $item, string $kategori): array
    {
        if ($kategori === 'balita') {
            return [
                ['label' => 'BB', 'value' => $this->numberValue($item->berat_badan ?? null, 'kg')],
                ['label' => 'TB', 'value' => $this->heightValue($item->tinggi_badan ?? null, 'balita')],
                ['label' => 'LK', 'value' => $this->numberValue($item->lingkar_kepala ?? null, 'cm')],
                ['label' => 'Status', 'value' => $item->status_gizi ?? $item->status_bbtb ?? 'Belum Dinilai'],
            ];
        }

        if ($kategori === 'remaja') {
            return [
                ['label' => 'BB', 'value' => $this->numberValue($item->berat_badan ?? null, 'kg')],
                ['label' => 'TB', 'value' => $this->heightValue($item->tinggi_badan ?? null, 'remaja')],
                ['label' => 'IMT', 'value' => $this->imtValue($item)],
                ['label' => 'HB', 'value' => $this->numberValue($item->hemoglobin ?? $item->hb ?? null, 'g/dL')],
            ];
        }

        return [
            ['label' => 'Tensi', 'value' => $this->bloodPressureValue($item->tekanan_darah ?? null)],
            ['label' => 'Gula', 'value' => $this->numberValue($item->gula_darah ?? null, 'mg/dL')],
            ['label' => 'Kolesterol', 'value' => $this->numberValue($item->kolesterol ?? null, 'mg/dL')],
            ['label' => 'Asam Urat', 'value' => $this->numberValue($item->asam_urat ?? null, 'mg/dL')],
        ];
    }

    private function buildCounts(Collection $targets, LengthAwarePaginator $riwayat): array
    {
        return [
            'target' => $targets->count(),
            'total' => $riwayat->total(),
            'balita' => $targets->where('kategori', 'balita')->count(),
            'remaja' => $targets->where('kategori', 'remaja')->count(),
            'lansia' => $targets->where('kategori', 'lansia')->count(),
        ];
    }

    private function emptyCounts(): array
    {
        return [
            'target' => 0,
            'total' => 0,
            'balita' => 0,
            'remaja' => 0,
            'lansia' => 0,
        ];
    }

    private function emptyPaginator(Request $request): LengthAwarePaginator
    {
        return new LengthAwarePaginator(
            [],
            0,
            10,
            LengthAwarePaginator::resolveCurrentPage(),
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );
    }

    private function dateColumn(): string
    {
        return Schema::hasColumn('pemeriksaans', 'tanggal_periksa')
            ? 'tanggal_periksa'
            : 'created_at';
    }

    private function statusLabel(?string $status): string
    {
        return match ($status) {
            'tervalidasi', 'verified', 'approved', 'valid', 'selesai' => 'Tervalidasi',
            'pending' => 'Menunggu',
            'revisi' => 'Perlu Revisi',
            default => 'Tervalidasi',
        };
    }

    private function imtValue(Pemeriksaan $item): string
    {
        if (filled($item->imt ?? null)) {
            return number_format((float) $item->imt, 1, ',', '.');
        }

        if (blank($item->berat_badan ?? null) || blank($item->tinggi_badan ?? null)) {
            return '-';
        }

        $height = $this->normalizeHeightCm($item->tinggi_badan, 'remaja');

        if (! $height) {
            return '-';
        }

        $meter = $height / 100;
        $imt = ((float) $item->berat_badan) / ($meter * $meter);

        return number_format($imt, 1, ',', '.');
    }

    private function normalizeHeightCm($value, string $kategori = 'umum'): ?float
    {
        if (blank($value)) {
            return null;
        }

        // PENINGKATAN: Gunakan Regex agar kebal terhadap teks seperti "160 cm" atau spasi
        $height = (float) preg_replace('/[^0-9.]/', '', (string) $value);

        if ($height >= 1 && $height <= 2.5) {
            $height *= 100;
        }

        if ($height >= 10 && $height < 50) {
            $height *= 10;
        }

        $max = $kategori === 'balita' ? 140 : 250;

        if ($height < 35 || $height > $max) {
            return null;
        }

        return round($height, 1);
    }

    private function heightValue($value, string $kategori = 'umum'): string
    {
        $height = $this->normalizeHeightCm($value, $kategori);

        if (! $height) {
            return '-';
        }

        return $this->numberValue($height, 'cm');
    }

    private function bloodPressureValue($value): string
    {
        if (blank($value)) {
            return '-';
        }

        preg_match_all('/\d+/', (string) $value, $matches);

        $numbers = collect($matches[0] ?? [])
            ->map(fn ($item) => (int) $item)
            ->filter(fn ($item) => $item > 0)
            ->values();

        if (! $numbers->get(0)) {
            return '-';
        }

        if (! $numbers->get(1)) {
            return (string) $numbers->get(0);
        }

        return $numbers->get(0) . '/' . $numbers->get(1) . ' mmHg';
    }

    private function numberValue($value, string $unit = ''): string
    {
        if (blank($value)) {
            return '-';
        }

        $number = (float) $value;
        $formatted = rtrim(rtrim(number_format($number, 1, '.', ''), '0'), '.');

        return trim($formatted . ' ' . $unit);
    }
}