<?php

namespace App\Http\Controllers\Bidan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Models\Pemeriksaan;
// INJEKSI OTAL MEDIS (SERVICES)
use App\Services\AnalisisBalitaService;
use App\Services\AnalisisRemajaService;
use App\Services\AnalisisIbuHamilService;
use App\Services\AnalisisLansiaService;

class PemeriksaanController extends Controller
{
    protected $balitaService;
    protected $remajaService;
    protected $bumilService;
    protected $lansiaService;

    /**
     * Constructor dengan Dependency Injection untuk seluruh layanan analisis
     */
    public function __construct(
        AnalisisBalitaService $balitaService,
        AnalisisRemajaService $remajaService,
        AnalisisIbuHamilService $bumilService,
        AnalisisLansiaService $lansiaService
    ) {
        $this->balitaService = $balitaService;
        $this->remajaService = $remajaService;
        $this->bumilService  = $bumilService;
        $this->lansiaService = $lansiaService;
    }

    /**
     * INDEX: Ruang Tunggu Validasi Bidan (Triase Meja 5)
     */
    public function index(Request $request)
    {
        try {
            $tab = $request->get('tab', 'pending');
            $search = $request->get('search');

            $query = Pemeriksaan::with(['kunjungan.pasien', 'pemeriksa'])->latest();

            if ($tab === 'verified') {
                $query->where('status_verifikasi', 'verified');
            } else {
                $query->where('status_verifikasi', 'pending');
                $tab = 'pending';
            }

            if ($search) {
                $query->whereHas('kunjungan.pasien', function($q) use ($search) {
                    $q->where('nama_lengkap', 'like', "%{$search}%")
                      ->orWhere('nik', 'like', "%{$search}%");
                });
            }

            $pemeriksaans = $query->paginate(15)->withQueryString();
            $pendingCount = Pemeriksaan::where('status_verifikasi', 'pending')->count();

            return view('bidan.pemeriksaan.index', compact('pemeriksaans', 'tab', 'pendingCount'));

        } catch (\Exception $e) {
            Log::error('BIDAN_INDEX_ERROR: ' . $e->getMessage());
            return back()->with('error', 'Gagal memuat ruang tunggu pemeriksaan.');
        }
    }

    /**
     * VALIDASI: Halaman Tinjauan Medis sebelum Disahkan oleh Bidan
     * Di sinilah Otak AI Medis bekerja memberikan rekomendasi otomatis ke Bidan
     */
    public function validasi($id)
    {
        try {
            // Tarik data pemeriksaan beserta data pasien polimorfik lewat kunjungan
            $pemeriksaan = Pemeriksaan::with(['kunjungan.pasien', 'pemeriksa'])->findOrFail($id);
            $pasien = $pemeriksaan->kunjungan->pasien ?? null;

            if (!$pasien) {
                return redirect()->route('bidan.pemeriksaan.index')->with('error', 'Biodata pasien tidak ditemukan.');
            }

            $analisis = null;
            $kategori = strtolower($pemeriksaan->kategori_pasien);

            // PROSES ANALISIS OTOMATIS BERDASARKAN KATEGORI DEMOGRAFI PASIEN
            if ($kategori === 'balita') {
                $usiaBulan = $pasien->usia_bulan ?? 0;
                $jk = $pasien->jenis_kelamin ?? 'L';
                $analisis = $this->balitaService->analisisKomprehensif(
                    $usiaBulan, 
                    $jk, 
                    (float)$pemeriksaan->berat_badan, 
                    (float)$pemeriksaan->tinggi_badan
                );
            } elseif ($kategori === 'remaja') {
                $usiaTahun = $pasien->usia_tahun ?? 0;
                $jk = $pasien->jenis_kelamin ?? 'L';
                
                // Mencegah error pembagian jika tinggi badan 0
                $tb = (float)($pemeriksaan->tinggi_badan ?? 0);
                $bb = (float)($pemeriksaan->berat_badan ?? 0);
                $imtValue = (float)($pemeriksaan->imt ?? 0);
                
                if ($imtValue <= 0 && $tb > 0) {
                    $imtValue = $bb / (($tb / 100) ** 2);
                }
                
                $analisis = $this->remajaService->analisisIMT($usiaTahun, $jk, $imtValue);
            } elseif (str_contains($kategori, 'hamil') || $kategori === 'ibu_hamil') {
                $analisis = $this->bumilService->analisisKomprehensif($pemeriksaan);
            } elseif ($kategori === 'lansia') {
                $jk = $pasien->jenis_kelamin ?? 'L';
                $analisis = $this->lansiaService->analisisKomprehensif($pemeriksaan, $jk);
            }

            return view('bidan.pemeriksaan.validasi', compact('pemeriksaan', 'pasien', 'analisis'));

        } catch (\Exception $e) {
            Log::error('BIDAN_SHOW_VALIDASI_ERROR: ' . $e->getMessage());
            return redirect()->route('bidan.pemeriksaan.index')->with('error', 'Gagal memuat panel tinjauan medis.');
        }
    }

    /**
     * SIMPAN VALIDASI: Menyimpan verifikasi resmi dari Bidan beserta diagnosa akhir
     */
    public function simpanValidasi(Request $request, $id)
    {
        $request->validate([
            'status_gizi'   => 'nullable|string|max:100',
            'diagnosa'      => 'required|string|max:255',
            'tindakan'      => 'nullable|string|max:255',
            'catatan_edukasi'=> 'nullable|string',
        ], [
            'diagnosa.required' => 'Kolom Kesimpulan Diagnosa Medis wajib diisi oleh Bidan.'
        ]);

        DB::beginTransaction();
        try {
            $pemeriksaan = Pemeriksaan::findOrFail($id);

            // Hitung IMT otomatis untuk kategori remaja/lansia/bumil jika belum terisi
            $imt = $pemeriksaan->imt;
            if (empty($imt) && $pemeriksaan->berat_badan > 0 && $pemeriksaan->tinggi_badan > 0) {
                $imt = round($pemeriksaan->berat_badan / (($pemeriksaan->tinggi_badan / 100) ** 2), 2);
            }

            // Update status rekam medis menjadi Terverifikasi (Verified)
            $pemeriksaan->update([
                'imt'               => $imt,
                'status_gizi'       => $request->status_gizi ?? $pemeriksaan->status_gizi,
                'diagnosa'          => $request->diagnosa,
                'tindakan'          => $request->tindakan ?? '-',
                'edukasi'           => $request->catatan_edukasi ?? '-',
                'status_verifikasi' => 'verified',
                'user_id_verifikator' => Auth::id(), // ID Bidan yang mengesahkan
                'tanggal_periksa'   => now(),
            ]);

            DB::commit();
            return redirect()->route('bidan.pemeriksaan.index', ['tab' => 'verified'])
                             ->with('success', 'Rekam medis berhasil disahkan dan diterbitkan ke Buku Kesehatan Digital warga.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('BIDAN_SIMPAN_VALIDASI_ERROR: ' . $e->getMessage());
            return back()->with('error', 'Gagal mengesahkan data: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * SHOW: Melihat detail pemeriksaan yang sudah verified
     */
    public function show($id)
    {
        try {
            $pemeriksaan = Pemeriksaan::with(['kunjungan.pasien', 'pemeriksa', 'verifikator'])->findOrFail($id);
            $pasien = $pemeriksaan->kunjungan->pasien ?? null;
            return view('bidan.pemeriksaan.show', compact('pemeriksaan', 'pasien'));
        } catch (\Exception $e) {
            return redirect()->route('bidan.pemeriksaan.index')->with('error', 'Data tidak ditemukan.');
        }
    }

    /**
     * DESTROY: Menghapus data antrean (Darurat/Salah Input)
     */
    public function destroy($id)
    {
        try {
            $pem = Pemeriksaan::findOrFail($id);
            $pem->delete();
            return back()->with('success', 'Data antrean pemeriksaan berhasil dihapus dari sistem.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus data.');
        }
    }
}