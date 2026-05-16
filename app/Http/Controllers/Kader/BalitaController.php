<?php

namespace App\Http\Controllers\Kader;

use App\Http\Controllers\Controller;
use App\Models\Balita;
use App\Models\User;
use App\Traits\SyncsUserAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class BalitaController extends Controller
{
    use SyncsUserAccount;
    /**
     * =========================================================================
     * 1. MENAMPILKAN DATABASE (INDEX) DENGAN FILTER UMUR CERDAS
     * =========================================================================
     */
    public function index(Request $request)
    {
        $search = $request->get('search');

        $query = Balita::with('pemeriksaan_terakhir')->latest('created_at');

        // Pencarian Instan Server-Side
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%")
                  ->orWhere('nama_ibu', 'like', "%{$search}%");
            });
        }

        // PISAHKAN BERDASARKAN UMUR BULAN MENGGUNAKAN RAW SQL
        // 1. Bayi: 0 sampai 11 Bulan
        $bayis = (clone $query)->whereRaw('TIMESTAMPDIFF(MONTH, tanggal_lahir, CURDATE()) < 12')->get();
        
        // 2. Balita: 12 sampai 59 Bulan (Anak > 59 Bulan otomatis terfilter keluar)
        $balitas = (clone $query)->whereRaw('TIMESTAMPDIFF(MONTH, tanggal_lahir, CURDATE()) BETWEEN 12 AND 59')->get();

        return view('kader.data.balita.index', compact('bayis', 'balitas', 'search'));
    }

    /**
     * =========================================================================
     * 2. HALAMAN FORM REGISTRASI
     * =========================================================================
     */
    public function create()
    {
        return view('kader.data.balita.create');
    }

    /**
     * =========================================================================
     * 3. SIMPAN DATA REGISTRASI (DENGAN PROTEKSI UMUR & 1:1 NIK)
     * =========================================================================
     */
    public function store(Request $request)
    {
        // Validasi Aturan Form (NIK Ibu & Ayah dihapus dari kewajiban)
        $request->validate([
            'nama_lengkap'  => 'required|string|max:255',
            'nik'           => 'required|numeric|digits:16|unique:balitas,nik', // NIK Anak Wajib & Unik
            'jenis_kelamin' => 'required|in:L,P',
            'tempat_lahir'  => 'required|string|max:100',
            'tanggal_lahir' => 'required|date|before_or_equal:today',
            'nama_ibu'      => 'required|string|max:255',
            'nama_ayah'     => 'nullable|string|max:255',
            'alamat'        => 'required|string',
            'berat_lahir'   => 'nullable|numeric|min:0',
            'panjang_lahir' => 'nullable|numeric|min:0',
        ], [
            'nik.required'        => 'NIK Anak wajib diisi sebagai kunci sistem.',
            'nik.digits'          => 'NIK Anak harus berisi tepat 16 digit angka.',
            'nik.unique'          => 'Sistem mendeteksi NIK anak ini sudah terdaftar sebelumnya.',
            'tanggal_lahir.before_or_equal' => 'Tanggal lahir tidak boleh melebihi hari ini (masa depan).',
        ]);

        // 🔥 VALIDASI BACKEND STRICT: Blokir Anak Usia >= 60 Bulan
        $tanggalLahir = Carbon::parse($request->tanggal_lahir);
        $usiaBulan = $tanggalLahir->diffInMonths(now());
        
        if ($usiaBulan >= 60) {
            $tahun = floor($usiaBulan / 12);
            $bulan = $usiaBulan % 12;
            $teksUsia = $bulan > 0 ? "{$tahun} Tahun {$bulan} Bulan" : "{$tahun} Tahun";
            
            return back()->withInput()->with('error', "Registrasi Ditolak! Usia anak terdeteksi {$teksUsia}. Sistem membatasi pendaftaran modul ini maksimal 59 Bulan.");
        }

        DB::beginTransaction();
        try {
            // Generate Kode Unik
            $kode = 'BLT-' . date('ym') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

            $balita = Balita::create([
                'kode_balita'   => $kode,
                'nik'           => $request->nik,
                'nama_lengkap'  => $request->nama_lengkap,
                'tempat_lahir'  => $request->tempat_lahir,
                'tanggal_lahir' => $request->tanggal_lahir,
                'jenis_kelamin' => $request->jenis_kelamin,
                'nik_ibu'       => null, // Sengaja di-null-kan karena sudah pakai sistem 1:1
                'nama_ibu'      => $request->nama_ibu,
                'nik_ayah'      => null, 
                'nama_ayah'     => $request->nama_ayah,
                'alamat'        => $request->alamat,
                'berat_lahir'   => $request->berat_lahir,
                'panjang_lahir' => $request->panjang_lahir,
                'created_by'    => Auth::id(),
            ]);

            // Deteksi Akun Warga Otomatis (MENGGUNAKAN NIK BALITA SECARA LANGSUNG)
            $linkedUser = $this->findLinkedUser($request->nik, $request->nama_lengkap);
            if ($linkedUser) {
                $balita->user_id = $linkedUser->id;
                $balita->save(); 
            }

            DB::commit();
            
            if ($linkedUser) {
                return redirect()->route('kader.data.balita.index')
                    ->with('success', 'Registrasi Sukses! Data anak tersimpan dan otomatis terhubung dengan akun Login.');
            } else {
                return redirect()->route('kader.data.balita.index')
                    ->with('warning', "Registrasi Tersimpan! Namun belum ada akun pengguna dengan NIK {$request->nik} di sistem. Integrasi tertunda sampai akun dibuat oleh Admin.");
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Fatal Error Create Balita: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Kegagalan Server: Gagal menyimpan ke pangkalan data.');
        }
    }

    /**
     * =========================================================================
     * 4. BUKA BUKU KIA (DETAIL)
     * =========================================================================
     */
    public function show($id) 
    {
        $balita = Balita::with(['kunjungans' => function($q) {
                $q->with(['petugas', 'pemeriksaan'])->latest()->take(10);
            }, 'user'])->findOrFail($id);
        
        $tgl_lahir = Carbon::parse($balita->tanggal_lahir);
        $diff = $tgl_lahir->diff(now());
        
        $userTerhubung = $balita->user;
        if (!$userTerhubung) {
            // Lacak via NIK Balita
            $userTerhubung = $this->findLinkedUser($balita->nik, $balita->nama_lengkap);
        }

        return view('kader.data.balita.show', [
            'balita'        => $balita,
            'usia_tahun'    => $diff->y,
            'usia_bulan'    => $diff->m,
            'usia_hari'     => $diff->d,
            'sisa_bulan'    => $diff->m,
            'userTerhubung' => $userTerhubung
        ]);
    }

    /**
     * =========================================================================
     * 5. HALAMAN EDIT DATA
     * =========================================================================
     */
    public function edit($id)
    {
        $balita = Balita::findOrFail($id);
        return view('kader.data.balita.edit', compact('balita'));
    }

    /**
     * =========================================================================
     * 6. PROSES SIMPAN PERUBAHAN DATA
     * =========================================================================
     */
    public function update(Request $request, $id)
    {
        $balita = Balita::findOrFail($id);
            
        $request->validate([
            'nama_lengkap'  => 'required|string|max:255',
            'nik'           => 'required|numeric|digits:16|unique:balitas,nik,' . $id,
            'jenis_kelamin' => 'required|in:L,P',
            'tempat_lahir'  => 'required|string|max:100',
            'tanggal_lahir' => 'required|date|before_or_equal:today',
            'nama_ibu'      => 'required|string|max:255',
            'nama_ayah'     => 'nullable|string|max:255',
            'alamat'        => 'required|string',
            'berat_lahir'   => 'nullable|numeric|min:0',
            'panjang_lahir' => 'nullable|numeric|min:0',
        ]);

        $tanggalLahir = Carbon::parse($request->tanggal_lahir);
        $usiaBulan = $tanggalLahir->diffInMonths(now());
        
        if ($usiaBulan >= 60) {
            return back()->withInput()->with('error', "Pembaruan Ditolak! Anda mengatur tanggal lahir menjadi di atas 5 Tahun. Silakan mutasikan anak ini ke modul lain.");
        }

        DB::beginTransaction();
        try {
            $updateData = $request->except(['_token', '_method', 'user_id', 'nik_ibu', 'nik_ayah']);
            $balita->update($updateData);

            // Cek ulang sinkronisasi akun dengan NIK Balita yang baru diedit
            $linkedUser = $this->findLinkedUser($request->nik, $request->nama_lengkap);
            $balita->user_id = $linkedUser ? $linkedUser->id : null;
            $balita->save();

            DB::commit();

            if ($linkedUser) {
                return redirect()->route('kader.data.balita.index')->with('success', 'Pembaruan Berhasil! Data terkoreksi dan afirmasi akses login warga terhubung.');
            } else {
                return redirect()->route('kader.data.balita.index')->with('warning', 'Pembaruan Berhasil! Namun sinkronisasi akun Login terputus (NIK tidak ditemukan di sistem).');
            }
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Fatal Error Update Balita: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Kegagalan Server: Gagal memperbarui data.');
        }
    }

    /**
     * =========================================================================
     * 7. HAPUS SATU DATA
     * =========================================================================
     */
    public function destroy($id)
    {
        $balita = Balita::findOrFail($id);
        
        if ($balita->kunjungans()->count() > 0) {
            return back()->with('error', 'Tindakan Dilarang! Anak ini sudah memiliki rekam medis / riwayat kunjungan. Penghapusan akan merusak laporan Posyandu.');
        }
        
        $balita->delete();
        return redirect()->route('kader.data.balita.index')->with('success', 'Aksi Final: Data pendaftaran anak berhasil dihapus permanen.');
    }

    /**
     * =========================================================================
     * 8. HAPUS MASAL
     * =========================================================================
     */
    public function bulkDelete(Request $request)
    {
        $ids = $request->ids;
        if (!$ids || count($ids) == 0) {
            return redirect()->back()->with('error', 'Misi Dibatalkan: Tidak ada data yang dicentang untuk dihapus.');
        }

        $anakAktif = Balita::whereIn('id', $ids)->has('kunjungans')->count();
        
        if ($anakAktif > 0) {
            return redirect()->back()->with('error', "Tindakan Ditolak! $anakAktif dari anak yang Anda centang sudah memiliki jejak rekam medis. Hapus centang pada anak tersebut untuk melanjutkan.");
        }

        DB::beginTransaction();
        try {
            Balita::whereIn('id', $ids)->delete();
            DB::commit();
            return redirect()->back()->with('success', 'Pembersihan Sukses: ' . count($ids) . ' data registrasi berhasil dihapus massal.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Fatal Error Bulk Delete Balita: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Kegagalan Server: Terjadi konflik saat menghapus data.');
        }
    }

    /**
     * =========================================================================
     * 9. FITUR TRIGGER MANUAL UNTUK SINKRONISASI AKUN
     * =========================================================================
     */
    public function syncUser($id)
    {
        $balita = Balita::findOrFail($id);
        // Lacak via NIK Anak
        $user = $this->findLinkedUser($balita->nik, $balita->nama_lengkap);
        
        if ($user) {
            $balita->user_id = $user->id;
            $balita->save();
            return redirect()->back()->with('success', "Integrasi Terkunci! Akun anak berhasil dihubungkan dengan perangkat akses NIK tersebut.");
        }

        return redirect()->back()->with('error', 'Pencarian Gagal. Sistem tidak menemukan pengguna aplikasi dengan NIK Anak tersebut di database utama.');
    }
}