<?php

namespace App\Http\Controllers\Kader;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

// Memanggil Model Entitas
use App\Models\Imunisasi;
use App\Models\Kunjungan;
use App\Models\Balita;


/**
 * =========================================================================
 * IMUNISASI CONTROLLER (NEXUS ENTERPRISE ARCHITECTURE)
 * =========================================================================
 * Mengelola akses Read-Only Imunisasi untuk Kader Posyandu.
 * Kode dirombak menjadi Modular dengan Sistem Failsafe Polymorphism,
 * Analytics Engine, dan Exception Handling tingkat tinggi.
 */
class ImunisasiController extends Controller
{
    /**
     * =========================================================================
     * 1. INDEX: DASHBOARD LOG & ANALITIK IMUNISASI
     * =========================================================================
     */
    public function index(Request $request)
    {
        try {
            // Ambil Parameter Input dari URL
            $kategori = $request->get('kategori', 'semua');
            $search   = trim($request->get('search', ''));

            // A. KUMPULKAN METRIK STATISTIK (DASHBOARD ENGINE)
            $statistics = $this->generateAnalytics();

            // B. BANGUN KUERI UTAMA (EAGER LOADING)
            // with() digunakan untuk mencegah N+1 Query Problem yang bikin web lambat
            $query = Imunisasi::with(['kunjungan.petugas', 'kunjungan.pasien'])
                              ->latest('tanggal_imunisasi');

            // C. TERAPKAN FILTER & PENCARIAN (MODULAR)
            $this->applyCategoryFilter($query, $kategori);
            $this->applySmartSearch($query, $search);

            // D. EKSEKUSI PAGINASI
            $imunisasis = $query->paginate(15)->withQueryString();

            // E. RENDER UI (MENDUKUNG AJAX SEAMLESS)
            if ($request->ajax() || $request->wantsJson()) {
                return view('kader.imunisasi.index', array_merge(
                    compact('imunisasis', 'kategori', 'search'), 
                    $statistics
                ))->render();
            }
                
            return view('kader.imunisasi.index', array_merge(
                compact('imunisasis', 'kategori', 'search'), 
                $statistics
            ));

        } catch (\Throwable $e) {
            // Jika database crash, sistem akan mencatatnya tanpa membuat layar blank
            Log::error('IMUNISASI_INDEX_CRASH: ' . $e->getMessage());
            return back()->with('error', 'Terjadi gangguan pada server saat memuat data Imunisasi.');
        }
    }

    /**
     * =========================================================================
     * 2. SHOW: DETAIL SERTIFIKAT IMUNISASI
     * =========================================================================
     */
    public function show($id)
    {
        try {
            $imunisasi = Imunisasi::with(['kunjungan.petugas', 'kunjungan.pasien'])->findOrFail($id);
            return view('kader.imunisasi.show', compact('imunisasi'));
        } catch (\Throwable $e) {
            Log::error('IMUNISASI_SHOW_CRASH: ' . $e->getMessage());
            return redirect()->route('kader.imunisasi.index')
                             ->with('error', 'Arsip imunisasi tidak ditemukan atau telah dihapus oleh sistem.');
        }
    }


    /**
     * =========================================================================
     * PRIVATE ENGINES (FUNGSI PEMBANTU INTERNAL - CLEAN CODE)
     * =========================================================================
     * Fungsi-fungsi di bawah ini memisahkan logika rumit dari fungsi utama,
     * membuat Controller sangat mudah dibaca, dilacak, dan dipelihara.
     */

    /**
     * Menghitung metrik performa imunisasi secara Real-Time.
     * Menggunakan klausa LIKE agar kebal terhadap error namespace di Laravel.
     */
    private function generateAnalytics(): array
    {
        $now = Carbon::now();

        // Kueri Dasar Bulan Ini
        $baseQuery = Imunisasi::whereMonth('tanggal_imunisasi', $now->month)
                              ->whereYear('tanggal_imunisasi', $now->year);

        return [
            'statBulanIni' => (clone $baseQuery)->count(),
            'statBalita'   => (clone $baseQuery)->whereHas('kunjungan', function($q) {
                                  $q->where('pasien_type', 'like', '%Balita%');
                              })->count(),
        ];
    }

    /**
     * Menerapkan filter kategori ke dalam Query Builder.
     */
    private function applyCategoryFilter(Builder $query, string $kategori): void
    {
        if (empty($kategori) || $kategori === 'semua') {
            return; // Lewati jika pilihannya "Semua"
        }

        $query->whereHas('kunjungan', function ($q) {
    $q->where('pasien_type', 'like', '%Balita%')
      ->orWhere('pasien_type', 'balita');
});
    }

    /**
     * Menerapkan pencarian cerdas ke berbagai kolom dan tabel relasi (Polymorphic).
     */
    private function applySmartSearch(Builder $query, string $search): void
    {
        if (empty($search)) {
            return;
        }

        $query->where(function($q) use ($search) {
            // Pencarian Primer: Cari di Kolom Tabel Imunisasi
            $q->where('vaksin', 'like', "%{$search}%")
              ->orWhere('jenis_imunisasi', 'like', "%{$search}%")
              ->orWhere('batch_number', 'like', "%{$search}%")
            
            // Pencarian Sekunder: Menyelam otomatis ke Tabel Pasien (Balita)
              ->orWhereHas('kunjungan', function($q2) use ($search) {
                  $q2->whereHasMorph('pasien', [Balita::class], function($morphQ) use ($search) {
                      $morphQ->where('nama_lengkap', 'like', "%{$search}%")
                             ->orWhere('nik', 'like', "%{$search}%");
                  });
              });
        });
    }


    /**
     * =========================================================================
     * SECURITY FIREWALL: BLOKIR AKSES CRUD UNTUK KADER
     * =========================================================================
     * Melindungi sistem dari eksploitasi URL (Direct Route Access).
     */
    public function create()  { return back()->with('error', 'Akses Terkunci! Wewenang Bidan (Meja 5).'); }
    public function store()   { abort(403, 'Akses Ditolak.'); }
    public function edit($id) { return back()->with('error', 'Akses Terkunci! Wewenang Bidan (Meja 5).'); }
    public function update()  { abort(403, 'Akses Ditolak.'); }
    public function destroy() { return back()->with('error', 'Akses Terkunci! Wewenang Bidan (Meja 5).'); }
}