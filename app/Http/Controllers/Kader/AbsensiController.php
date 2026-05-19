<?php

namespace App\Http\Controllers\Kader;

use App\Http\Controllers\Controller;
use App\Models\AbsensiPosyandu;
use App\Models\AbsensiDetail;
use App\Models\Balita;
use App\Models\Remaja;
use App\Models\Lansia;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support5\Facades\Log;

class AbsensiController extends Controller
{
    /**
     * Mapping kategori string ke namespace Model terkait.
     */
    private array $modelMapping = [
        'balita' => Balita::class,
        'remaja' => Remaja::class,
        'lansia' => Lansia::class,
    ];

    /**
     * =========================================================================
     * 1. HALAMAN UTAMA INPUT ABSENSI (PRESENSI)
     * =========================================================================
     */
    public function index(Request $request): View|RedirectResponse
    {
        $kategori = $request->get('kategori', 'balita');
        
        // Anti-Tampering: Validasi kategori via mapping array
        if (!array_key_exists($kategori, $this->modelMapping)) {
            return redirect()->route('kader.absensi.index', ['kategori' => 'balita']);
        }

        $pasiens = $this->getPasienByKategori($kategori);
        $tanggal = today()->format('Y-m-d');

        // Eager Loading rincian absensi hari ini
        $sesiHariIni = AbsensiPosyandu::with(['details' => function($query) {
                $query->select('id', 'absensi_id', 'pasien_id', 'hadir', 'keterangan');
            }])
            ->where('kategori', $kategori)
            ->whereDate('tanggal_posyandu', $tanggal)
            ->first();

        return view('kader.absensi.index', compact('pasiens', 'kategori', 'tanggal', 'sesiHariIni'));
    }

    /**
     * =========================================================================
     * 2. ENGINE PENYIMPANAN DATA (SMART INSERT & UPDATE)
     * =========================================================================
     */
    public function store(Request $request): RedirectResponse
    {
        $kategori = $request->input('kategori');
        
        if (!array_key_exists($kategori, $this->modelMapping)) {
            return back()->with('error', 'Kategori layanan tidak valid.');
        }

        // 1. Validasi Struktur Request Dasar
        $request->validate([
            'kehadiran'  => 'required|array',
            'keterangan' => 'nullable|array',
        ]);

        $kehadiranData = $request->input('kehadiran');
        $pasienIds = array_keys($kehadiranData);
        $modelClass = $this->modelMapping[$kategori];

        // 2. CRITICAL SECURITY LOCK: Validasi keaslian ID Pasien di Database
        $validCount = $modelClass::whereIn('id', $pasienIds)->count();
        if ($validCount !== count($pasienIds)) {
            return back()->with('error', 'Sistem mendeteksi adanya manipulasi data ID warga! Proses dibatalkan.');
        }

        DB::beginTransaction();
        try {
            $tanggal = today();
            
            // Generate atau dapatkan nomor pertemuan berjalan di bulan ini
            $nomorPertemuan = AbsensiPosyandu::where('kategori', $kategori)
                ->where('bulan', $tanggal->month)
                ->where('tahun', $tanggal->year)
                ->count() + 1;

            // 3. Simpan / Dapatkan Header Sesi Absensi
            $absensi = AbsensiPosyandu::firstOrCreate(
                [
                    'kategori'         => $kategori,
                    'tanggal_posyandu' => $tanggal->format('Y-m-d'),
                ],
                [
                    'kode_absensi'     => 'ABS-' . strtoupper($kategori) . '-' . $tanggal->format('YmdHis'),
                    'nomor_pertemuan'  => $nomorPertemuan,
                    'bulan'            => $tanggal->month,
                    'tahun'            => $tanggal->year,
                    'dicatat_oleh'     => auth()->id() ?? 1,
                ]
            );

            // 4. Proses Upsert Rincian Menggunakan Keunggulan Polymorphic
            foreach ($kehadiranData as $pasienId => $status) {
                $absensi->details()->updateOrCreate(
                    [
                        'pasien_id'   => $pasienId,
                        'pasien_type' => $modelClass, // Menyimpan class model polimorfik
                    ],
                    [
                        'hadir'       => (bool) $status,
                        'keterangan'  => $request->input("keterangan.$pasienId"),
                    ]
                );
            }

            DB::commit();
            return redirect()->route('kader.absensi.success', ['id' => $absensi->id]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Kegagalan Sistem Absensi (Store): ' . $e->getMessage());
            return back()->with('error', 'Kegagalan database saat memproses absensi.');
        }
    }

    /**
     * =========================================================================
     * 3. ENGINE NOTIFIKASI SUKSES (SUCCESS SCREEN)
     * =========================================================================
     */
    public function success($id): View|RedirectResponse
    {
        $absensi = AbsensiPosyandu::with(['pencatat', 'details'])->findOrFail($id);
        return view('kader.absensi.success', compact('absensi'));
    }

    /**
     * =========================================================================
     * 4. ARSIP & RIWAYAT ABSENSI BULANAN
     * =========================================================================
     */
    public function riwayat(Request $request): View
    {
        $bulan = $request->get('bulan', today()->month);
        $tahun = $request->get('tahun', today()->year);

        // Menggunakan Local Scopes dari model AbsensiPosyandu yang sudah kita rapihkan
        $riwayats = AbsensiPosyandu::with(['pencatat'])
            ->withCount(['details', 'hadir'])
            ->periode($bulan, $tahun)
            ->orderBy('tanggal_posyandu', 'desc')
            ->get();

        return view('kader.absensi.riwayat', compact('riwayats', 'bulan', 'tahun'));
    }

    /**
     * =========================================================================
     * 5. MANIFEST DETAIL SESI (Memanfaatkan Polymorphic Eager Loading)
     * =========================================================================
     */
    public function show($id): View
    {
        // Berkat Polymorphic, kita tinggal panggil 'details.pasien'
        // Eloquent otomatis tahu relasi ke tabel Balita/Remaja/Lansia secara instan!
        $absensi = AbsensiPosyandu::with([
            'pencatat', 
            'details.pasien:id,nama_lengkap,nik'
        ])->findOrFail($id);

        return view('kader.absensi.show', compact('absensi'));
    }

    /**
     * =========================================================================
     * 6. PROSES DESTRUKSI DATA (HAPUS PERTEMUAN)
     * =========================================================================
     */
    public function destroy($id): RedirectResponse
    {
        DB::beginTransaction();
        try {
            $absensi = AbsensiPosyandu::findOrFail($id);
            
            // Hapus detail terlebih dahulu untuk menjaga integritas foreign key
            $absensi->details()->delete();
            $absensi->delete();

            DB::commit();
            return back()->with('success', 'Riwayat presensi berhasil dihanguskan dari sistem.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Kegagalan Destruksi Absensi: ' . $e->getMessage());
            return back()->with('error', 'Terdapat kunci relasi aktif. Sistem menolak penghapusan data.');
        }
    }

    /**
     * =========================================================================
     * 7. CORE ENGINE: PENARIKAN DATA ENTITAS PASIEN (OPTIMIZED)
     * =========================================================================
     */
    private function getPasienByKategori(string $kategori)
    {
        return match($kategori) {
            // Anak Balita: 12 - 59 Bulan. Menggunakan rentang tanggal agar ramah Database Indexing
            'balita' => Balita::whereBetween('tanggal_lahir', [
                                  now()->subMonths(59)->startOfDay()->format('Y-m-d'),
                                  now()->subMonths(12)->endOfDay()->format('Y-m-d')
                              ])
                              ->select('id', 'nama_lengkap', 'nik')
                              ->orderBy('nama_lengkap')
                              ->get(),
                              
            'remaja' => Remaja::select('id', 'nama_lengkap', 'nik')
                              ->orderBy('nama_lengkap')
                              ->get(),
                              
            'lansia' => Lansia::select('id', 'nama_lengkap', 'nik')
                              ->orderBy('nama_lengkap')
                              ->get(),
                              
            default  => collect(),
        };
    }
}