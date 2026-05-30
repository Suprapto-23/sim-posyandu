<?php

namespace App\Http\Controllers\Kader;

use App\Http\Controllers\Controller;
use App\Models\Remaja;
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
 * REMAJA CONTROLLER (NEXUS ENGINE UPGRADED)
 * =========================================================================
 * Menangani manajemen data Remaja dengan arsitektur terpadu.
 * Fitur: Pencarian Cerdas, Proteksi Rekam Medis, & Auto-Sync Akun Warga.
 */
class RemajaController extends Controller
{
   use SyncsUserAccount;
    /**
     * 1. INDEX: Direktori Remaja & Statistik
     */
    public function index(Request $request)
{
    $search = trim((string) $request->get('search', ''));
    $statusAkun = $request->get('status_akun', 'semua');
    $jenisKelamin = $request->get('jenis_kelamin', 'semua');

    if (!in_array($statusAkun, ['semua', 'terhubung', 'belum'], true)) {
        $statusAkun = 'semua';
    }

    if (!in_array($jenisKelamin, ['semua', 'L', 'P'], true)) {
        $jenisKelamin = 'semua';
    }

    $baseQuery = Remaja::query()
        ->with(['pemeriksaan_terakhir', 'user']);

    $statTotal = (clone $baseQuery)->count();

    $statLaki = (clone $baseQuery)
        ->where('jenis_kelamin', 'L')
        ->count();

    $statPerempuan = (clone $baseQuery)
        ->where('jenis_kelamin', 'P')
        ->count();

    $statTerhubung = (clone $baseQuery)
        ->whereNotNull('user_id')
        ->count();

    $statBelumTerhubung = (clone $baseQuery)
        ->whereNull('user_id')
        ->count();

    $statBulanIni = (clone $baseQuery)
        ->whereMonth('created_at', now('Asia/Jakarta')->month)
        ->whereYear('created_at', now('Asia/Jakarta')->year)
        ->count();

    $query = Remaja::query()
        ->with(['pemeriksaan_terakhir', 'user'])
        ->latest('created_at');

    if ($statusAkun === 'terhubung') {
        $query->whereNotNull('user_id');
    }

    if ($statusAkun === 'belum') {
        $query->whereNull('user_id');
    }

    if ($jenisKelamin !== 'semua') {
        $query->where('jenis_kelamin', $jenisKelamin);
    }

    if ($search !== '') {
        $query->where(function ($q) use ($search) {
            $q->where('nama_lengkap', 'like', "%{$search}%")
                ->orWhere('nik', 'like', "%{$search}%")
                ->orWhere('sekolah', 'like', "%{$search}%")
                ->orWhere('kelas', 'like', "%{$search}%")
                ->orWhere('nama_ortu', 'like', "%{$search}%")
                ->orWhere('telepon_ortu', 'like', "%{$search}%")
                ->orWhere('alamat', 'like', "%{$search}%");
        });
    }

    $items = $query->paginate(10)->withQueryString();

    return view('kader.data.remaja.index', compact(
        'items',
        'search',
        'statusAkun',
        'jenisKelamin',
        'statTotal',
        'statLaki',
        'statPerempuan',
        'statTerhubung',
        'statBelumTerhubung',
        'statBulanIni'
    ));
}

    /**
     * 2. CREATE: Tampilkan Form Pendaftaran
     */
    public function create()
    {
        return view('kader.data.remaja.create');
    }

    /**
     * 3. STORE: Registrasi Baru dengan Auto-Sync
     */
    public function store(Request $request)
    {
        $request->validate([
            'nik'            => 'required|digits:16|unique:remajas,nik',
            'nama_lengkap'   => 'required|string|max:191',
            'tempat_lahir'   => 'required|string|max:100',
            'tanggal_lahir'  => 'required|date|before:today',
            'jenis_kelamin'  => 'required|in:L,P',
            'nama_ortu'      => 'required|string|max:191',
            'alamat'         => 'required|string',
            'sekolah'        => 'nullable|string|max:191',
            'kelas'          => 'nullable|string|max:20',
            'telepon_ortu'   => 'nullable|string|max:20',
        ], [
            'nik.unique' => 'NIK ini sudah terdaftar sebagai peserta Remaja.',
            'nik.digits' => 'Format NIK harus 16 digit angka.',
            'tanggal_lahir.before' => 'Tanggal lahir tidak valid.',
        ]);

        DB::beginTransaction();
        try {
            $kode_remaja = 'RMJ-' . date('Ymd') . '-' . strtoupper(Str::random(4));
            
            // Pencarian akun warga secara efisien
            $linkedUser = $this->findLinkedUser($request->nik, $request->nama_lengkap);

            Remaja::create([
                'kode_remaja'    => $kode_remaja,
                'user_id'        => $linkedUser ? $linkedUser->id : null,
                'nik'            => $request->nik,
                'nama_lengkap'   => $request->nama_lengkap,
                'tempat_lahir'   => $request->tempat_lahir,
                'tanggal_lahir'  => $request->tanggal_lahir,
                'jenis_kelamin'  => $request->jenis_kelamin,
                'sekolah'        => $request->sekolah,
                'kelas'          => $request->kelas,
                'nama_ortu'      => $request->nama_ortu,
                'telepon_ortu'   => $request->telepon_ortu,
                'alamat'         => $request->alamat,
                'created_by'     => Auth::id(),
            ]);

            DB::commit();
            return redirect()->route('kader.data.remaja.index')
                ->with('success', 'Registrasi Berhasil! Data Remaja telah disimpan.');
                
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('REMAJA_STORE_ERR: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    /**
     * 4. SHOW: Buku Medis Remaja
     */
    public function show($id)
    {
        // Menggunakan Eager Loading terpadu (Remaja -> Kunjungan -> Pemeriksaan)
        $remaja = Remaja::with([
            'user', 
            'kunjungans' => function($q) { 
                $q->with(['pemeriksaan'])->latest('tanggal_kunjungan'); 
            }
        ])->findOrFail($id);

        return view('kader.data.remaja.show', compact('remaja'));
    }

    /**
     * 5. EDIT: Form Koreksi Data
     */
    public function edit($id)
    {
        $remaja = Remaja::findOrFail($id);
        return view('kader.data.remaja.edit', compact('remaja'));
    }

    /**
     * 6. UPDATE: Sinkronisasi Ulang saat Edit
     */
    public function update(Request $request, $id)
    {
        $remaja = Remaja::findOrFail($id);

        $request->validate([
            'nik'          => 'required|digits:16|unique:remajas,nik,' . $remaja->id,
            'nama_lengkap' => 'required|string|max:191',
            'tempat_lahir' => 'required|string|max:100',
            'tanggal_lahir' => 'required|date|before:today',
            'jenis_kelamin' => 'required|in:L,P',
            'nama_ortu'    => 'required|string|max:191',
            'alamat'       => 'required|string',
            'sekolah'      => 'nullable|string|max:191',
            'kelas'        => 'nullable|string|max:20',
            'telepon_ortu' => 'nullable|string|max:20',
        ]);

        DB::beginTransaction();
        try {
            // Jika NIK berubah, cek apakah ada akun warga baru yang cocok
            $linkedUser = $this->findLinkedUser($request->nik, $request->nama_lengkap);

            $remaja->update([
                'user_id'        => $linkedUser ? $linkedUser->id : $remaja->user_id,
                'nik'            => $request->nik,
                'nama_lengkap'   => $request->nama_lengkap,
                'tempat_lahir'   => $request->tempat_lahir,
                'tanggal_lahir'  => $request->tanggal_lahir,
                'jenis_kelamin'  => $request->jenis_kelamin,
                'sekolah'        => $request->sekolah,
                'kelas'          => $request->kelas,
                'nama_ortu'      => $request->nama_ortu,
                'telepon_ortu'   => $request->telepon_ortu,
                'alamat'         => $request->alamat,
            ]);

            DB::commit();
            return redirect()->route('kader.data.remaja.show', $remaja->id)
                ->with('success', 'Koreksi Berhasil! Data profil telah diperbarui.');
                
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('REMAJA_UPDATE_ERR: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }

    /**
     * 7. DESTROY: Proteksi Hapus Mutlak
     */
    public function destroy($id)
    {
        try {
            $remaja = Remaja::findOrFail($id);
            
            // CEGAH HAPUS: Jika sudah pernah periksa, data master tidak boleh dihapus (menjaga integritas laporan)
            if ($remaja->kunjungans()->count() > 0) {
                return back()->with('error', 'Ditolak! Peserta ini sudah memiliki riwayat pemeriksaan medis.');
            }

            $remaja->delete();
            return redirect()->route('kader.data.remaja.index')->with('success', 'Arsip dihapus. Data telah dihilangkan dari sistem.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Terjadi kesalahan sistem saat menghapus data.');
        }
    }

    /**
     * 8. BULK DELETE: Pembersihan Masal Berproteksi
     */
    public function bulkDelete(Request $request)
    {
        $ids = $request->ids;
        if (!$ids || count($ids) == 0) {
            return back()->with('error', 'Misi Dibatalkan: Tidak ada data yang dipilih.');
        }

        // Hitung berapa data yang punya rekam medis dari daftar yang dicentang
        $terpakai = Remaja::whereIn('id', $ids)->has('kunjungans')->count();
        if ($terpakai > 0) {
            return back()->with('error', "Operasi Gagal! {$terpakai} data yang Anda pilih sudah memiliki jejak rekam medis.");
        }

        try {
            Remaja::whereIn('id', $ids)->delete();
            return redirect()->route('kader.data.remaja.index')->with('success', count($ids) . ' Data berhasil dibersihkan masal.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Sistem gagal mengeksekusi penghapusan masal.');
        }
    }

    /**
     * 9. SYNC USER: Tarik Akun Warga Secara Manual
     */
    public function syncUser($id)
    {
        $remaja = Remaja::findOrFail($id);
        $user = $this->findLinkedUser($remaja->nik, $remaja->nama_lengkap);
        
        if ($user) {
            $remaja->update(['user_id' => $user->id]);
            return back()->with('success', 'Akun berhasil terhubung dengan Pengguna: ' . $user->name);
        }

        return back()->with('error', 'Gagal! Akun Warga dengan NIK tersebut tidak ditemukan di sistem.');
    }
    private function findLinkedUser(?string $nik, ?string $nama = null): ?\App\Models\User
{
    $nik = trim((string) $nik);

    if ($nik === '') {
        return null;
    }

    $query = \App\Models\User::query();

    $query->where(function ($q) use ($nik) {
        $hasCondition = false;

        if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'nik')) {
            $q->orWhere('nik', $nik);
            $hasCondition = true;
        }

        if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'username')) {
            $q->orWhere('username', $nik);
            $hasCondition = true;
        }

        if (!$hasCondition) {
            $q->whereRaw('1 = 0');
        }
    });

    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'role')) {
        $query->whereIn('role', ['user', 'warga', 'masyarakat']);
    }

    return $query->first();
}
}