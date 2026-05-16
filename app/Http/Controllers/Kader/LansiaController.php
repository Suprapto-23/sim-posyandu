<?php

namespace App\Http\Controllers\Kader;

use App\Http\Controllers\Controller;
use App\Models\Lansia;
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
 * LANSIA CONTROLLER (NEXUS ENGINE UPGRADED)
 * =========================================================================
 * Menangani manajemen data Lansia (Warna Tema: Emerald/Teal).
 * Dilengkapi kalkulasi IMT Backend, Proteksi Hapus, dan Sinkronisasi Cerdas.
 */
class LansiaController extends Controller
{
    use SyncsUserAccount;
    /**
     * 1. INDEX: Menampilkan Direktori Lansia
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        
        $query = Lansia::query()->latest('created_at');

        // Pencarian Cerdas (Fuzzy Search)
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama_lengkap', 'LIKE', "%{$search}%")
                  ->orWhere('nik', 'LIKE', "%{$search}%")
                  ->orWhere('kode_lansia', 'LIKE', "%{$search}%");
            });
        }

        $lansias = $query->paginate(15)->withQueryString();

        // Statistik Instan untuk UI Dashboard Index
        $stats = [
            'total'     => Lansia::count(),
            'laki_laki' => Lansia::where('jenis_kelamin', 'L')->count(),
            'perempuan' => Lansia::where('jenis_kelamin', 'P')->count(),
        ];
            
        return view('kader.data.lansia.index', compact('lansias', 'search', 'stats'));
    }

    /**
     * 2. CREATE: Tampilkan Form Pendaftaran
     */
    public function create()
    {
        return view('kader.data.lansia.create');
    }

    /**
     * 3. STORE: Registrasi Baru dengan Kalkulasi IMT Otomatis
     * FIXED: Hanya menggunakan kolom yang ADA di database lansias
     * Kolom yang dihapus: 'telepon_keluarga', 'kemandirian', 'golongan_darah'
     */
    public function store(Request $request)
    {
        $request->validate([
            'nik'              => 'nullable|digits:16|unique:lansias,nik',
            'nama_lengkap'     => 'required|string|max:191',
            'tempat_lahir'     => 'required|string|max:100',
            'tanggal_lahir'    => 'required|date|before:-45 years', 
            'jenis_kelamin'    => 'required|in:L,P',
            'penyakit_bawaan'  => 'nullable|string',
            'berat_badan'      => 'nullable|numeric|min:1|max:300',
            'tinggi_badan'     => 'nullable|numeric|min:50|max:250',
            'alamat'           => 'required|string',
            // HAPUS: 'kemandirian' dan 'telepon_keluarga' karena tidak ada di database
        ], [
            'nik.unique'           => 'Peringatan: NIK ini sudah terdaftar sebagai lansia.',
            'nik.digits'           => 'NIK harus terdiri dari 16 digit angka.',
            'tanggal_lahir.before' => 'Kategori Lansia/Pra-Lansia minimal harus berusia 45 tahun.',
        ]);

        DB::beginTransaction();
        try {
            $kode_lansia = 'LNS-' . date('Ymd') . '-' . strtoupper(Str::random(4));
            $linkedUser = $this->findLinkedUser($request->nik, $request->nama_lengkap);

            // Hitung IMT secara presisi di server
            $imt = null;
            if ($request->berat_badan && $request->tinggi_badan && $request->tinggi_badan > 0) {
                $tinggiM = $request->tinggi_badan / 100;
                $imt = round($request->berat_badan / ($tinggiM * $tinggiM), 2);
            }

            Lansia::create([
                'kode_lansia'      => $kode_lansia,
                'user_id'          => $linkedUser ? $linkedUser->id : null,
                'nik'              => $request->nik,
                'nama_lengkap'     => $request->nama_lengkap,
                'tempat_lahir'     => $request->tempat_lahir,
                'tanggal_lahir'    => $request->tanggal_lahir,
                'jenis_kelamin'    => $request->jenis_kelamin,
                'alamat'           => $request->alamat,
                'penyakit_bawaan'  => $request->penyakit_bawaan,
                'berat_badan'      => $request->berat_badan,
                'tinggi_badan'     => $request->tinggi_badan,
                'imt'              => $imt,
                // HAPUS: 'kemandirian' => $request->kemandirian,
                // HAPUS: 'telepon_keluarga' => $request->telepon_keluarga,
                // HAPUS: 'golongan_darah' => $request->golongan_darah,
                'created_by'       => Auth::id(),
            ]);

            DB::commit();
            return redirect()->route('kader.data.lansia.index')
                ->with('success', 'Registrasi Selesai! Data Lansia berhasil ditambahkan ke direktori.');
                
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('KADER_LANSIA_STORE_ERROR: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Kegagalan Sistem: ' . $e->getMessage());
        }
    }

    /**
     * 4. SHOW: Buku Pemantauan Lansia
     */
    public function show($id)
    {
        $lansia = Lansia::with([
            'user', 
            'kunjungans' => function($q) { $q->latest('tanggal_kunjungan'); },
            'kunjungans.pemeriksaan'
        ])->findOrFail($id);

        return view('kader.data.lansia.show', compact('lansia'));
    }

    /**
     * 5. EDIT: Form Koreksi Data
     */
    public function edit($id)
    {
        $lansia = Lansia::findOrFail($id);
        return view('kader.data.lansia.edit', compact('lansia'));
    }

    /**
     * 6. UPDATE: Pembaruan Data
     */
    public function update(Request $request, $id)
    {
        $lansia = Lansia::findOrFail($id);

        $request->validate([
            'nik'              => 'nullable|digits:16|unique:lansias,nik,' . $lansia->id,
            'nama_lengkap'     => 'required|string|max:191',
            'tempat_lahir'     => 'required|string|max:100',
            'tanggal_lahir'    => 'required|date|before:-45 years',
            'jenis_kelamin'    => 'required|in:L,P',
            'penyakit_bawaan'  => 'nullable|string',
            'berat_badan'      => 'nullable|numeric|min:1|max:300',
            'tinggi_badan'     => 'nullable|numeric|min:50|max:250',
            'alamat'           => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $linkedUser = $this->findLinkedUser($request->nik, $request->nama_lengkap);

            // Hitung ulang IMT
            $imt = $lansia->imt;
            if ($request->berat_badan && $request->tinggi_badan && $request->tinggi_badan > 0) {
                $tinggiM = $request->tinggi_badan / 100;
                $imt = round($request->berat_badan / ($tinggiM * $tinggiM), 2);
            }

            $lansia->update([
                'user_id'          => $linkedUser ? $linkedUser->id : $lansia->user_id,
                'nik'              => $request->nik,
                'nama_lengkap'     => $request->nama_lengkap,
                'tempat_lahir'     => $request->tempat_lahir,
                'tanggal_lahir'    => $request->tanggal_lahir,
                'jenis_kelamin'    => $request->jenis_kelamin,
                'alamat'           => $request->alamat,
                'penyakit_bawaan'  => $request->penyakit_bawaan,
                'berat_badan'      => $request->berat_badan,
                'tinggi_badan'     => $request->tinggi_badan,
                'imt'              => $imt,
            ]);

            DB::commit();
            return redirect()->route('kader.data.lansia.show', $lansia->id)
                ->with('success', 'Koreksi Berhasil! Data Lansia telah diperbarui.');
                
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('KADER_LANSIA_UPDATE_ERROR: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }

    /**
     * 7. DESTROY: Proteksi Kunci Mutlak
     */
    public function destroy($id)
    {
        try {
            $lansia = Lansia::findOrFail($id);
            
            if ($lansia->kunjungans()->count() > 0) {
                return back()->with('error', 'Ditolak! Lansia ini sudah memiliki riwayat pemantauan medis. Data tidak boleh dihapus.');
            }

            $nama = $lansia->nama_lengkap;
            $lansia->delete();
            return redirect()->route('kader.data.lansia.index')->with('success', "Arsip dihapus. Data atas nama {$nama} berhasil dihilangkan.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Sistem gagal menghapus data.');
        }
    }

    /**
     * 8. BULK DELETE: Pembersihan Masal Berproteksi
     */
    public function bulkDelete(Request $request)
    {
        $ids = $request->ids;
        if (!$ids || count($ids) == 0) {
            return redirect()->back()->with('error', 'Tidak ada data yang dicentang untuk dihapus!');
        }
        
        $terpakai = Lansia::whereIn('id', $ids)->has('kunjungans')->count();
        if ($terpakai > 0) {
            return back()->with('error', "Operasi Dibatalkan! {$terpakai} Lansia yang Anda pilih sudah memiliki jejak rekam medis.");
        }

        try {
            Lansia::whereIn('id', $ids)->delete();
            return redirect()->route('kader.data.lansia.index')->with('success', count($ids) . ' Data lansia berhasil dibersihkan secara masal.');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Sistem gagal menghapus masal data tersebut.');
        }
    }

    /**
     * 9. SYNC USER: Tarik Akun Warga via UI
     */
    public function syncUser($id)
    {
        $lansia = Lansia::findOrFail($id);
        $user = $this->findLinkedUser($lansia->nik, $lansia->nama_lengkap);
        
        if ($user) {
            $lansia->update(['user_id' => $user->id]);
            return redirect()->back()->with('success', 'Akun Lansia berhasil dihubungkan dengan pengguna: ' . $user->name);
        }
        return redirect()->back()->with('error', 'Tidak ditemukan pengguna dengan NIK tersebut.');
    }
}