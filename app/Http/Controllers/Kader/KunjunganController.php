<?php

namespace App\Http\Controllers\Kader;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Models\Kunjungan;
use App\Models\Balita;
use App\Models\Remaja;
use App\Models\Lansia;


/**
 * =========================================================================
 * KUNJUNGAN CONTROLLER (POSYANDU NEXUS EDITION)
 * =========================================================================
 * Mengelola Buku Induk Pendaftaran Warga (Sistem 5 Meja Posyandu).
 * Modul ini bersifat Read-Only karena data terpusat dari pelayanan medis.
 */
class KunjunganController extends Controller
{
    /**
     * 1. INDEX: TAMPILAN BUKU INDUK PENDAFTARAN
     */
    public function index(Request $request)
    {
        $search   = $request->get('search', '');
        $kategori = $request->get('kategori', 'semua');

        // Eager Load untuk menghindari N+1 Problem (Performa Maksimal)
        $query = Kunjungan::with(['pasien', 'petugas', 'pemeriksaan', 'imunisasis'])
                    ->latest('tanggal_kunjungan')
                    ->latest('created_at');

        // Filter Berdasarkan Kategori Warga
        if ($kategori !== 'semua') {
            $pasienType = match($kategori) {
                'remaja'    => 'App\Models\Remaja',
                'lansia'    => 'App\Models\Lansia',
                default     => 'App\Models\Balita',
            };
            $query->where('pasien_type', $pasienType);
        }

        // Pencarian Real-Time Cerdas (Menyisir 4 Tabel Sekaligus)
        if (!empty($search)) {
            $query->whereHasMorph('pasien', [Balita::class, Remaja::class, Lansia::class], function ($morphQ) use ($search) {
                $morphQ->where('nama_lengkap', 'like', "%{$search}%")
                       ->orWhere('nik', 'like', "%{$search}%");
            });
        }

        $kunjungans = $query->paginate(15)->withQueryString();

        // Respon Instan untuk AJAX Live Search
        if ($request->ajax() || $request->wantsJson()) {
            return view('kader.kunjungan.index', compact('kunjungans', 'search', 'kategori'))->render();
        }

        return view('kader.kunjungan.index', compact('kunjungans', 'search', 'kategori'));
    }

    /**
     * 2. SHOW: DETAIL BUKTI PENDAFTARAN (ARSIP)
     */
    public function show($id)
    {
        $kunjungan = Kunjungan::with(['pasien', 'petugas', 'pemeriksaan', 'imunisasis'])->findOrFail($id);
        return view('kader.kunjungan.show', compact('kunjungan'));
    }

    /**
     * ====================================================================
     * KUNCI OTORITAS: PENCEGAHAN EDIT MANUAL (INTEGRITAS DATA)
     * ====================================================================
     */
    public function create() {
        return back()->with('error', 'Informasi: Kunjungan warga akan tercatat secara otomatis ketika Anda memproses pendaftaran atau pelayanan.');
    }

    public function store(Request $request) { 
        abort(403, 'Akses Ditolak.'); 
    }

    public function edit($id) {
        return back()->with('error', 'Buku Induk bersifat permanen. Jika ada kesalahan data fisik, silakan ubah melalui menu Pemeriksaan Medis.');
    }

    public function update(Request $request, $id) { 
        abort(403, 'Akses Ditolak.'); 
    }

    public function destroy($id) {
        return back()->with('error', 'Tindakan Ilegal! Arsip kunjungan warga tidak boleh dihapus secara manual demi integritas laporan Posyandu.');
    }
}