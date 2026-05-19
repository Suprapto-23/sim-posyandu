<?php

namespace App\Http\Controllers\Kader;

use App\Http\Controllers\Controller;
use App\Models\AbsensiPosyandu;
use App\Models\AbsensiDetail;
use App\Models\Balita;
use App\Models\Remaja;
use App\Models\Lansia;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AbsensiController extends Controller
{
    /**
     * =========================================================================
     * 1. HALAMAN UTAMA INPUT ABSENSI (PRESENSI)
     * =========================================================================
     */
    public function index(Request $request)
    {
        // Proteksi ketat: Hanya izinkan 3 entitas utama
        $kategori = $request->get('kategori', 'balita');
        if (!in_array($kategori, ['balita', 'remaja', 'lansia'])) {
            return redirect()->route('kader.absensi.index', ['kategori' => 'balita']);
        }

        $pasiens = $this->getPasienByKategori($kategori);
        $tanggal = today()->format('Y-m-d');

        // Cek sesi hari ini dengan Eager Loading untuk efisiensi
        $sesiHariIni = AbsensiPosyandu::with(['details' => function($query) {
                $query->select('absensi_id', 'pasien_id', 'hadir', 'keterangan');
            }])
            ->where('kategori', $kategori)
            ->whereDate('tanggal_posyandu', $tanggal)
            ->first();

        // Mapping data absensi yang sudah terisi ke dalam array memory
        $absensiData = [];
        $pertemuanBerikutnya = AbsensiPosyandu::where('kategori', $kategori)->count() + 1;

        if ($sesiHariIni) {
            foreach ($sesiHariIni->details as $detail) {
                $absensiData[$detail->pasien_id] = [
                    'hadir'      => $detail->hadir,
                    'keterangan' => $detail->keterangan
                ];
            }
            $pertemuanBerikutnya = $sesiHariIni->nomor_pertemuan; 
        }

        // Kalkulasi Statistik Sidebar
        $statsPerKategori = [];
        foreach (['balita', 'remaja', 'lansia'] as $kat) {
            $statsPerKategori[$kat] = [
                'total_pertemuan' => AbsensiPosyandu::where('kategori', $kat)->count(),
                'total_pasien'    => $this->getPasienByKategori($kat)->count(),
            ];
        }

        return view('kader.absensi.index', compact(
            'kategori', 'pasiens', 'pertemuanBerikutnya', 'sesiHariIni', 'statsPerKategori', 'absensiData'
        ));
    }

    /**
     * =========================================================================
     * 2. MESIN PENYIMPANAN MASSAL (TRANSAKSI AMAN & OPTIMAL)
     * =========================================================================
     */
    public function store(Request $request)
    {
        // Validasi strict
        $request->validate([
            'kategori'   => 'required|in:balita,remaja,lansia',
            'kehadiran'  => 'required|array', 
            'keterangan' => 'nullable|array',
        ]);

        $kategori = $request->kategori;
        $tanggal  = today()->format('Y-m-d');
        
        DB::beginTransaction();
        try {
            // A. Inisialisasi atau Ambil Sesi Master
            $sesi = AbsensiPosyandu::firstOrCreate(
                [
                    'kategori'         => $kategori,
                    'tanggal_posyandu' => $tanggal
                ],
                [
                    'kode_absensi'     => 'ABS-' . strtoupper(substr($kategori, 0, 3)) . '-' . date('Ymd') . '-' . rand(1000,9999),
                    'nomor_pertemuan'  => AbsensiPosyandu::where('kategori', $kategori)->count() + 1,
                    'bulan'            => date('m'),
                    'tahun'            => date('Y'),
                    'dicatat_oleh'     => auth()->id(),
                ]
            );

            // B. Proses Detail Absensi (Upsert Logic)
            foreach ($request->kehadiran as $pasien_id => $statusHadir) {
                // Konversi boolean strict
                $isHadir = filter_var($statusHadir, FILTER_VALIDATE_BOOLEAN);
                $keterangan = !$isHadir ? ($request->keterangan[$pasien_id] ?? null) : null;

                AbsensiDetail::updateOrCreate(
                    [
                        'absensi_id'  => $sesi->id,
                        'pasien_id'   => $pasien_id,
                        'pasien_type' => $kategori
                    ],
                    [
                        'hadir'       => $isHadir,
                        'keterangan'  => $keterangan,
                        'updated_at'  => now(),
                    ]
                );
            }

            DB::commit();
            return redirect()->route('kader.absensi.success')
                             ->with('success', 'Data presensi berhasil disinkronisasi ke server.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Kegagalan Sync Absensi Kader ({$kategori}): " . $e->getMessage());
            return back()->with('error', 'Integritas data gagal! Sistem membatalkan penyimpanan otomatis.');
        }
    }

    /**
     * =========================================================================
     * 3. HALAMAN ANIMASI SUKSES
     * =========================================================================
     */
    public function success()
    {
        return view('kader.absensi.success');
    }

    /**
     * =========================================================================
     * 4. HALAMAN DETAIL ABSENSI (DIOPTIMALKAN DARI N+1 QUERY PROBLEM)
     * =========================================================================
     */
    public function show($id)
    {
        $absensi = AbsensiPosyandu::with('pencatat')->findOrFail($id);
        $details = AbsensiDetail::where('absensi_id', $id)->get();

        // BULK QUERY OPTIMIZATION 
        $pasienIds = $details->pluck('pasien_id')->toArray();
        
        $pasienData = match($absensi->kategori) {
            'remaja' => Remaja::whereIn('id', $pasienIds)->select('id', 'nama_lengkap', 'nik')->get()->keyBy('id'),
            'lansia' => Lansia::whereIn('id', $pasienIds)->select('id', 'nama_lengkap', 'nik')->get()->keyBy('id'),
            default  => Balita::whereIn('id', $pasienIds)->select('id', 'nama_lengkap', 'nik')->get()->keyBy('id'),
        };

        // Menyuntikkan relasi ke collection
        $details->map(function ($d) use ($pasienData) {
            $d->pasien_data = $pasienData->get($d->pasien_id);
            return $d;
        });

        $totalPasien = $details->count();
        $totalHadir  = $details->where('hadir', true)->count();
        $totalAbsen  = $totalPasien - $totalHadir;

        // Ambil riwayat sesi lain untuk sidebar navigasi cepat
        $semuaSesi = AbsensiPosyandu::where('kategori', $absensi->kategori)
            ->select('id', 'nomor_pertemuan', 'tanggal_posyandu')
            ->orderBy('tanggal_posyandu', 'desc')
            ->limit(10)
            ->get();

        return view('kader.absensi.show', compact(
            'absensi', 'details', 'totalHadir', 'totalAbsen', 'totalPasien', 'semuaSesi'
        ));
    }

    /**
     * =========================================================================
     * 5. HALAMAN DAFTAR RIWAYAT (HISTORY)
     * =========================================================================
     */
    public function riwayat(Request $request)
    {
        $kategori = $request->get('kategori');
        $bulan    = $request->get('bulan');

        $query = AbsensiPosyandu::withCount([
            'details as total_hadir' => function ($query) {
                $query->where('hadir', true);
            },
            'details as total_pasien'
        ])->latest('tanggal_posyandu');

        if ($kategori && in_array($kategori, ['balita', 'remaja', 'lansia'])) {
            $query->where('kategori', $kategori);
        }

        if ($bulan) {
            $parts = explode('-', $bulan);
            if (count($parts) === 2) {
                $query->whereYear('tanggal_posyandu', $parts[0])
                      ->whereMonth('tanggal_posyandu', $parts[1]);
            }
        }

        $riwayat = $query->paginate(15)->withQueryString();

        return view('kader.absensi.riwayat', compact('riwayat'));
    }

    /**
     * =========================================================================
     * 6. FUNGSI HAPUS (DELETE DENGAN PENGAMANAN)
     * =========================================================================
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $absensi = AbsensiPosyandu::findOrFail($id);
            AbsensiDetail::where('absensi_id', $absensi->id)->delete();
            $absensi->delete();
            
            DB::commit();
            return back()->with('success', 'Riwayat presensi berhasil dihanguskan dari sistem.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Kegagalan Destruksi Absensi: ' . $e->getMessage());
            return back()->with('error', 'Terdapat kunci relasi. Sistem menolak penghapusan data.');
        }
    }

    /**
     * =========================================================================
     * 7. CORE ENGINE: PENARIKAN DATA ENTITAS PASIEN
     * =========================================================================
     */
    private function getPasienByKategori(string $kategori)
    {
        return match($kategori) {
            // Anak Balita: Strictly 12 - 59 Bulan sesuai standar medis Kemenkes
            'balita' => Balita::whereRaw('TIMESTAMPDIFF(MONTH, tanggal_lahir, CURDATE()) BETWEEN 12 AND 59')
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