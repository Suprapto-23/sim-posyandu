<?php

namespace App\Http\Controllers\Kader;

use App\Http\Controllers\Controller;
use App\Models\Remaja;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RemajaController extends Controller
{
    public function index(Request $request): View
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

        $hasUserId = $this->hasColumn('user_id');
        $hasKelas = $this->hasColumn('kelas');

        if (!$hasUserId) {
            $statusAkun = 'semua';
        }

        $baseQuery = Remaja::query();

        $statTotal = (clone $baseQuery)->count();

        $statLaki = (clone $baseQuery)
            ->where('jenis_kelamin', 'L')
            ->count();

        $statPerempuan = (clone $baseQuery)
            ->where('jenis_kelamin', 'P')
            ->count();

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

        $selectColumns = $this->existingColumns([
            'id',
            'user_id',
            'kode_remaja',
            'nik',
            'nama_lengkap',
            'tempat_lahir',
            'tanggal_lahir',
            'jenis_kelamin',
            'sekolah',
            'kelas',
            'nama_ortu',
            'telepon_ortu',
            'alamat',
            'created_by',
            'created_at',
            'updated_at',
        ]);

        $query = Remaja::query()
            ->select($selectColumns)
            ->with(['pemeriksaan_terakhir'])
            ->when($hasUserId, function ($q) {
                $q->with(['user:id,name,nik,email,role,status']);
            })
            ->latest('id');

        if ($hasUserId && $statusAkun === 'terhubung') {
            $query->whereNotNull('user_id');
        }

        if ($hasUserId && $statusAkun === 'belum') {
            $query->whereNull('user_id');
        }

        if ($jenisKelamin !== 'semua') {
            $query->where('jenis_kelamin', $jenisKelamin);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search, $hasKelas) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%")
                    ->orWhere('sekolah', 'like', "%{$search}%")
                    ->orWhere('nama_ortu', 'like', "%{$search}%")
                    ->orWhere('telepon_ortu', 'like', "%{$search}%")
                    ->orWhere('alamat', 'like', "%{$search}%");

                if ($hasKelas) {
                    $q->orWhere('kelas', 'like', "%{$search}%");
                }
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

    public function create(): View
    {
        return view('kader.data.remaja.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $rules = [
            'nik' => 'required|numeric|digits:16|unique:remajas,nik',
            'nama_lengkap' => 'required|string|max:191',
            'tempat_lahir' => 'required|string|max:100',
            'tanggal_lahir' => 'required|date|before_or_equal:today',
            'jenis_kelamin' => 'required|in:L,P',
            'nama_ortu' => 'required|string|max:191',
            'alamat' => 'required|string',
            'sekolah' => 'nullable|string|max:191',
            'telepon_ortu' => 'nullable|string|max:20',
        ];

        if ($this->hasColumn('kelas')) {
            $rules['kelas'] = 'nullable|string|max:20';
        }

        $request->validate($rules, [
            'nik.required' => 'NIK Remaja wajib diisi.',
            'nik.numeric' => 'NIK hanya boleh berisi angka.',
            'nik.digits' => 'NIK harus berisi tepat 16 digit angka.',
            'nik.unique' => 'NIK ini sudah terdaftar sebagai peserta Remaja.',
            'nama_lengkap.required' => 'Nama lengkap Remaja wajib diisi.',
            'tempat_lahir.required' => 'Tempat lahir wajib diisi.',
            'tanggal_lahir.required' => 'Tanggal lahir wajib diisi.',
            'tanggal_lahir.before_or_equal' => 'Tanggal lahir tidak boleh melebihi hari ini.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
            'nama_ortu.required' => 'Nama orang tua/wali wajib diisi.',
            'alamat.required' => 'Alamat wajib diisi.',
        ]);

        DB::beginTransaction();

        try {
            $linkedUser = $this->findLinkedUser($request->nik);

            $data = [
                'kode_remaja' => $this->generateKodeRemaja(),
                'nik' => $request->nik,
                'nama_lengkap' => $request->nama_lengkap,
                'tempat_lahir' => $request->tempat_lahir,
                'tanggal_lahir' => $request->tanggal_lahir,
                'jenis_kelamin' => $request->jenis_kelamin,
                'sekolah' => $request->sekolah,
                'nama_ortu' => $request->nama_ortu,
                'telepon_ortu' => $request->telepon_ortu,
                'alamat' => $request->alamat,
                'created_by' => Auth::id(),
            ];

            if ($this->hasColumn('kelas')) {
                $data['kelas'] = $request->kelas;
            }

            if ($this->hasColumn('user_id')) {
                $data['user_id'] = $linkedUser ? $linkedUser->id : null;
            }

            Remaja::create($data);

            DB::commit();

            if ($linkedUser && $this->hasColumn('user_id')) {
                return redirect()
                    ->route('kader.data.remaja.index')
                    ->with('success', 'Data Remaja berhasil disimpan dan otomatis terhubung dengan akun warga.');
            }

            if ($linkedUser && !$this->hasColumn('user_id')) {
                return redirect()
                    ->route('kader.data.remaja.index')
                    ->with('warning', 'Data Remaja berhasil disimpan. Akun warga ditemukan, tetapi kolom user_id belum tersedia pada tabel remajas.');
            }

            return redirect()
                ->route('kader.data.remaja.index')
                ->with('warning', "Data Remaja berhasil disimpan, tetapi belum ada akun warga dengan NIK {$request->nik}.");
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Gagal menyimpan data Remaja', [
                'message' => $e->getMessage(),
                'nik' => $request->nik,
            ]);

            return back()
                ->withInput()
                ->with('error', 'Gagal menyimpan data Remaja. Periksa kembali data atau struktur database.');
        }
    }

    public function show($id): View
    {
        $query = Remaja::query()
            ->with([
                'kunjungans' => function ($q) {
                    $q->with(['petugas', 'pemeriksaan'])
                        ->latest('tanggal_kunjungan')
                        ->take(10);
                },
            ]);

        if ($this->hasColumn('user_id')) {
            $query->with(['user:id,name,nik,email,role,status']);
        }

        $remaja = $query->findOrFail($id);

        $userTerhubung = null;

        if ($this->hasColumn('user_id')) {
            $userTerhubung = $remaja->user;
        }

        if (!$userTerhubung) {
            $userTerhubung = $this->findLinkedUser($remaja->nik);
        }

        return view('kader.data.remaja.show', compact(
            'remaja',
            'userTerhubung'
        ));
    }

    public function edit($id): View
    {
        $remaja = Remaja::findOrFail($id);

        return view('kader.data.remaja.edit', compact('remaja'));
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $remaja = Remaja::findOrFail($id);

        $rules = [
            'nik' => 'required|numeric|digits:16|unique:remajas,nik,' . $remaja->id,
            'nama_lengkap' => 'required|string|max:191',
            'tempat_lahir' => 'required|string|max:100',
            'tanggal_lahir' => 'required|date|before_or_equal:today',
            'jenis_kelamin' => 'required|in:L,P',
            'nama_ortu' => 'required|string|max:191',
            'alamat' => 'required|string',
            'sekolah' => 'nullable|string|max:191',
            'telepon_ortu' => 'nullable|string|max:20',
        ];

        if ($this->hasColumn('kelas')) {
            $rules['kelas'] = 'nullable|string|max:20';
        }

        $request->validate($rules, [
            'nik.required' => 'NIK Remaja wajib diisi.',
            'nik.numeric' => 'NIK hanya boleh berisi angka.',
            'nik.digits' => 'NIK harus berisi tepat 16 digit angka.',
            'nik.unique' => 'NIK ini sudah digunakan oleh data Remaja lain.',
            'nama_lengkap.required' => 'Nama lengkap Remaja wajib diisi.',
            'tempat_lahir.required' => 'Tempat lahir wajib diisi.',
            'tanggal_lahir.required' => 'Tanggal lahir wajib diisi.',
            'tanggal_lahir.before_or_equal' => 'Tanggal lahir tidak boleh melebihi hari ini.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
            'nama_ortu.required' => 'Nama orang tua/wali wajib diisi.',
            'alamat.required' => 'Alamat wajib diisi.',
        ]);

        DB::beginTransaction();

        try {
            $linkedUser = $this->findLinkedUser($request->nik);

            $data = [
                'nik' => $request->nik,
                'nama_lengkap' => $request->nama_lengkap,
                'tempat_lahir' => $request->tempat_lahir,
                'tanggal_lahir' => $request->tanggal_lahir,
                'jenis_kelamin' => $request->jenis_kelamin,
                'sekolah' => $request->sekolah,
                'nama_ortu' => $request->nama_ortu,
                'telepon_ortu' => $request->telepon_ortu,
                'alamat' => $request->alamat,
            ];

            if ($this->hasColumn('kelas')) {
                $data['kelas'] = $request->kelas;
            }

            if ($this->hasColumn('user_id')) {
                $data['user_id'] = $linkedUser ? $linkedUser->id : null;
            }

            $remaja->update($data);

            DB::commit();

            if ($linkedUser && $this->hasColumn('user_id')) {
                return redirect()
                    ->route('kader.data.remaja.index')
                    ->with('success', 'Data Remaja berhasil diperbarui dan akun warga berhasil disinkronkan.');
            }

            if ($linkedUser && !$this->hasColumn('user_id')) {
                return redirect()
                    ->route('kader.data.remaja.index')
                    ->with('warning', 'Data Remaja berhasil diperbarui. Akun warga ditemukan, tetapi kolom user_id belum tersedia pada tabel remajas.');
            }

            return redirect()
                ->route('kader.data.remaja.index')
                ->with('warning', 'Data Remaja berhasil diperbarui, tetapi belum terhubung dengan akun warga.');
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Gagal memperbarui data Remaja', [
                'message' => $e->getMessage(),
                'remaja_id' => $remaja->id,
            ]);

            return back()
                ->withInput()
                ->with('error', 'Gagal memperbarui data Remaja. Periksa kembali data atau struktur database.');
        }
    }

    public function destroy($id): RedirectResponse
    {
        try {
            $remaja = Remaja::findOrFail($id);

            if ($remaja->kunjungans()->exists()) {
                return back()
                    ->with('error', 'Data tidak bisa dihapus karena Remaja sudah memiliki riwayat kunjungan atau rekam medis.');
            }

            $remaja->delete();

            return redirect()
                ->route('kader.data.remaja.index')
                ->with('success', 'Data Remaja berhasil dihapus.');
        } catch (\Throwable $e) {
            Log::error('Gagal menghapus data Remaja', [
                'message' => $e->getMessage(),
                'remaja_id' => $id,
            ]);

            return back()
                ->with('error', 'Gagal menghapus data Remaja.');
        }
    }

    public function bulkDelete(Request $request): RedirectResponse
    {
        $ids = $request->input('ids', []);

        if (!is_array($ids) || count($ids) === 0) {
            return back()
                ->with('error', 'Tidak ada data Remaja yang dipilih untuk dihapus.');
        }

        $terpakai = Remaja::whereIn('id', $ids)
            ->has('kunjungans')
            ->count();

        if ($terpakai > 0) {
            return back()
                ->with('error', "{$terpakai} data Remaja tidak bisa dihapus karena sudah memiliki riwayat kunjungan atau rekam medis.");
        }

        DB::beginTransaction();

        try {
            $jumlah = Remaja::whereIn('id', $ids)->delete();

            DB::commit();

            return redirect()
                ->route('kader.data.remaja.index')
                ->with('success', "{$jumlah} data Remaja berhasil dihapus.");
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Gagal menghapus massal data Remaja', [
                'message' => $e->getMessage(),
                'ids' => $ids,
            ]);

            return back()
                ->with('error', 'Gagal menghapus data Remaja secara massal.');
        }
    }

    public function syncUser($id): RedirectResponse
    {
        $remaja = Remaja::findOrFail($id);

        $user = $this->findLinkedUser($remaja->nik);

        if (!$user) {
            return back()
                ->with('error', 'Akun warga dengan NIK Remaja ini belum ditemukan.');
        }

        if (!$this->hasColumn('user_id')) {
            return back()
                ->with('error', 'Akun warga ditemukan, tetapi kolom user_id belum tersedia pada tabel remajas.');
        }

        $remaja->user_id = $user->id;
        $remaja->save();

        return back()
            ->with('success', 'Data Remaja berhasil disinkronkan dengan akun warga.');
    }

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

    private function hasColumn(string $column): bool
    {
        return Schema::hasColumn('remajas', $column);
    }

    private function existingColumns(array $columns): array
    {
        return array_values(array_filter($columns, function ($column) {
            return $this->hasColumn($column);
        }));
    }

    private function generateKodeRemaja(): string
    {
        do {
            $kode = 'RMJ-' . now('Asia/Jakarta')->format('ymd') . '-' . strtoupper(Str::random(4));
        } while (Remaja::where('kode_remaja', $kode)->exists());

        return $kode;
    }
}