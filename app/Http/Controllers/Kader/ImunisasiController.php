<?php

namespace App\Http\Controllers\Kader;

use App\Http\Controllers\Controller;
use App\Models\Balita;
use App\Models\Imunisasi;
use App\Models\Lansia;
use App\Models\Remaja;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ImunisasiController extends Controller
{
    public function index(Request $request)
    {
        try {
            $kategori = strtolower((string) $request->get('kategori', 'semua'));
            $search = trim((string) $request->get('search', ''));
            $bulan = (int) $request->get('bulan', now()->month);
            $tahun = (int) $request->get('tahun', now()->year);

            if (! in_array($kategori, ['semua', 'balita', 'remaja', 'lansia'], true)) {
                $kategori = 'semua';
            }

            if ($bulan < 1 || $bulan > 12) {
                $bulan = now()->month;
            }

            if ($tahun < 2020 || $tahun > ((int) now()->year + 1)) {
                $tahun = now()->year;
            }

            $query = Imunisasi::query()
                ->with(['kunjungan.petugas', 'kunjungan.pasien'])
                ->whereMonth('tanggal_imunisasi', $bulan)
                ->whereYear('tanggal_imunisasi', $tahun)
                ->latest('tanggal_imunisasi')
                ->latest('id');

            $this->applyCategoryFilter($query, $kategori);
            $this->applySmartSearch($query, $search);

            $imunisasis = $query
                ->paginate(10)
                ->withQueryString();

            $statistics = $this->generateAnalytics($kategori, $bulan, $tahun);

            return view('kader.imunisasi.index', array_merge(
                compact('imunisasis', 'kategori', 'search', 'bulan', 'tahun'),
                $statistics
            ));
        } catch (\Throwable $e) {
            Log::error('KADER_IMUNISASI_INDEX_ERROR', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return redirect()
                ->route('kader.dashboard')
                ->with('error', 'Log imunisasi gagal dimuat. Sistemnya ngambek, tapi sudah dicatat untuk dicek.');
        }
    }

    public function show($id)
    {
        try {
            $imunisasi = Imunisasi::query()
                ->with(['kunjungan.petugas', 'kunjungan.pasien'])
                ->findOrFail($id);

            return view('kader.imunisasi.show', compact('imunisasi'));
        } catch (\Throwable $e) {
            Log::error('KADER_IMUNISASI_SHOW_ERROR', [
                'id' => $id,
                'message' => $e->getMessage(),
            ]);

            return redirect()
                ->route('kader.imunisasi.index')
                ->with('error', 'Detail imunisasi tidak ditemukan atau sudah tidak tersedia.');
        }
    }

    private function generateAnalytics(string $kategori, int $bulan, int $tahun): array
    {
        $baseQuery = Imunisasi::query()
            ->with(['kunjungan.pasien'])
            ->whereMonth('tanggal_imunisasi', $bulan)
            ->whereYear('tanggal_imunisasi', $tahun);

        $this->applyCategoryFilter($baseQuery, $kategori);

        $rows = (clone $baseQuery)->get();

        $statTotal = $rows->count();

        $statBalita = $rows->filter(function ($item) {
            return $item->kategori_key === 'balita';
        })->count();

        $statRemaja = $rows->filter(function ($item) {
            return $item->kategori_key === 'remaja';
        })->count();

        $statLansia = $rows->filter(function ($item) {
            return $item->kategori_key === 'lansia';
        })->count();

        $statVaksinUnik = $rows
            ->map(fn ($item) => strtolower(trim((string) ($item->vaksin ?? $item->jenis_imunisasi))))
            ->filter()
            ->unique()
            ->count();

        $statBulanLabel = Carbon::create($tahun, $bulan, 1)
            ->locale('id')
            ->translatedFormat('F Y');

        $lastUpdate = $rows
            ->sortByDesc('created_at')
            ->first();

        return [
            'statTotal' => $statTotal,
            'statBalita' => $statBalita,
            'statRemaja' => $statRemaja,
            'statLansia' => $statLansia,
            'statVaksinUnik' => $statVaksinUnik,
            'statBulanLabel' => $statBulanLabel,
            'statLastUpdate' => $lastUpdate?->created_at,
        ];
    }

    private function applyCategoryFilter(Builder $query, string $kategori): void
    {
        if ($kategori === 'semua') {
            return;
        }

        $modelClass = match ($kategori) {
            'balita' => Balita::class,
            'remaja' => Remaja::class,
            'lansia' => Lansia::class,
            default => null,
        };

        if (! $modelClass) {
            return;
        }

        $query->whereHas('kunjungan', function (Builder $q) use ($modelClass, $kategori) {
            $q->where('pasien_type', $modelClass)
                ->orWhere('pasien_type', 'like', '%' . class_basename($modelClass) . '%')
                ->orWhere('pasien_type', $kategori);
        });
    }

    private function applySmartSearch(Builder $query, string $search): void
    {
        if ($search === '') {
            return;
        }

        $query->where(function (Builder $q) use ($search) {
            if (is_numeric($search)) {
                $q->where('batch_number', 'like', "%{$search}%")
                    ->orWhereHas('kunjungan', function (Builder $kunjunganQuery) use ($search) {
                        $kunjunganQuery->whereHasMorph(
                            'pasien',
                            [Balita::class, Remaja::class, Lansia::class],
                            function (Builder $pasienQuery) use ($search) {
                                $pasienQuery->where('nik', 'like', "%{$search}%");
                            }
                        );
                    });

                return;
            }

            $q->where('vaksin', 'like', "%{$search}%")
                ->orWhere('jenis_imunisasi', 'like', "%{$search}%")
                ->orWhere('penyelenggara', 'like', "%{$search}%")
                ->orWhere('batch_number', 'like', "%{$search}%")
                ->orWhereHas('kunjungan', function (Builder $kunjunganQuery) use ($search) {
                    $kunjunganQuery->whereHasMorph(
                        'pasien',
                        [Balita::class, Remaja::class, Lansia::class],
                        function (Builder $pasienQuery) use ($search) {
                            $pasienQuery
                                ->where('nama_lengkap', 'like', "%{$search}%")
                                ->orWhere('nama', 'like', "%{$search}%");
                        }
                    );
                });
        });
    }

    public function create()
    {
        return redirect()
            ->route('kader.imunisasi.index')
            ->with('error', 'Log imunisasi bersifat baca saja. Input dan perubahan data dilakukan oleh Bidan.');
    }

    public function store()
    {
        abort(403, 'Akses ditolak. Imunisasi dikelola oleh Bidan.');
    }

    public function edit($id)
    {
        return redirect()
            ->route('kader.imunisasi.show', $id)
            ->with('error', 'Kader hanya dapat melihat log imunisasi. Edit data dilakukan oleh Bidan.');
    }

    public function update()
    {
        abort(403, 'Akses ditolak. Imunisasi dikelola oleh Bidan.');
    }

    public function destroy()
    {
        abort(403, 'Akses ditolak. Imunisasi dikelola oleh Bidan.');
    }
}