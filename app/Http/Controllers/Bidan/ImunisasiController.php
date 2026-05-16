<?php

namespace App\Http\Controllers\Bidan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

// Memanggil Model yang relevan untuk Imunisasi KIA
use App\Models\Kunjungan;
use App\Models\Imunisasi;
use App\Models\Balita;
use App\Models\IbuHamil;

class ImunisasiController extends Controller
{
    /**
     * 1. INDEX: Buku Register Imunisasi (Nexus Style)
     */
    public function index(Request $request)
    {
        try {
            $search = $request->get('search');
            
            $query = Imunisasi::with(['kunjungan.pasien', 'kunjungan.petugas'])
                              ->latest('tanggal_imunisasi');
            
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('vaksin', 'like', "%{$search}%")
                      ->orWhereHas('kunjungan', function ($kunjunganQuery) use ($search) {
                          $kunjunganQuery->whereHasMorph('pasien', [Balita::class, IbuHamil::class], function ($pasienQuery) use ($search) {
                              $pasienQuery->where('nama_lengkap', 'like', "%{$search}%")
                                          ->orWhere('nik', 'like', "%{$search}%");
                          });
                      });
                });
            }
            
            $imunisasis = $query->paginate(15)->withQueryString();
            
            return view('bidan.imunisasi.index', compact('imunisasis', 'search'));

        } catch (\Exception $e) {
            Log::error('BIDAN_IMUNISASI_INDEX_ERROR: ' . $e->getMessage());
            abort(500, 'Gagal memuat register imunisasi.');
        }
    }

    /**
     * 2. CREATE: Form Imunisasi dengan Fitur Prefill (Smart Bridge)
     */
    public function create(Request $request)
    {
        // Menangkap data lemparan dari "Jembatan Cerdas" di Meja 5
        $prefill = [
            'pasien_id'   => $request->get('pasien_id'),
            'pasien_type' => $request->get('type'), // Berisi 'balita', 'bayi', atau 'ibu_hamil'
        ];

        // Optimasi: Hanya ambil kolom yang diperlukan untuk performa tinggi
        $balitas   = Balita::select('id', 'nama_lengkap', 'nik')->orderBy('nama_lengkap')->get();
        $ibuHamils = IbuHamil::select('id', 'nama_lengkap', 'nik')->orderBy('nama_lengkap')->get();
            
        return view('bidan.imunisasi.create', compact('balitas', 'ibuHamils', 'prefill'));
    }

    /**
     * 3. STORE: Simpan Tindakan Imunisasi ke EMR
     */
    public function store(Request $request)
    {
        $request->validate([
            'pasien_id'         => 'required',
            'pasien_type'       => 'required|string',
            'jenis_imunisasi'   => 'required|string',
            'vaksin'            => 'required|string',
            'dosis'             => 'required|integer|min:1',
            'tanggal_imunisasi' => 'required|date',
            'keterangan'        => 'nullable|string'
        ], [
            'dosis.required' => 'Urutan dosis vaksin wajib diisi.',
            'dosis.min'      => 'Dosis minimal adalah 1.',
        ]);

        DB::beginTransaction();
        try {
            // Cek atau buat kunjungan otomatis jika belum ada di hari yang sama
            $kunjungan = Kunjungan::firstOrCreate(
                [
                    'pasien_id'         => $request->pasien_id,
                    'pasien_type'       => ($request->pasien_type == 'ibu_hamil') ? 'App\Models\IbuHamil' : 'App\Models\Balita',
                    'tanggal_kunjungan' => $request->tanggal_imunisasi,
                ],
                [
                    'petugas_id'      => Auth::id(),
                    'jenis_kunjungan' => 'Imunisasi',
                    'waktu_kedatangan'=> Carbon::now()->format('H:i:s'),
                    'status'          => 'selesai'
                ]
            );

            Imunisasi::create([
                'kunjungan_id'      => $kunjungan->id,
                'jenis_imunisasi'   => $request->jenis_imunisasi,
                'vaksin'            => $request->vaksin,
                'dosis'             => $request->dosis,
                'tanggal_imunisasi' => $request->tanggal_imunisasi,
                'keterangan'        => $request->keterangan ?? '-',
            ]);

            DB::commit();
            return redirect()->route('bidan.imunisasi.index')
                             ->with('success', 'Tindakan imunisasi berhasil dicatat dalam rekam medis warga.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('BIDAN_IMUNISASI_STORE_ERROR: ' . $e->getMessage());
            return back()->with('error', 'Gagal menyimpan data imunisasi: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * 4. SHOW: Detail Riwayat (Read Only)
     */
    public function show($id)
    {
        $imunisasi = Imunisasi::with(['kunjungan.pasien', 'kunjungan.petugas'])->findOrFail($id);
        return view('bidan.imunisasi.show', compact('imunisasi'));
    }

    /**
     * 5. DESTROY: Hapus Data
     */
    public function destroy($id)
    {
        try {
            Imunisasi::findOrFail($id)->delete();
            return redirect()->route('bidan.imunisasi.index')->with('success', 'Data imunisasi telah dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus data.');
        }
    }
}