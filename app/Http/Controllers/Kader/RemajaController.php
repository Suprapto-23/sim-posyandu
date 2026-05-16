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
        $search = $request->get('search');
        $query = Remaja::query()->latest('created_at');

        // Pencarian Cerdas (Nama, NIK, Sekolah)
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama_lengkap', 'LIKE', "%{$search}%")
                  ->orWhere('nik', 'LIKE', "%{$search}%")
                  ->orWhere('sekolah', 'LIKE', "%{$search}%");
            });
        }

        $remajas = $query->paginate(15)->withQueryString();

        // Statistik untuk Dashboard
        $stats = [
            'total'     => Remaja::count(),
            'laki_laki' => Remaja::where('jenis_kelamin', 'L')->count(),
            'perempuan' => Remaja::where('jenis_kelamin', 'P')->count(),
        ];

        return view('kader.data.remaja.index', compact('remajas', 'search', 'stats'));
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
}