<?php

namespace App\Http\Controllers\Kader;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

// Memanggil Semua Model Entitas
use App\Models\Pemeriksaan;
use App\Models\Kunjungan;
use App\Models\Balita;
use App\Models\Remaja;
use App\Models\Lansia;
 

/**
 * =========================================================================
 * PEMERIKSAAN CONTROLLER (ULTIMATE EDITION)
 * =========================================================================
 * Telah di-patch: Bug IMT Backend & API KEK Trigger.
 */
class PemeriksaanController extends Controller
{
    public function index(Request $request)
    {
        $kategori = $request->get('kategori', '');
        $search   = $request->get('search', '');
        $status   = $request->get('status', '');

        $query = Pemeriksaan::with(['kunjungan.pasien', 'kunjungan.petugas'])->latest('tanggal_periksa');

        if (!empty($kategori)) $query->kategori($kategori);
        
        if (!empty($status)) {
            if ($status === 'pending') {
                $query->pending(); 
            } else {
                $query->where('status_verifikasi', $status);
            }
        }

        if (!empty($search)) {
            $query->whereHas('kunjungan', function($q) use ($search) {
                $q->whereHasMorph('pasien', [Balita::class, Remaja::class, Lansia::class], function ($morphQ) use ($search) {
                    $morphQ->where('nama_lengkap', 'like', "%{$search}%")->orWhere('nik', 'like', "%{$search}%");
                });
            });
        }

        $pemeriksaans = $query->paginate(15)->withQueryString();

        return view('kader.pemeriksaan.index', compact('pemeriksaans', 'kategori', 'search', 'status'));
    }

    public function create(Request $request)
    {
        $kategori_awal = $request->get('kategori', 'balita');
        $pasien_id_awal = $request->get('pasien_id', null);

        return view('kader.pemeriksaan.create', compact('kategori_awal', 'pasien_id_awal'));
    }

    public function store(Request $request)
    {
        $rules = [
            'pasien_id'       => 'required',
             'kategori_pasien' => 'required|in:balita,remaja,lansia',
            'tanggal_periksa' => 'required|date|before_or_equal:today',
            'berat_badan'     => 'nullable|numeric|min:0.1|max:300',
            'tinggi_badan'    => 'nullable|numeric|min:10|max:250',
            'keluhan'         => 'nullable|string|max:1000',
            'catatan_kader'   => 'nullable|string|max:1000',
        ];

        $kategori = $request->kategori_pasien;
        if ($kategori === 'balita') {
            $rules['lingkar_kepala'] = 'nullable|numeric|min:10|max:100';
            $rules['suhu_tubuh']     = 'nullable|numeric|min:30|max:45';
        
        } elseif ($kategori === 'lansia') {
            $rules['tekanan_darah']  = 'nullable|string|max:15';
            $rules['lingkar_perut']  = 'nullable|numeric|min:20|max:200';
            $rules['gula_darah']     = 'nullable|numeric|min:10|max:1000';
            $rules['kolesterol']     = 'nullable|integer|min:10|max:1000';
            $rules['asam_urat']      = 'nullable|numeric|min:1|max:30';
        }

        $request->validate($rules);

        // FIX BUG 1: KALKULASI IMT DI BACKEND
        $imt = null;
        if ($request->berat_badan && $request->tinggi_badan && $kategori !== 'balita') {
            $tinggiM = $request->tinggi_badan / 100;
            $imt = round($request->berat_badan / ($tinggiM * $tinggiM), 2);
        }

        DB::beginTransaction();
        try {
            $kunjungan = Kunjungan::create([
                'pasien_id'         => $request->pasien_id,
                'pasien_type'       => $this->mapPasienType($kategori),
                'tanggal_kunjungan' => $request->tanggal_periksa,
                'keluhan'           => $request->keluhan,
                'pemeriksa_id'      => Auth::id(),
            ]);

            Pemeriksaan::create([
                'kunjungan_id'    => $kunjungan->id,
                'pasien_id'       => $request->pasien_id,
                'kategori_pasien' => $kategori,
                'tanggal_periksa' => $request->tanggal_periksa,
                'berat_badan'     => $request->berat_badan,
                'tinggi_badan'    => $request->tinggi_badan,
                'suhu_tubuh'      => $request->suhu_tubuh,
                'imt'             => $imt, // <-- IMT KINI TERSIMPAN AMAN
                'lingkar_kepala'  => $request->lingkar_kepala,
                'lingkar_lengan'  => $request->lingkar_lengan,
                'lingkar_perut'   => $request->lingkar_perut,
                'tekanan_darah'   => $request->tekanan_darah,
                'gula_darah'      => $request->gula_darah,
                'kolesterol'      => $request->kolesterol,
                'asam_urat'       => $request->asam_urat,
                'hemoglobin'      => $request->hemoglobin,
                'keluhan'         => $request->keluhan,
                'catatan_kader'   => $request->catatan_kader,
                'status_verifikasi' => 'pending',
                'created_by'      => Auth::id(),
            ]);

            DB::commit();
            return redirect()->route('kader.pemeriksaan.index')
                             ->with('success', 'Rekam Medis Berhasil Disimpan & Menunggu Validasi Bidan.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PEMERIKSAAN_STORE_CRITICAL_ERROR: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Kegagalan Server: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $pemeriksaan = Pemeriksaan::with(['kunjungan.pasien', 'kunjungan.petugas', 'verifikator'])->findOrFail($id);
        return view('kader.pemeriksaan.show', compact('pemeriksaan'));
    }

    public function edit($id)
    {
        $pemeriksaan = Pemeriksaan::with('kunjungan.pasien')->findOrFail($id);
        
        if (in_array($pemeriksaan->status_verifikasi, ['tervalidasi', 'verified', 'approved'])) {
            return back()->with('error', 'Akses Terkunci! Anda tidak berhak mengubah data medis yang sudah divalidasi oleh Bidan.');
        }

        return view('kader.pemeriksaan.edit', compact('pemeriksaan'));
    }

    public function update(Request $request, $id)
    {
        $pemeriksaan = Pemeriksaan::findOrFail($id);

        if (in_array($pemeriksaan->status_verifikasi, ['tervalidasi', 'verified', 'approved'])) {
            return back()->with('error', 'Bypass Terdeteksi: Akses ditolak oleh sistem keamanan.');
        }

        $rules = [
            'tanggal_periksa' => 'required|date|before_or_equal:today',
            'berat_badan'     => 'nullable|numeric|min:0.1|max:300',
            'tinggi_badan'    => 'nullable|numeric|min:10|max:250',
            'keluhan'         => 'nullable|string|max:1000',
            'catatan_kader'   => 'nullable|string|max:1000',
        ];

        $kategori = $pemeriksaan->kategori_pasien;
        if ($kategori === 'balita') {
            $rules['lingkar_kepala'] = 'nullable|numeric|min:10|max:100';
            $rules['suhu_tubuh']     = 'nullable|numeric|min:30|max:45';
        
        } elseif ($kategori === 'lansia') {
            $rules['tekanan_darah']  = 'nullable|string|max:15';
            $rules['lingkar_perut']  = 'nullable|numeric|min:20|max:200';
            $rules['gula_darah']     = 'nullable|numeric|min:10|max:1000';
            $rules['kolesterol']     = 'nullable|integer|min:10|max:1000';
            $rules['asam_urat']      = 'nullable|numeric|min:1|max:30';
        }

        $request->validate($rules);

        // FIX BUG 1: KALKULASI IMT DI BACKEND (UPDATE)
        $imt = $pemeriksaan->imt;
        if ($request->berat_badan && $request->tinggi_badan && $kategori !== 'balita') {
            $tinggiM = $request->tinggi_badan / 100;
            $imt = round($request->berat_badan / ($tinggiM * $tinggiM), 2);
        }

        DB::beginTransaction();
        try {
            $pemeriksaan->update([
                'tanggal_periksa'   => $request->tanggal_periksa,
                'berat_badan'       => $request->berat_badan,
                'tinggi_badan'      => $request->tinggi_badan,
                'suhu_tubuh'        => $request->suhu_tubuh,
                'imt'               => $imt, // <-- UPDATE IMT TERKUNCI AMAN
                'lingkar_kepala'    => $request->lingkar_kepala ?? $pemeriksaan->lingkar_kepala,
                'lingkar_lengan'    => $request->lingkar_lengan ?? $pemeriksaan->lingkar_lengan,
                'lingkar_perut'     => $request->lingkar_perut ?? $pemeriksaan->lingkar_perut,
                'tekanan_darah'     => $request->tekanan_darah ?? $pemeriksaan->tekanan_darah,
                'gula_darah'        => $request->gula_darah ?? $pemeriksaan->gula_darah,
                'kolesterol'        => $request->kolesterol ?? $pemeriksaan->kolesterol,
                'asam_urat'         => $request->asam_urat ?? $pemeriksaan->asam_urat,
                'keluhan'           => $request->keluhan,
                'catatan_kader'     => $request->catatan_kader,
                'status_verifikasi' => $pemeriksaan->status_verifikasi === 'ditolak' ? 'pending' : $pemeriksaan->status_verifikasi,
            ]);

            if ($pemeriksaan->kunjungan) {
                $pemeriksaan->kunjungan->update([
                    'tanggal_kunjungan' => $request->tanggal_periksa,
                    'keluhan'           => $request->keluhan,
                ]);
            }

            DB::commit();
            
            // Format JSON Respons agar dikenali oleh Fetch API di edit.blade.php
            if($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'success', 
                    'message' => 'Log medis berhasil dikoreksi.',
                    'redirect' => route('kader.pemeriksaan.index')
                ]);
            }
            
            return redirect()->route('kader.pemeriksaan.index')->with('success', 'Koreksi Berhasil! Log pemeriksaan telah diperbarui.');

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('PEMERIKSAAN_UPDATE_CRITICAL_ERROR: ' . $e->getMessage());
            
            if($request->ajax() || $request->wantsJson()) {
                return response()->json(['status' => 'error', 'message' => 'Gagal: ' . $e->getMessage()]);
            }
            return back()->withInput()->with('error', 'Sistem Gagal memperbarui data: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $pemeriksaan = Pemeriksaan::findOrFail($id);

            if (in_array($pemeriksaan->status_verifikasi, ['tervalidasi', 'verified', 'approved'])) {
                return back()->with('error', 'Akses Ditolak! Anda tidak diizinkan menghapus data yang telah menjadi Rekam Medis Sah Bidan.');
            }

            $kunjungan_id = $pemeriksaan->kunjungan_id;
            $pemeriksaan->delete();

            if ($kunjungan_id) {
                $kunjungan = Kunjungan::find($kunjungan_id);
                if ($kunjungan) {
                    $hasPemeriksaanLain = $kunjungan->pemeriksaan()->count() > 0;
                    $hasImunisasi       = $kunjungan->imunisasis()->count() > 0;

                    if (!$hasPemeriksaanLain && !$hasImunisasi) {
                        $kunjungan->delete();
                    }
                }
            }

            DB::commit();
            return redirect()->route('kader.pemeriksaan.index')->with('success', 'Log pemeriksaan (dan kunjungan terkait) berhasil dihanguskan dari sistem.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('PEMERIKSAAN_DELETE_ERROR: ' . $e->getMessage());
            return back()->with('error', 'Terjadi konflik internal saat menghapus data.');
        }
    }

    private function mapPasienType(string $kategori): string
    {
        return match($kategori) {
            'balita'    => 'App\Models\Balita',
            'remaja'    => 'App\Models\Remaja',
            'lansia'    => 'App\Models\Lansia',
            default     => 'App\Models\User',
        };
    }

    /**
     * API ENDPOINT
     * FIX BUG 2: Penambahan atribut `jenis_kelamin` agar fungsi KEK berfungsi.
     */
    public function getPasienApi(Request $request)
    {
        $kategori = $request->get('kategori');
        $data = [];

        try {
            if ($kategori === 'balita') {
                $data = Balita::select('id', 'nama_lengkap as nama', 'nik', 'jenis_kelamin')->orderBy('nama_lengkap')->get();
            } elseif ($kategori === 'remaja') {
                // Perlu 'jenis_kelamin' untuk memicu warning KEK pada Remaja Putri
                $data = Remaja::select('id', 'nama_lengkap as nama', 'nik', 'jenis_kelamin')->orderBy('nama_lengkap')->get();
            } elseif ($kategori === 'lansia') {
                $data = Lansia::select('id', 'nama_lengkap as nama', 'nik', 'jenis_kelamin')->orderBy('nama_lengkap')->get();
            }

            return response()->json([
                'status'  => 'success',
                'data'    => $data,
                'message' => 'Data berhasil dimuat.'
            ]);
        } catch (\Throwable $e) {
            Log::error('API_PASIEN_FETCH_ERROR: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'data'    => [],
                'message' => 'Koneksi gagal. Sistem tidak dapat menarik data populasi.'
            ], 500);
        }
    }
}