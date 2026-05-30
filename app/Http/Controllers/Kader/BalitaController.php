<?php

namespace App\Http\Controllers\Kader;

use App\Http\Controllers\Controller;
use App\Models\Balita;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class BalitaController extends Controller
{
    /**
     * =========================================================================
     * 1. HALAMAN DAFTAR DATA BALITA
     * =========================================================================
     * Dibuat lebih ringan:
     * - Ambil kolom seperlunya saja.
     * - Statistik dibuat aman.
     * - Relasi user dan pemeriksaan terakhir tetap ada kalau kolom mendukung.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->get('search', ''));
        $statusAkun = $request->get('status_akun', 'semua');

        if (!in_array($statusAkun, ['semua', 'terhubung', 'belum'], true)) {
            $statusAkun = 'semua';
        }

        $hasUserId = $this->balitaHasUserIdColumn();

        /*
         * Kalau kolom user_id belum ada, jangan paksa filter akun.
         * Kalau dipaksa, MySQL bakal ngamuk: unknown column user_id.
         */
        if (!$hasUserId) {
            $statusAkun = 'semua';
        }

        $baseQuery = Balita::query();

        $statTotal = (clone $baseQuery)->count();

        $statTerhubung = $hasUserId
            ? (clone $baseQuery)->whereNotNull('user_id')->count()
            : 0;

        $statBelumTerhubung = $hasUserId
            ? (clone $baseQuery)->whereNull('user_id')->count()
            : $statTotal;

        $statBulanIni = (clone $baseQuery)
            ->whereMonth('created_at', now('Asia/Jakarta')->month)
            ->whereYear('created_at', now('Asia/Jakarta')->year)
            ->count();

        $selectColumns = [
            'id',
            'kode_balita',
            'nik',
            'nama_lengkap',
            'jenis_kelamin',
            'tempat_lahir',
            'tanggal_lahir',
            'nama_ibu',
            'nama_ayah',
            'alamat',
            'berat_lahir',
            'panjang_lahir',
            'created_by',
            'created_at',
            'updated_at',
        ];

        if ($hasUserId) {
            $selectColumns[] = 'user_id';
        }

        $query = Balita::query()
            ->select($selectColumns)
            ->when($hasUserId, function ($q) {
                $q->with([
                    'user:id,name,nik,email,role,status',
                    'pemeriksaan_terakhir',
                ]);
            })
            ->when(!$hasUserId, function ($q) {
                $q->with([
                    'pemeriksaan_terakhir',
                ]);
            })
            ->latest('id');

        if ($hasUserId && $statusAkun === 'terhubung') {
            $query->whereNotNull('user_id');
        }

        if ($hasUserId && $statusAkun === 'belum') {
            $query->whereNull('user_id');
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%")
                    ->orWhere('nama_ibu', 'like', "%{$search}%")
                    ->orWhere('nama_ayah', 'like', "%{$search}%")
                    ->orWhere('alamat', 'like', "%{$search}%");
            });
        }

        $items = $query->paginate(10)->withQueryString();

        return view('kader.data.balita.index', compact(
            'items',
            'search',
            'statusAkun',
            'statTotal',
            'statTerhubung',
            'statBelumTerhubung',
            'statBulanIni'
        ));
    }

    /**
     * =========================================================================
     * 2. HALAMAN TAMBAH DATA BALITA
     * =========================================================================
     */
    public function create(): View
    {
        return view('kader.data.balita.create');
    }

    /**
     * =========================================================================
     * 3. SIMPAN DATA BALITA
     * =========================================================================
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'nik' => 'required|numeric|digits:16|unique:balitas,nik',
            'jenis_kelamin' => 'required|in:L,P',
            'tempat_lahir' => 'required|string|max:100',
            'tanggal_lahir' => 'required|date|before_or_equal:today',
            'nama_ibu' => 'required|string|max:255',
            'nama_ayah' => 'nullable|string|max:255',
            'alamat' => 'required|string',
            'berat_lahir' => 'nullable|numeric|min:0',
            'panjang_lahir' => 'nullable|numeric|min:0',
        ], [
            'nama_lengkap.required' => 'Nama lengkap Balita wajib diisi.',
            'nik.required' => 'NIK Balita wajib diisi sebagai kunci data.',
            'nik.numeric' => 'NIK hanya boleh berisi angka.',
            'nik.digits' => 'NIK harus berisi tepat 16 digit angka.',
            'nik.unique' => 'NIK Balita ini sudah terdaftar.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
            'tanggal_lahir.before_or_equal' => 'Tanggal lahir tidak boleh melebihi hari ini.',
            'nama_ibu.required' => 'Nama ibu wajib diisi.',
            'alamat.required' => 'Alamat wajib diisi.',
        ]);

        $tanggalLahir = Carbon::parse($request->tanggal_lahir);
        $usiaBulan = $tanggalLahir->diffInMonths(now());

        if ($usiaBulan >= 60) {
            $tahun = floor($usiaBulan / 12);
            $bulan = $usiaBulan % 12;
            $teksUsia = $bulan > 0 ? "{$tahun} Tahun {$bulan} Bulan" : "{$tahun} Tahun";

            return back()
                ->withInput()
                ->with('error', "Registrasi ditolak. Usia anak terdeteksi {$teksUsia}. Modul Balita hanya menerima usia maksimal 59 bulan.");
        }

        DB::beginTransaction();

        try {
            $kode = $this->generateKodeBalita();

            $balita = Balita::create([
                'kode_balita' => $kode,
                'nik' => $request->nik,
                'nama_lengkap' => $request->nama_lengkap,
                'tempat_lahir' => $request->tempat_lahir,
                'tanggal_lahir' => $request->tanggal_lahir,
                'jenis_kelamin' => $request->jenis_kelamin,
                'nama_ibu' => $request->nama_ibu,
                'nama_ayah' => $request->nama_ayah,
                'alamat' => $request->alamat,
                'berat_lahir' => $request->berat_lahir,
                'panjang_lahir' => $request->panjang_lahir,
                'created_by' => Auth::id(),
            ]);

            $linkedUser = $this->findLinkedUser($request->nik);

            if ($linkedUser && $this->balitaHasUserIdColumn()) {
                $balita->user_id = $linkedUser->id;
                $balita->save();
            }

            DB::commit();

            if ($linkedUser && $this->balitaHasUserIdColumn()) {
                return redirect()
                    ->route('kader.data.balita.index')
                    ->with('success', 'Data Balita berhasil disimpan dan otomatis terhubung dengan akun warga.');
            }

            if ($linkedUser && !$this->balitaHasUserIdColumn()) {
                return redirect()
                    ->route('kader.data.balita.index')
                    ->with('warning', 'Data Balita berhasil disimpan. Akun warga ditemukan, tetapi kolom user_id belum tersedia pada tabel balitas.');
            }

            return redirect()
                ->route('kader.data.balita.index')
                ->with('warning', "Data Balita berhasil disimpan, tetapi belum ada akun warga dengan NIK {$request->nik}.");
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Gagal menyimpan data Balita', [
                'message' => $e->getMessage(),
                'nik' => $request->nik,
            ]);

            return back()
                ->withInput()
                ->with('error', 'Gagal menyimpan data Balita. Periksa kembali data atau struktur database.');
        }
    }

    /**
     * =========================================================================
     * 4. DETAIL DATA BALITA
     * =========================================================================
     */
    public function show($id): View
    {
        $query = Balita::query()
            ->with([
                'kunjungans' => function ($q) {
                    $q->with(['petugas', 'pemeriksaan'])
                        ->latest()
                        ->take(10);
                },
            ]);

        if ($this->balitaHasUserIdColumn()) {
            $query->with('user:id,name,nik,email,role,status');
        }

        $balita = $query->findOrFail($id);

        $tglLahir = Carbon::parse($balita->tanggal_lahir);
        $diff = $tglLahir->diff(now());

        $userTerhubung = null;

        if ($this->balitaHasUserIdColumn()) {
            $userTerhubung = $balita->user;
        }

        if (!$userTerhubung) {
            $userTerhubung = $this->findLinkedUser($balita->nik);
        }

        return view('kader.data.balita.show', [
            'balita' => $balita,
            'usia_tahun' => $diff->y,
            'usia_bulan' => $diff->m,
            'usia_hari' => $diff->d,
            'sisa_bulan' => $diff->m,
            'userTerhubung' => $userTerhubung,
        ]);
    }

    /**
     * =========================================================================
     * 5. HALAMAN EDIT DATA BALITA
     * =========================================================================
     */
    public function edit($id): View
    {
        $balita = Balita::findOrFail($id);

        return view('kader.data.balita.edit', compact('balita'));
    }

    /**
     * =========================================================================
     * 6. UPDATE DATA BALITA
     * =========================================================================
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $balita = Balita::findOrFail($id);

        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'nik' => 'required|numeric|digits:16|unique:balitas,nik,' . $id,
            'jenis_kelamin' => 'required|in:L,P',
            'tempat_lahir' => 'required|string|max:100',
            'tanggal_lahir' => 'required|date|before_or_equal:today',
            'nama_ibu' => 'required|string|max:255',
            'nama_ayah' => 'nullable|string|max:255',
            'alamat' => 'required|string',
            'berat_lahir' => 'nullable|numeric|min:0',
            'panjang_lahir' => 'nullable|numeric|min:0',
        ], [
            'nama_lengkap.required' => 'Nama lengkap Balita wajib diisi.',
            'nik.required' => 'NIK Balita wajib diisi.',
            'nik.numeric' => 'NIK hanya boleh berisi angka.',
            'nik.digits' => 'NIK harus berisi tepat 16 digit angka.',
            'nik.unique' => 'NIK Balita ini sudah digunakan data lain.',
            'tanggal_lahir.before_or_equal' => 'Tanggal lahir tidak boleh melebihi hari ini.',
        ]);

        $tanggalLahir = Carbon::parse($request->tanggal_lahir);
        $usiaBulan = $tanggalLahir->diffInMonths(now());

        if ($usiaBulan >= 60) {
            return back()
                ->withInput()
                ->with('error', 'Pembaruan ditolak. Usia Balita melewati batas layanan modul ini, yaitu maksimal 59 bulan.');
        }

        DB::beginTransaction();

        try {
            $balita->update([
                'nik' => $request->nik,
                'nama_lengkap' => $request->nama_lengkap,
                'tempat_lahir' => $request->tempat_lahir,
                'tanggal_lahir' => $request->tanggal_lahir,
                'jenis_kelamin' => $request->jenis_kelamin,
                'nama_ibu' => $request->nama_ibu,
                'nama_ayah' => $request->nama_ayah,
                'alamat' => $request->alamat,
                'berat_lahir' => $request->berat_lahir,
                'panjang_lahir' => $request->panjang_lahir,
            ]);

            $linkedUser = $this->findLinkedUser($request->nik);

            if ($this->balitaHasUserIdColumn()) {
                $balita->user_id = $linkedUser ? $linkedUser->id : null;
                $balita->save();
            }

            DB::commit();

            if ($linkedUser && $this->balitaHasUserIdColumn()) {
                return redirect()
                    ->route('kader.data.balita.index')
                    ->with('success', 'Data Balita berhasil diperbarui dan akun warga berhasil disinkronkan.');
            }

            if ($linkedUser && !$this->balitaHasUserIdColumn()) {
                return redirect()
                    ->route('kader.data.balita.index')
                    ->with('warning', 'Data Balita berhasil diperbarui. Akun warga ditemukan, tetapi kolom user_id belum tersedia pada tabel balitas.');
            }

            return redirect()
                ->route('kader.data.balita.index')
                ->with('warning', 'Data Balita berhasil diperbarui, tetapi belum terhubung dengan akun warga.');
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Gagal memperbarui data Balita', [
                'message' => $e->getMessage(),
                'balita_id' => $id,
            ]);

            return back()
                ->withInput()
                ->with('error', 'Gagal memperbarui data Balita. Periksa kembali data atau struktur database.');
        }
    }

    /**
     * =========================================================================
     * 7. HAPUS SATU DATA BALITA
     * =========================================================================
     */
    public function destroy($id): RedirectResponse
    {
        $balita = Balita::findOrFail($id);

        if ($balita->kunjungans()->exists()) {
            return back()
                ->with('error', 'Data tidak bisa dihapus karena Balita sudah memiliki riwayat kunjungan atau rekam medis.');
        }

        try {
            $balita->delete();

            return redirect()
                ->route('kader.data.balita.index')
                ->with('success', 'Data Balita berhasil dihapus.');
        } catch (\Throwable $e) {
            Log::error('Gagal menghapus data Balita', [
                'message' => $e->getMessage(),
                'balita_id' => $id,
            ]);

            return back()
                ->with('error', 'Gagal menghapus data Balita.');
        }
    }

    /**
     * =========================================================================
     * 8. HAPUS DATA BALITA SECARA MASSAL
     * =========================================================================
     */
    public function bulkDelete(Request $request): RedirectResponse
    {
        $ids = $request->input('ids', []);

        if (!is_array($ids) || count($ids) === 0) {
            return back()
                ->with('error', 'Tidak ada data Balita yang dipilih untuk dihapus.');
        }

        $anakAktif = Balita::whereIn('id', $ids)
            ->has('kunjungans')
            ->count();

        if ($anakAktif > 0) {
            return back()
                ->with('error', "{$anakAktif} data Balita tidak bisa dihapus karena sudah memiliki riwayat kunjungan atau rekam medis.");
        }

        DB::beginTransaction();

        try {
            $jumlah = Balita::whereIn('id', $ids)->delete();

            DB::commit();

            return back()
                ->with('success', "{$jumlah} data Balita berhasil dihapus.");
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Gagal menghapus massal data Balita', [
                'message' => $e->getMessage(),
                'ids' => $ids,
            ]);

            return back()
                ->with('error', 'Gagal menghapus data Balita secara massal.');
        }
    }

    /**
     * =========================================================================
     * 9. SINKRONISASI AKUN WARGA MANUAL
     * =========================================================================
     */
    public function syncUser($id): RedirectResponse
    {
        $balita = Balita::findOrFail($id);

        $user = $this->findLinkedUser($balita->nik);

        if (!$user) {
            return back()
                ->with('error', 'Akun warga dengan NIK Balita ini belum ditemukan.');
        }

        if (!$this->balitaHasUserIdColumn()) {
            return back()
                ->with('error', 'Akun warga ditemukan, tetapi kolom user_id belum tersedia pada tabel balitas. Tambahkan migration user_id dulu, bro. Database jangan diajak cosplay.');
        }

        $balita->user_id = $user->id;
        $balita->save();

        return back()
            ->with('success', 'Data Balita berhasil disinkronkan dengan akun warga.');
    }

    /**
     * =========================================================================
     * HELPER: CARI AKUN WARGA BERDASARKAN NIK
     * =========================================================================
     */
    private function findLinkedUser(?string $nik): ?User
    {
        $nik = trim((string) $nik);

        if ($nik === '') {
            return null;
        }

        if (!Schema::hasColumn('users', 'nik')) {
            return null;
        }

        return User::query()
            ->where('nik', $nik)
            ->when(Schema::hasColumn('users', 'role'), function ($query) {
                $query->where('role', 'user');
            })
            ->first();
    }

    /**
     * =========================================================================
     * HELPER: CEK KOLOM user_id DI TABEL balitas
     * =========================================================================
     */
    private function balitaHasUserIdColumn(): bool
    {
        return Schema::hasColumn('balitas', 'user_id');
    }

    /**
     * =========================================================================
     * HELPER: GENERATE KODE BALITA
     * =========================================================================
     */
    private function generateKodeBalita(): string
    {
        do {
            $kode = 'BLT-' . now('Asia/Jakarta')->format('ym') . '-' . str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (Balita::where('kode_balita', $kode)->exists());

        return $kode;
    }
}