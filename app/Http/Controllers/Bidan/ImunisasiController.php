<?php

namespace App\Http\Controllers\Bidan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

// Memanggil Model yang relevan
use App\Models\Kunjungan;
use App\Models\Imunisasi;
use App\Models\Balita;
use App\Models\IbuHamil;

class ImunisasiController extends Controller
{
   /**
     * 1. INDEX: Buku Register Imunisasi (Server-Side Search & Pagination)
     */
    public function index(Request $request)
    {
        try {
            $search = $request->get('search');
            
            // Panggil relasi lengkap dari awal untuk mencegah N+1 Query
            $query = Imunisasi::with(['kunjungan.pasien', 'kunjungan.petugas'])
                              ->latest('tanggal_imunisasi');
            
            // Logika Pencarian Akurat
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('vaksin', 'like', "%{$search}%")
                      ->orWhereHas('kunjungan', function ($kunjunganQuery) use ($search) {
                          $kunjunganQuery->whereHasMorph('pasien', [\App\Models\Balita::class, \App\Models\IbuHamil::class], function ($pasienQuery) use ($search) {
                              $pasienQuery->where('nama_lengkap', 'like', "%{$search}%")
                                          ->orWhere('nik', 'like', "%{$search}%");
                          });
                      });
                });
            }
            
            // KUNCI PERBAIKAN: Gunakan paginate() BUKAN get()
            $imunisasis = $query->paginate(10)->withQueryString();
            
            return view('bidan.imunisasi.index', compact('imunisasis'));

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('BIDAN_IMUNISASI_INDEX_ERROR: ' . $e->getMessage());
            return back()->with('error', 'Gagal memuat data register imunisasi.');
        }
    }

    /**
     * 2. CREATE: Halaman Log Vaksinasi Baru (Injeksi Data Cerdas Tanpa API)
     */
    public function create()
    {
        // INJEKSI DATA: Ambil id, nama, dan nik saja agar ringan dimuat oleh browser
        // Ini menggantikan Fetch API eksternal yang rawan error di Alpine.js
        $masterData = [
            'balita'    => Balita::select('id', 'nama_lengkap as nama', 'nik')->get(),
            'ibu_hamil' => IbuHamil::select('id', 'nama_lengkap as nama', 'nik')->get(),
        ];

        return view('bidan.imunisasi.create', compact('masterData'));
    }

    /**
     * 3. STORE: Validasi & Simpan Log Vaksinasi
     */
    public function store(Request $request)
    {
        // Validasi input dasar
        $request->validate([
            'pasien_id'         => 'required|integer',
            'kategori'          => 'required|in:balita,ibu_hamil', // Standarisasi nama string biasa
            'jenis_imunisasi'   => 'required|string|max:100',
            'vaksin'            => 'required|string|max:100',
            'dosis'             => 'required|string|max:50',
            'tanggal_imunisasi' => 'required|date',
            'keterangan'        => 'nullable|string',
        ], [
            'pasien_id.required' => 'Identitas Pasien (Warga) wajib dipilih.',
            'kategori.in'        => 'Kategori pasien tidak valid.'
        ]);

        DB::beginTransaction();
        try {
            // KEAMANAN TINGKAT TINGGI: Translasi string ke Namespace Model
            // (Mencegah user iseng menginjeksi class lain dari inspect element browser)
            $modelMap = [
                'balita'    => \App\Models\Balita::class,
                'ibu_hamil' => \App\Models\IbuHamil::class,
            ];
            $pasienTypeClass = $modelMap[$request->kategori];

            // 1. Catat Kunjungan Posyandu
            // Cek apakah hari ini pasien tersebut sudah dicatat kunjungannya (misal sebelumnya diukur BB di Meja 2)
            // Jika belum ada, otomatis buat record Kunjungan baru.
            $kunjungan = Kunjungan::firstOrCreate(
                [
                    'pasien_id'         => $request->pasien_id,
                    'pasien_type'       => $pasienTypeClass,
                    'tanggal_kunjungan' => $request->tanggal_imunisasi,
                ],
                [
                    'kader_id' => Auth::id(), // ID Bidan yang mencatat langsung
                    'status'   => 'selesai'   // Tindakan imunisasi langsung selesai
                ]
            );

            // 2. Simpan Catatan Imunisasi / KIPI ke Buku Induk
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
                             ->with('success', 'Tindakan imunisasi berhasil dicatat dengan aman dalam rekam medis warga.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('BIDAN_IMUNISASI_STORE_ERROR: ' . $e->getMessage());
            return back()->with('error', 'Gagal menyimpan data imunisasi: ' . $e->getMessage())->withInput();
        }
    }

   /**
     * 4. SHOW: Detail Riwayat Imunisasi (Read Only)
     */
    public function show($id)
    {
        try {
            // Memanggil data imunisasi beserta relasi pasien dan bidan penanggung jawab
            $imunisasi = Imunisasi::with(['kunjungan.pasien', 'kunjungan.petugas'])->findOrFail($id);
            return view('bidan.imunisasi.show', compact('imunisasi'));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('SHOW_IMUNISASI_ERROR: ' . $e->getMessage());
            return redirect()->route('bidan.imunisasi.index')->with('error', 'Dokumen imunisasi tidak ditemukan atau sudah dihapus.');
        }
    }

    /**
     * 5. DESTROY: Hapus Data (Darurat / Salah Input)
     */
    public function destroy($id)
    {
        try {
            Imunisasi::findOrFail($id)->delete();
            return redirect()->route('bidan.imunisasi.index')->with('success', 'Catatan imunisasi telah dihapus dari sistem.');
        } catch (\Exception $e) {
            return back()->with('error', 'Data imunisasi gagal dihapus karena terikat sistem.');
        }
    }
}