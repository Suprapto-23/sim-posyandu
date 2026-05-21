<?php

namespace App\Http\Controllers\Kader;

use App\Http\Controllers\Controller;
use App\Models\JadwalPosyandu;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class JadwalController extends Controller
{
    public function index(Request $request)
    {
        try {
            $this->autoUpdateStatus();

            $search = trim((string) $request->get('search', ''));
            $status = $request->get('status', 'semua');
            $target = $request->get('target', 'semua');
            $bulan  = $request->get('bulan', now('Asia/Jakarta')->format('Y-m'));

            $baseQuery = JadwalPosyandu::query();

            $statTotal = (clone $baseQuery)->count();
            $statAktif = (clone $baseQuery)->where('status', 'aktif')->count();
            $statSelesai = (clone $baseQuery)->where('status', 'selesai')->count();

            $statBulanIni = (clone $baseQuery)
                ->whereMonth('tanggal', now('Asia/Jakarta')->month)
                ->whereYear('tanggal', now('Asia/Jakarta')->year)
                ->count();

            $query = JadwalPosyandu::query()
                ->orderByRaw("CASE WHEN status = 'aktif' THEN 0 ELSE 1 END")
                ->orderByRaw("CASE WHEN tanggal >= CURDATE() THEN 0 ELSE 1 END")
                ->orderBy('tanggal', 'asc')
                ->orderBy('waktu_mulai', 'asc');

            if ($status !== 'semua') {
                $query->where('status', $status);
            }

            if ($target !== 'semua') {
                $query->where('target_peserta', $target);
            }

            if ($this->isValidMonth($bulan)) {
                $date = Carbon::createFromFormat('Y-m', $bulan);

                $query->whereMonth('tanggal', $date->month)
                    ->whereYear('tanggal', $date->year);
            }

            $this->applySearch($query, $search);

            $jadwals = $query->paginate(10)->withQueryString();

            return view('kader.jadwal.index', compact(
                'jadwals',
                'search',
                'status',
                'target',
                'bulan',
                'statTotal',
                'statAktif',
                'statSelesai',
                'statBulanIni'
            ));
        } catch (\Throwable $e) {
            Log::error('KADER_JADWAL_INDEX_ERROR: ' . $e->getMessage());

            return back()->with('error', 'Data jadwal Posyandu gagal dimuat.');
        }
    }

    public function show(JadwalPosyandu $jadwal)
    {
        try {
            $this->autoUpdateStatus();

            $jadwal->refresh();

            return view('kader.jadwal.show', compact('jadwal'));
        } catch (\Throwable $e) {
            Log::error('KADER_JADWAL_SHOW_ERROR: ' . $e->getMessage());

            return redirect()
                ->route('kader.jadwal.index')
                ->with('error', 'Detail jadwal Posyandu tidak ditemukan.');
        }
    }

    private function applySearch(Builder $query, string $search): void
    {
        if ($search === '') {
            return;
        }

        $query->where(function ($q) use ($search) {
            $q->where('judul', 'like', "%{$search}%")
                ->orWhere('lokasi', 'like', "%{$search}%")
                ->orWhere('kategori', 'like', "%{$search}%")
                ->orWhere('target_peserta', 'like', "%{$search}%")
                ->orWhere('deskripsi', 'like', "%{$search}%");
        });
    }

    private function isValidMonth(?string $bulan): bool
    {
        return is_string($bulan) && preg_match('/^\d{4}-\d{2}$/', $bulan) === 1;
    }

    private function autoUpdateStatus(): void
    {
        $now = Carbon::now('Asia/Jakarta');

        JadwalPosyandu::query()
            ->where('status', 'aktif')
            ->where(function ($query) use ($now) {
                $query->whereDate('tanggal', '<', $now->toDateString())
                    ->orWhere(function ($q) use ($now) {
                        $q->whereDate('tanggal', $now->toDateString())
                            ->whereTime('waktu_selesai', '<', $now->toTimeString());
                    });
            })
            ->update(['status' => 'selesai']);
    }
}