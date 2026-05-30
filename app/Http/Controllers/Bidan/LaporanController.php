<?php

namespace App\Http\Controllers\Bidan;

use App\Http\Controllers\Controller;
use App\Models\Pemeriksaan;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    private array $bulanOptions = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    public function index(Request $request)
    {
        $bulan = $request->get('bulan', 'semua');
        $tahun = (int) $request->get('tahun', now()->year);
        $jenis = $request->get('jenis', 'semua');
        $status = $request->get('status', 'semua');

        $baseQuery = Pemeriksaan::query()
            ->whereNotNull('tanggal_periksa')
            ->whereYear('tanggal_periksa', $tahun);

        if ($bulan !== 'semua') {
            $baseQuery->whereMonth('tanggal_periksa', (int) $bulan);
        }

        if ($jenis !== 'semua') {
            $baseQuery->where('kategori_pasien', $jenis);
        }

        $this->applyStatusFilter($baseQuery, $status);

        $ringkasanQuery = clone $baseQuery;

        $ringkasan = [
            'total' => (clone $ringkasanQuery)->count(),
            'menunggu_review' => (clone $ringkasanQuery)->where(function ($q) {
                $q->whereNull('status_verifikasi')
                    ->orWhereIn('status_verifikasi', ['pending', 'menunggu_review']);
            })->count(),
            'disetujui' => (clone $ringkasanQuery)->whereIn('status_verifikasi', ['verified', 'approved', 'tervalidasi'])->count(),
            'perlu_revisi' => (clone $ringkasanQuery)->whereIn('status_verifikasi', ['rejected', 'ditolak', 'perlu_perbaikan'])->count(),
            'balita' => (clone $ringkasanQuery)->where('kategori_pasien', 'balita')->count(),
            'remaja' => (clone $ringkasanQuery)->where('kategori_pasien', 'remaja')->count(),
            'lansia' => (clone $ringkasanQuery)->where('kategori_pasien', 'lansia')->count(),
        ];

        $laporanMasuk = (clone $baseQuery)
            ->selectRaw('
                YEAR(tanggal_periksa) as tahun,
                MONTH(tanggal_periksa) as bulan,
                kategori_pasien as jenis,
                COUNT(*) as total_data,
                SUM(CASE WHEN status_verifikasi IS NULL OR status_verifikasi IN ("pending", "menunggu_review") THEN 1 ELSE 0 END) as menunggu_review,
                SUM(CASE WHEN status_verifikasi IN ("verified", "approved", "tervalidasi") THEN 1 ELSE 0 END) as disetujui,
                SUM(CASE WHEN status_verifikasi IN ("rejected", "ditolak", "perlu_perbaikan") THEN 1 ELSE 0 END) as perlu_revisi,
                MAX(updated_at) as terakhir_update
            ')
            ->groupBy(
                DB::raw('YEAR(tanggal_periksa)'),
                DB::raw('MONTH(tanggal_periksa)'),
                'kategori_pasien'
            )
            ->orderByDesc(DB::raw('MAX(updated_at)'))
            ->paginate(8)
            ->withQueryString();

        $tahunOptions = Pemeriksaan::query()
            ->whereNotNull('tanggal_periksa')
            ->selectRaw('YEAR(tanggal_periksa) as tahun')
            ->distinct()
            ->orderByDesc('tahun')
            ->pluck('tahun');

        if ($tahunOptions->isEmpty()) {
            $tahunOptions = collect([now()->year]);
        }

        $bulanOptions = $this->bulanOptions;

        return view('bidan.laporan.index', compact(
            'laporanMasuk',
            'ringkasan',
            'bulan',
            'tahun',
            'jenis',
            'status',
            'tahunOptions',
            'bulanOptions'
        ));
    }

    public function cetak(Request $request)
    {
        $bulan = $request->get('bulan', now()->month);
        $tahun = $request->get('tahun', now()->year);
        $jenis = $request->get('jenis', 'semua');

        $periode = Carbon::create($tahun, $bulan, 1);

        $query = Pemeriksaan::with(['balita', 'remaja', 'lansia', 'pemeriksa', 'verifikator'])
            ->whereMonth('tanggal_periksa', $bulan)
            ->whereYear('tanggal_periksa', $tahun)
            ->whereIn('status_verifikasi', ['verified', 'approved', 'tervalidasi'])
            ->orderBy('tanggal_periksa', 'asc');

        if ($jenis !== 'semua') {
            $query->where('kategori_pasien', $jenis);
        }

        $pemeriksaans = $query->get();

        $judulJenis = match ($jenis) {
            'balita' => 'Kesehatan Anak dan Balita',
            'remaja' => 'Kesehatan Remaja',
            'lansia' => 'Kesehatan Lansia',
            default => 'Layanan Medis Terpadu',
        };

        $pdf = Pdf::loadView('bidan.laporan.cetak', compact(
            'pemeriksaans',
            'periode',
            'jenis',
            'judulJenis'
        ))->setPaper('a4', 'landscape');

        $namaFile = 'Laporan_Medis_' . str_replace(' ', '_', $judulJenis) . '_' . $periode->format('M_Y') . '.pdf';

        return $pdf->download($namaFile);
    }

    public function uploadTtd(Request $request)
    {
        $request->validate([
            'ttd' => ['required', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
        ]);

        $request->file('ttd')->store('ttd-bidan', 'public');

        return back()->with('success', 'Tanda tangan Bidan berhasil diunggah.');
    }

    private function applyStatusFilter($query, string $status)
    {
        if ($status === 'menunggu_review') {
            return $query->where(function ($q) {
                $q->whereNull('status_verifikasi')
                    ->orWhereIn('status_verifikasi', ['pending', 'menunggu_review']);
            });
        }

        if ($status === 'disetujui') {
            return $query->whereIn('status_verifikasi', ['verified', 'approved', 'tervalidasi']);
        }

        if ($status === 'perlu_revisi') {
            return $query->whereIn('status_verifikasi', ['rejected', 'ditolak', 'perlu_perbaikan']);
        }

        return $query;
    }
}