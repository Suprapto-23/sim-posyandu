<?php

namespace App\Http\Controllers\Bidan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Pemeriksaan;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanController extends Controller
{
    /**
     * Halaman Filter & Preview Laporan
     */
    public function index(Request $request)
    {
        $bulan = $request->get('bulan', now()->month);
        $tahun = $request->get('tahun', now()->year);
        $jenis = $request->get('jenis', 'semua');

        $periode = Carbon::create($tahun, $bulan, 1);

        // Hanya ambil data yang SUDAH DIVALIDASI Bidan
        $query = Pemeriksaan::whereMonth('tanggal_periksa', $bulan)
            ->whereYear('tanggal_periksa', $tahun)
            ->where('status_verifikasi', 'verified');

        if ($jenis !== 'semua') {
            $query->where('kategori_pasien', $jenis);
        }

        $data = $query->get();

        // Kalkulasi Cerdas untuk Preview
        $ringkasan = [
            'total'      => $data->count(),
            'balita'     => $data->where('kategori_pasien', 'balita')->count(),
            'remaja'     => $data->where('kategori_pasien', 'remaja')->count(),
            'lansia'     => $data->where('kategori_pasien', 'lansia')->count(),
            'stunting'   => $data->filter(fn($p) => in_array(strtolower($p->status_gizi), ['stunting', 'buruk']))->count(),
            'hipertensi' => $data->filter(function($p) {
                $td = intval(explode('/', $p->tekanan_darah ?? '0/0')[0]);
                return $td >= 140;
            })->count(),
        ];

        return view('bidan.laporan.index', compact('bulan', 'tahun', 'jenis', 'periode', 'ringkasan'));
    }

    /**
     * Generate & Download PDF Menggunakan DomPDF
     */
    public function cetak(Request $request)
    {
        $bulan = $request->get('bulan', now()->month);
        $tahun = $request->get('tahun', now()->year);
        $jenis = $request->get('jenis', 'semua');

        $periode = Carbon::create($tahun, $bulan, 1);

        // Tarik data lengkap dengan relasi untuk dicetak
        $query = Pemeriksaan::with(['balita', 'remaja', 'lansia', 'pemeriksa', 'verifikator'])
            ->whereMonth('tanggal_periksa', $bulan)
            ->whereYear('tanggal_periksa', $tahun)
            ->where('status_verifikasi', 'verified')
            ->orderBy('tanggal_periksa', 'asc');

        if ($jenis !== 'semua') {
            $query->where('kategori_pasien', $jenis);
        }

        $pemeriksaans = $query->get();

        $judulJenis = match($jenis) {
            'balita'    => 'Kesehatan Anak & Balita',
            'remaja'    => 'Kesehatan Remaja',
            'lansia'    => 'Kesehatan Lansia',
            default     => 'Layanan Medis Terpadu',
        };

        // Render PDF (Kertas A4, posisi Memanjang/Landscape agar tabel muat)
        $pdf = Pdf::loadView('bidan.laporan.cetak', compact(
            'pemeriksaans', 'periode', 'jenis', 'judulJenis'
        ))->setPaper('a4', 'landscape');

        $namaFile = 'Laporan_Medis_' . str_replace(' ', '_', $judulJenis) . '_' . $periode->format('M_Y') . '.pdf';

        return $pdf->download($namaFile);
    }
}