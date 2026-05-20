<?php

namespace App\Http\Controllers\Kader;

use App\Http\Controllers\Controller;
use App\Models\Balita;
use App\Models\Imunisasi;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class ImunisasiController extends Controller
{
    public function index(Request $request)
    {
        try {
            $search = trim((string) $request->get('search', ''));
            $bulan = $request->get('bulan', now()->format('Y-m'));

            $baseQuery = Imunisasi::query()
                ->targetBalita()
                ->with(['kunjungan.pasien', 'kunjungan.petugas']);

            $statTotal = (clone $baseQuery)->count();
            $statBulanIni = (clone $baseQuery)->bulanIni()->count();
            $statBalita = Balita::count();

            $query = (clone $baseQuery)->latest('tanggal_imunisasi');

            if ($this->isValidMonth($bulan)) {
                $date = Carbon::createFromFormat('Y-m', $bulan);
                $query->whereMonth('tanggal_imunisasi', $date->month)
                    ->whereYear('tanggal_imunisasi', $date->year);
            }

            $this->applySearch($query, $search);

            $imunisasis = $query->paginate(10)->withQueryString();

            return view('kader.imunisasi.index', compact(
                'imunisasis',
                'search',
                'bulan',
                'statTotal',
                'statBulanIni',
                'statBalita'
            ));
        } catch (\Throwable $e) {
            Log::error('KADER_IMUNISASI_INDEX_ERROR: ' . $e->getMessage());

            return back()->with('error', 'Data imunisasi gagal dimuat.');
        }
    }

    public function show($id)
    {
        try {
            $imunisasi = Imunisasi::query()
                ->targetBalita()
                ->with(['kunjungan.pasien', 'kunjungan.petugas'])
                ->findOrFail($id);

            return view('kader.imunisasi.show', compact('imunisasi'));
        } catch (\Throwable $e) {
            Log::error('KADER_IMUNISASI_SHOW_ERROR: ' . $e->getMessage());

            return redirect()
                ->route('kader.imunisasi.index')
                ->with('error', 'Detail imunisasi tidak ditemukan.');
        }
    }

    private function applySearch(Builder $query, string $search): void
    {
        if ($search === '') {
            return;
        }

        $query->where(function ($q) use ($search) {
            $q->where('jenis_imunisasi', 'like', "%{$search}%")
                ->orWhere('vaksin', 'like', "%{$search}%")
                ->orWhere('batch_number', 'like', "%{$search}%")
                ->orWhereHas('kunjungan', function ($kunjunganQuery) use ($search) {
                    $kunjunganQuery->whereHasMorph('pasien', [Balita::class], function ($pasienQuery) use ($search) {
                        $pasienQuery->where('nama_lengkap', 'like', "%{$search}%")
                            ->orWhere('nik', 'like', "%{$search}%");
                    });
                });
        });
    }

    private function isValidMonth(?string $bulan): bool
    {
        if (!$bulan) {
            return false;
        }

        return preg_match('/^\d{4}-\d{2}$/', $bulan) === 1;
    }
}