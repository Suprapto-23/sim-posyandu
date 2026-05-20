<?php

namespace App\Http\Controllers\Kader;

use App\Http\Controllers\Controller;
use App\Models\Pemeriksaan;
use App\Models\Imunisasi;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * =========================================================================
 * LAPORAN CONTROLLER (NEXUS ULTIMATE EDITION)
 * =========================================================================
 * Mesin utama pengolah dokumen PDF Posyandu.
 */
class LaporanController extends Controller
{
    public function index()
    {
        return view('kader.laporan.index');
    }

    public function generate(Request $request)
    {
        // 1. STABILITAS SISTEM
        ini_set('memory_limit', '1024M'); 
        set_time_limit(300);

        // 2. VALIDASI PARAMETER
        $type   = $request->query('type') ?? $request->type;
        $bulan  = (int) ($request->query('bulan') ?? $request->bulan ?? date('m'));
        $tahun  = (int) ($request->query('tahun') ?? $request->tahun ?? date('Y'));

        $validTypes = ['balita', 'remaja', 'lansia', 'imunisasi'];
        if (!in_array($type, $validTypes)) {
            return back()->with('error', 'Kategori laporan tidak terdaftar dalam protokol sistem.');
        }

        $namaBulan = Carbon::create()->month($bulan)->locale('id')->translatedFormat('F');
        $fileName  = "Arsip_" . strtoupper($type) . "_Bantarkulon_{$namaBulan}_{$tahun}";

        $data = collect(); 

        try {
            // 3. DATA GATHERING (OPTIMIZED EAGER LOADING)
            if ($type === 'imunisasi') {
                $data = Imunisasi::with(['kunjungan.pasien', 'kunjungan.petugas'])
                    ->whereMonth('tanggal_imunisasi', $bulan)
                    ->whereYear('tanggal_imunisasi', $tahun)
                    ->oldest('tanggal_imunisasi')
                    ->get();
            } 
            else {
                $kategori_query = $type === 'balita' ? ['bayi', 'balita'] : [$type];
                
                $data = Pemeriksaan::with(['kunjungan.pasien', 'kunjungan.petugas'])
                    ->whereIn('kategori_pasien', $kategori_query)
                    ->whereMonth('tanggal_periksa', $bulan)
                    ->whereYear('tanggal_periksa', $tahun)
                    ->oldest('tanggal_periksa')
                    ->get();

                // ========================================================================
                // INJEKSI RELASI DATA (MEMPERBAIKI BUG KOLOM L/P KOSONG DI PDF)
                // ========================================================================
                foreach ($data as $row) {
                    // Ambil entitas pasien dari relasi kunjungan
                    $pasien = $row->kunjungan->pasien ?? null;
                    
                    // Suntikkan nama asli dan jenis kelamin ke dalam baris data
                    $row->nama_pasien = $pasien->nama_lengkap ?? $row->nama_pasien ?? 'Tanpa Nama';
                    $row->jenis_kelamin = $pasien->jenis_kelamin ?? '-';
                }
            }

            // 4. VALIDASI KETERSEDIAAN DATA
            if ($data->isEmpty()) {
                $label = strtoupper(str_replace('_', ' ', $type));
                return back()->with('error', "Dokumen {$label} periode {$namaBulan} {$tahun} belum tersedia.");
            }

            // 5. PDF COMPILATION & RENDERING
            $pdf = Pdf::loadView("kader.laporan.templates.table-{$type}", compact('data', 'bulan', 'tahun', 'namaBulan'))
                ->setPaper('A4', 'landscape')
                ->setOption('isHtml5ParserEnabled', true)
                ->setOption('isRemoteEnabled', true); 

            return $pdf->download($fileName . '.pdf');

        } catch (\Exception $e) {
            Log::error('CRITICAL_PDF_ENGINE_FAIL: ' . $e->getMessage());
            return back()->with('error', 'Kegagalan teknis pada mesin PDF. Silakan coba beberapa saat lagi.');
        }
    }
}