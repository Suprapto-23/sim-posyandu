<?php

namespace App\Http\Controllers\Kader;

use App\Http\Controllers\Controller;
use App\Models\IbuHamil;
use App\Models\User;
use App\Traits\SyncsUserAccount;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * =========================================================================
 * IBU HAMIL CONTROLLER (KADER WORKSPACE)
 * =========================================================================
 * Modul utama untuk manajemen data Ibu Hamil.
 * Menangani CRUD, Filter Trimester/Hampir Lahir, Bulk Delete, dan SPA Interactivity.
 */
class IbuHamilController extends Controller
{
    use SyncsUserAccount;
    /**
     * 1. INDEX: Menampilkan Direktori Ibu Hamil
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $filter = $request->get('filter', 'semua'); // Tabs: 'semua', 'aktif', 'hampir_lahir'

        // Base Query dengan Eager Loading untuk mencegah N+1 Query Problem
        $query = IbuHamil::query()->latest('created_at');

        // Fitur Pencarian Lanjutan (Fuzzy Search)
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama_lengkap', 'LIKE', "%{$search}%")
                  ->orWhere('nik', 'LIKE', "%{$search}%")
                  ->orWhere('nama_suami', 'LIKE', "%{$search}%")
                  ->orWhere('kode_bumil', 'LIKE', "%{$search}%");
            });
        }

        // Filter Berdasarkan Status Kandungan
        if ($filter === 'aktif') {
            $query->where('status', 'aktif');
        } elseif ($filter === 'hampir_lahir') {
            // Memanggil local scope di model IbuHamil (asumsi: ada fungsi scopeHampirLahir)
            // Jika tidak ada scope, bisa pakai whereRaw HPL mendekati
            if (method_exists(IbuHamil::class, 'scopeHampirLahir')) {
                $query->hampirLahir(30); // Prediksi 30 hari menuju HPL
            }
        }

        $ibuHamils = $query->paginate(15)->withQueryString();

        // Kalkulasi Statistik Instan untuk UI Dashboard
        $stats = [
            'total'        => IbuHamil::count(),
            'aktif'        => IbuHamil::where('status', 'aktif')->count(),
            'hampir_lahir' => method_exists(IbuHamil::class, 'scopeHampirLahir') 
                                ? IbuHamil::hampirLahir(30)->count() 
                                : 0,
        ];

        // Render khusus untuk request AJAX (Navigasi SPA Tanpa Reload)
        if ($request->ajax() || $request->wantsJson()) {
            return view('kader.data.ibu-hamil.index', compact('ibuHamils', 'search', 'filter', 'stats'))->render();
        }

        return view('kader.data.ibu-hamil.index', compact('ibuHamils', 'search', 'filter', 'stats'));
    }

    /**
     * 2. CREATE: Menampilkan Form Pendaftaran
     */
    public function create()
    {
        return view('kader.data.ibu-hamil.create');
    }

    /**
     * 3. STORE: Menyimpan Data Ibu Hamil Baru
     */
    public function store(Request $request)
{
    // PERBAIKAN 1: Ubah validasi HPL
    $request->validate([
        'nik'              => 'nullable|digits:16|unique:ibu_hamils,nik',
        'nama_lengkap'     => 'required|string|max:191',
        'tempat_lahir'     => 'required|string|max:100',
        'tanggal_lahir'    => 'required|date|before:today',
        'nama_suami'       => 'nullable|string|max:191',
        'telepon_ortu'     => 'nullable|string|max:20',  // ← Ganti dari 'telepon'
        'alamat'           => 'required|string',
        'hpht'             => 'nullable|date|before_or_equal:today',
        'hpl'              => 'nullable|date',  // ← HAPUS 'after:today'
        'status'           => 'required|in:aktif,selesai',  // ← Sesuaikan dengan DB
        'golongan_darah'   => 'nullable|string|max:3',
        'berat_badan'      => 'nullable|numeric|min:20|max:200',
        'tinggi_badan'     => 'nullable|numeric|min:50|max:250',
    ], [
        'nik.unique'       => 'Peringatan: NIK ibu ini sudah terdaftar di dalam database Bumil.',
        'nik.digits'       => 'NIK harus terdiri dari 16 digit angka yang valid.',
        'tanggal_lahir.before' => 'Tanggal lahir tidak valid.',
        'status.in'        => 'Status harus aktif atau selesai',  // ← Tambahkan
    ]);

    DB::beginTransaction();
    try {
        // Generate Kode Rekam Medis (perbaiki nama kolom)
        $kode_hamil = 'BML-' . date('Ymd') . '-' . strtoupper(Str::random(4));

        // Cari user terhubung
        $linkedUser = $this->findLinkedUser($request->nik, $request->nama_lengkap);

        // PERBAIKAN 2: Hitung IMT otomatis jika data tersedia
        $imt = null;
        if ($request->berat_badan && $request->tinggi_badan && $request->tinggi_badan > 0) {
            $tinggiM = $request->tinggi_badan / 100;
            $imt = round($request->berat_badan / ($tinggiM * $tinggiM), 2);
        }

        // PERBAIKAN 3: Masukkan SEMUA field ke dalam create
        IbuHamil::create([
            'kode_hamil'       => $kode_hamil,        // ← Ganti dari 'kode_bumil'
            'user_id'          => $linkedUser ? $linkedUser->id : null,
            'nik'              => $request->nik,
            'nama_lengkap'     => $request->nama_lengkap,
            'tempat_lahir'     => $request->tempat_lahir,
            'tanggal_lahir'    => $request->tanggal_lahir,
            'nama_suami'       => $request->nama_suami,
            'telepon_ortu'     => $request->telepon_ortu,  // ← Ganti dari 'telepon'
            'alamat'           => $request->alamat,
            'hpht'             => $request->hpht,
            'hpl'              => $request->hpl,
            'golongan_darah'   => $request->golongan_darah,  // ← TAMBAHKAN
            'riwayat_penyakit' => $request->riwayat_penyakit ?? null,
            'berat_badan'      => $request->berat_badan,      // ← TAMBAHKAN
            'tinggi_badan'     => $request->tinggi_badan,     // ← TAMBAHKAN
            'imt'              => $imt,                       // ← TAMBAHKAN
            'status'           => $request->status,
            'created_by'       => Auth::id(),
        ]);

        DB::commit();
        return redirect()->route('kader.data.ibu-hamil.index')
            ->with('success', 'Registrasi Selesai! Data Ibu Hamil berhasil ditambahkan ke Direktori.');
            
    } catch (\Throwable $e) {
        DB::rollBack();
        Log::error('KADER_BUMIL_STORE_ERROR: ' . $e->getMessage());
        
        // Tampilkan error spesifik untuk debugging
        return back()->withInput()->with('error', 'Kegagalan Sistem: ' . $e->getMessage());
    }
}
   /**
     * 4. SHOW: Detail Buku KIA Bumil
     */
    public function show($id)
    {
        // ARSITEKTUR TERPADU: Load IbuHamil -> Kunjungans -> Pemeriksaan
        $ibuHamil = IbuHamil::with([
            'user', 
            'kunjungans' => function($q) { 
                $q->with(['pemeriksaan'])->latest('tanggal_kunjungan')->take(10); 
            }
        ])->findOrFail($id);

        return view('kader.data.ibu-hamil.show', compact('ibuHamil'));
    }

    /**
     * 5. EDIT: Form Koreksi Data
     */
    public function edit($id)
    {
        $ibuHamil = IbuHamil::findOrFail($id);
        return view('kader.data.ibu-hamil.edit', compact('ibuHamil'));
    }

    /**
     * 6. UPDATE: Pembaruan Data
     */
    public function update(Request $request, $id)
{
    $ibuHamil = IbuHamil::findOrFail($id);

    $request->validate([
        'nik'              => 'nullable|digits:16|unique:ibu_hamils,nik,' . $ibuHamil->id,
        'nama_lengkap'     => 'required|string|max:191',
        'tempat_lahir'     => 'required|string|max:100',
        'tanggal_lahir'    => 'required|date|before:today',
        'nama_suami'       => 'nullable|string|max:191',
        'telepon_ortu'     => 'nullable|string|max:20',
        'alamat'           => 'required|string',
        'hpht'             => 'nullable|date|before_or_equal:today',
        'hpl'              => 'nullable|date',  // ← HAPUS after:today
        'status'           => 'required|in:aktif,selesai',
        'golongan_darah'   => 'nullable|string|max:3',
        'berat_badan'      => 'nullable|numeric',
        'tinggi_badan'     => 'nullable|numeric',
    ]);

    DB::beginTransaction();
    try {
        // Hitung ulang IMT
        $imt = null;
        if ($request->berat_badan && $request->tinggi_badan && $request->tinggi_badan > 0) {
            $tinggiM = $request->tinggi_badan / 100;
            $imt = round($request->berat_badan / ($tinggiM * $tinggiM), 2);
        }

        $linkedUser = $this->findLinkedUser($request->nik, $request->nama_lengkap);

        $ibuHamil->update([
            'user_id'          => $linkedUser ? $linkedUser->id : $ibuHamil->user_id,
            'nik'              => $request->nik,
            'nama_lengkap'     => $request->nama_lengkap,
            'tempat_lahir'     => $request->tempat_lahir,
            'tanggal_lahir'    => $request->tanggal_lahir,
            'nama_suami'       => $request->nama_suami,
            'telepon_ortu'     => $request->telepon_ortu,
            'alamat'           => $request->alamat,
            'hpht'             => $request->hpht,
            'hpl'              => $request->hpl,
            'golongan_darah'   => $request->golongan_darah,
            'riwayat_penyakit' => $request->riwayat_penyakit ?? null,
            'berat_badan'      => $request->berat_badan,
            'tinggi_badan'     => $request->tinggi_badan,
            'imt'              => $imt,
            'status'           => $request->status,
        ]);

        DB::commit();
        return redirect()->route('kader.data.ibu-hamil.show', $ibuHamil->id)
            ->with('success', 'Koreksi Disimpan! Data Ibu Hamil telah diperbarui.');
            
    } catch (\Throwable $e) {
        DB::rollBack();
        Log::error('KADER_BUMIL_UPDATE_ERROR: ' . $e->getMessage());
        return back()->withInput()->with('error', 'Kegagalan Sistem: ' . $e->getMessage());
    }
}

    /**
     * 7. DESTROY: Hapus Permanen 1 Data
     */
   public function destroy($id)
    {
        try {
            $ibuHamil = IbuHamil::findOrFail($id);
            $nama = $ibuHamil->nama_lengkap;
            
            // CEGAH HAPUS JIKA SUDAH ADA KUNJUNGAN
            if ($ibuHamil->kunjungans()->count() > 0) {
                return back()->with('error', 'Ditolak: Data ini sudah memiliki riwayat kunjungan/pemeriksaan.');
            }

            $ibuHamil->delete(); 
            return redirect()->route('kader.data.ibu-hamil.index')->with('success', "Arsip dihapus. Data atas nama Ibu {$nama} telah dihilangkan dari sistem.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menghapus data.');
        }
    }

    /**
     * 8. BULK DELETE: Hapus Banyak Data Sekaligus
     */
    public function bulkDelete(Request $request)
    {
        $ids = $request->ids;
        if (!$ids || count($ids) == 0) {
            return redirect()->back()->with('error', 'Misi Dibatalkan: Tidak ada data Ibu Hamil yang dicentang.');
        }

        // Proteksi: Jangan hapus jika ada yang sudah punya Kunjungan
        $bumilAktif = IbuHamil::whereIn('id', $ids)->has('kunjungans')->count();
        if ($bumilAktif > 0) {
            return redirect()->back()->with('error', "Tindakan Ditolak! {$bumilAktif} dari data yang dicentang sudah memiliki jejak rekam medis.");
        }

        try {
            IbuHamil::whereIn('id', $ids)->delete();
            return redirect()->route('kader.data.ibu-hamil.index')->with('success', 'Operasi Berhasil: ' . count($ids) . ' data telah dibersihkan secara masal.');
        } catch (\Throwable $e) {
            Log::error('KADER_BUMIL_BULK_ERROR: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Sistem Gagal menghapus data.');
        }
    }

    /**
     * 9. SYNC USER: Tarik Akun HP Warga Secara Manual
     */
    public function syncUser($id)
    {
        $ibuHamil = IbuHamil::findOrFail($id);
        $user = $this->findLinkedUser($ibuHamil->nik, $ibuHamil->nama_lengkap);
        
        if ($user) {
            $ibuHamil->user_id = $user->id;
            $ibuHamil->save();
            return redirect()->back()->with('success', 'Berhasil ditarik! Akun rekam medis ini sudah terhubung dengan HP milik Ibu (' . $user->name . ').');
        }

        return redirect()->back()->with('error', 'Gagal! Tidak ditemukan akun Warga dengan NIK tersebut. Pastikan Ibu sudah melakukan registrasi di portal Warga PosyanduCare.');
    }
}