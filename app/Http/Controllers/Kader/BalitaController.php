<?php

namespace App\Http\Controllers\Kader;

use App\Http\Controllers\Controller;
use App\Models\Balita;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class BalitaController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->get('search', ''));
        $statusAkun = $request->get('status_akun', 'semua');

        if (! in_array($statusAkun, ['semua', 'terhubung', 'belum'], true)) {
            $statusAkun = 'semua';
        }

        $hasUserId = $this->balitaHasUserIdColumn();
        $hasNikIbu = $this->balitaHasNikIbuColumn();

        if (! $hasUserId) {
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

        if ($hasNikIbu) {
            $selectColumns[] = 'nik_ibu';
        }

        $query = Balita::query()
            ->select($selectColumns)
            ->with(['pemeriksaan_terakhir'])
            ->when($hasUserId, function ($q) {
                $q->with('user:id,name,nik,email,role,status');
            });

        if ($hasUserId && $statusAkun === 'terhubung') {
            $query->whereNotNull('user_id');
        }

        if ($hasUserId && $statusAkun === 'belum') {
            $query->whereNull('user_id');
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                if (ctype_digit($search)) {
                    $q->where('nik', 'like', $search . '%')
                        ->orWhere('nik', 'like', '%' . $search . '%');

                    return;
                }

                $q->where('nama_lengkap', 'like', $search . '%')
                    ->orWhere('nama_lengkap', 'like', '%' . $search . '%');
            });

            if (ctype_digit($search)) {
                $query->orderByRaw(
                    'CASE WHEN nik LIKE ? THEN 0 WHEN nik LIKE ? THEN 1 ELSE 2 END',
                    [$search . '%', '%' . $search . '%']
                );
            } else {
                $query->orderByRaw(
                    'CASE WHEN nama_lengkap LIKE ? THEN 0 WHEN nama_lengkap LIKE ? THEN 1 ELSE 2 END',
                    [$search . '%', '%' . $search . '%']
                );
            }
        }

        $items = $query
            ->orderBy('nama_lengkap', 'asc')
            ->paginate(10)
            ->withQueryString();

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

    public function create(): View
    {
        return view('kader.data.balita.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'nik' => ['required', 'regex:/^[0-9]{16}$/', 'unique:balitas,nik'],
            'jenis_kelamin' => ['required', Rule::in(['L', 'P'])],
            'tempat_lahir' => ['required', 'string', 'max:100'],
            'tanggal_lahir' => ['required', 'date', 'before_or_equal:today'],
            'nama_ibu' => ['required', 'string', 'max:255'],
            'nik_ibu' => ['nullable', 'regex:/^[0-9]{16}$/'],
            'nama_ayah' => ['nullable', 'string', 'max:255'],
            'alamat' => ['required', 'string'],
            'berat_lahir' => ['nullable', 'numeric', 'min:0', 'max:20'],
            'panjang_lahir' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ], [
            'nama_lengkap.required' => 'Nama lengkap Balita wajib diisi.',
            'nik.required' => 'NIK Balita wajib diisi sebagai kunci data.',
            'nik.regex' => 'NIK Balita harus berisi tepat 16 digit angka.',
            'nik.unique' => 'NIK Balita ini sudah terdaftar.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
            'tanggal_lahir.before_or_equal' => 'Tanggal lahir tidak boleh melebihi hari ini.',
            'nama_ibu.required' => 'Nama ibu wajib diisi.',
            'nik_ibu.regex' => 'NIK ibu harus berisi tepat 16 digit angka.',
            'alamat.required' => 'Alamat wajib diisi.',
        ]);

        $tanggalLahir = Carbon::parse($validated['tanggal_lahir']);
        $usiaBulan = (int) $tanggalLahir->diffInMonths(now());

        if ($usiaBulan >= 60) {
            $tahun = intdiv($usiaBulan, 12);
            $bulan = $usiaBulan % 12;

            $teksUsia = $bulan > 0
                ? "{$tahun} tahun {$bulan} bulan"
                : "{$tahun} tahun";

            return back()
                ->withInput()
                ->with('error', "Registrasi ditolak. Usia anak terdeteksi {$teksUsia}. Modul Balita hanya menerima usia maksimal 59 bulan.");
        }

        DB::beginTransaction();

        try {
            $linkedUser = $this->findLinkedUser($validated['nik']);

            $data = [
                'kode_balita' => $this->generateKodeBalita(),
                'nik' => $validated['nik'],
                'nama_lengkap' => $validated['nama_lengkap'],
                'tempat_lahir' => $validated['tempat_lahir'],
                'tanggal_lahir' => $validated['tanggal_lahir'],
                'jenis_kelamin' => $validated['jenis_kelamin'],
                'nama_ibu' => $validated['nama_ibu'],
                'nama_ayah' => $validated['nama_ayah'] ?? null,
                'alamat' => $validated['alamat'],
                'berat_lahir' => $validated['berat_lahir'] ?? null,
                'panjang_lahir' => $validated['panjang_lahir'] ?? null,
                'created_by' => Auth::id(),
            ];

            if ($this->balitaHasNikIbuColumn()) {
                $data['nik_ibu'] = $validated['nik_ibu'] ?? null;
            }

            if ($this->balitaHasUserIdColumn()) {
                $data['user_id'] = $linkedUser?->id;
            }

            Balita::create($data);

            DB::commit();

            if ($linkedUser && $this->balitaHasUserIdColumn()) {
                return redirect()
                    ->route('kader.data.balita.index')
                    ->with('success', 'Data Balita berhasil disimpan dan otomatis terhubung dengan akun warga.');
            }

            return redirect()
                ->route('kader.data.balita.index')
                ->with('warning', "Data Balita berhasil disimpan, tetapi belum ada akun warga dengan NIK {$validated['nik']}.");
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Gagal menyimpan data Balita', [
                'message' => $e->getMessage(),
                'nik' => $validated['nik'] ?? null,
            ]);

            return back()
                ->withInput()
                ->with('error', 'Gagal menyimpan data Balita. Periksa kembali data atau struktur database.');
        }
    }

    public function show($id): View
    {
        $query = Balita::query()
            ->with([
                'kunjungans' => function ($q) {
                    $q->with(['petugas', 'pemeriksaan'])
                        ->latest()
                        ->take(10);
                },
                'pemeriksaans' => function ($q) {
                    $q->latest()
                        ->take(10);
                },
                'pemeriksaan_terakhir',
            ]);

        if ($this->balitaHasUserIdColumn()) {
            $query->with('user:id,name,nik,email,role,status');
        }

        $balita = $query->findOrFail($id);

        $tglLahir = Carbon::parse($balita->tanggal_lahir);
        $diff = $tglLahir->diff(now());

        $userTerhubung = $this->balitaHasUserIdColumn()
            ? $balita->user
            : null;

        if (! $userTerhubung) {
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

    public function edit($id): View
    {
        $balita = Balita::findOrFail($id);

        return view('kader.data.balita.edit', compact('balita'));
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $balita = Balita::findOrFail($id);

        $validated = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'nik' => ['required', 'regex:/^[0-9]{16}$/', 'unique:balitas,nik,' . $balita->id],
            'jenis_kelamin' => ['required', Rule::in(['L', 'P'])],
            'tempat_lahir' => ['required', 'string', 'max:100'],
            'tanggal_lahir' => ['required', 'date', 'before_or_equal:today'],
            'nama_ibu' => ['required', 'string', 'max:255'],
            'nik_ibu' => ['nullable', 'regex:/^[0-9]{16}$/'],
            'nama_ayah' => ['nullable', 'string', 'max:255'],
            'alamat' => ['required', 'string'],
            'berat_lahir' => ['nullable', 'numeric', 'min:0', 'max:20'],
            'panjang_lahir' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ], [
            'nama_lengkap.required' => 'Nama lengkap Balita wajib diisi.',
            'nik.required' => 'NIK Balita wajib diisi.',
            'nik.regex' => 'NIK Balita harus berisi tepat 16 digit angka.',
            'nik.unique' => 'NIK Balita ini sudah digunakan data lain.',
            'tanggal_lahir.before_or_equal' => 'Tanggal lahir tidak boleh melebihi hari ini.',
            'nik_ibu.regex' => 'NIK ibu harus berisi tepat 16 digit angka.',
        ]);

        $tanggalLahir = Carbon::parse($validated['tanggal_lahir']);
        $usiaBulan = (int) $tanggalLahir->diffInMonths(now());

        if ($usiaBulan >= 60) {
            return back()
                ->withInput()
                ->with('error', 'Pembaruan ditolak. Usia Balita melewati batas layanan modul ini, yaitu maksimal 59 bulan.');
        }

        DB::beginTransaction();

        try {
            $linkedUser = $this->findLinkedUser($validated['nik']);

            $data = [
                'nik' => $validated['nik'],
                'nama_lengkap' => $validated['nama_lengkap'],
                'tempat_lahir' => $validated['tempat_lahir'],
                'tanggal_lahir' => $validated['tanggal_lahir'],
                'jenis_kelamin' => $validated['jenis_kelamin'],
                'nama_ibu' => $validated['nama_ibu'],
                'nama_ayah' => $validated['nama_ayah'] ?? null,
                'alamat' => $validated['alamat'],
                'berat_lahir' => $validated['berat_lahir'] ?? null,
                'panjang_lahir' => $validated['panjang_lahir'] ?? null,
            ];

            if ($this->balitaHasNikIbuColumn()) {
                $data['nik_ibu'] = $validated['nik_ibu'] ?? null;
            }

            if ($this->balitaHasUserIdColumn()) {
                $data['user_id'] = $linkedUser?->id;
            }

            $balita->update($data);

            DB::commit();

            if ($linkedUser && $this->balitaHasUserIdColumn()) {
                return redirect()
                    ->route('kader.data.balita.index')
                    ->with('success', 'Data Balita berhasil diperbarui dan akun warga berhasil disinkronkan.');
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

    public function destroy($id): RedirectResponse
    {
        $balita = Balita::findOrFail($id);

        if ($balita->kunjungans()->exists() || $balita->pemeriksaans()->exists()) {
            return back()
                ->with('error', 'Data tidak bisa dihapus karena Balita sudah memiliki riwayat kunjungan, pengukuran, atau rekam medis.');
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

    public function bulkDelete(Request $request): RedirectResponse
    {
        $ids = $request->input('ids', []);

        if (! is_array($ids) || count($ids) === 0) {
            return back()
                ->with('error', 'Tidak ada data Balita yang dipilih untuk dihapus.');
        }

        $ids = collect($ids)
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        if (count($ids) === 0) {
            return back()
                ->with('error', 'Data pilihan tidak valid.');
        }

        $dataAktif = Balita::query()
            ->whereIn('id', $ids)
            ->where(function ($q) {
                $q->whereHas('kunjungans')
                    ->orWhereHas('pemeriksaans');
            })
            ->count();

        if ($dataAktif > 0) {
            return back()
                ->with('error', "{$dataAktif} data Balita tidak bisa dihapus karena sudah memiliki riwayat kunjungan, pengukuran, atau rekam medis.");
        }

        DB::beginTransaction();

        try {
            $jumlah = Balita::query()
                ->whereIn('id', $ids)
                ->delete();

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

    public function syncUser($id): RedirectResponse
    {
        $balita = Balita::findOrFail($id);

        if (! $this->balitaHasUserIdColumn()) {
            return back()
                ->with('error', 'Kolom user_id belum tersedia pada tabel balitas. Jalankan migration dulu, jangan database diajak cosplay.');
        }

        $user = $this->findLinkedUser($balita->nik);

        if (! $user) {
            $balita->user_id = null;
            $balita->save();

            return back()
                ->with('error', 'Akun warga dengan NIK Balita ini belum ditemukan.');
        }

        $balita->user_id = $user->id;
        $balita->save();

        return back()
            ->with('success', 'Data Balita berhasil disinkronkan dengan akun warga berdasarkan NIK Balita.');
    }

    private function findLinkedUser(?string $nik): ?User
    {
        $nik = trim((string) $nik);

        if ($nik === '') {
            return null;
        }

        if (! Schema::hasColumn('users', 'nik')) {
            return null;
        }

        return User::query()
            ->where('nik', $nik)
            ->when(Schema::hasColumn('users', 'role'), function ($query) {
                $query->where('role', 'user');
            })
            ->first();
    }

    private function balitaHasUserIdColumn(): bool
    {
        return Schema::hasColumn('balitas', 'user_id');
    }

    private function balitaHasNikIbuColumn(): bool
    {
        return Schema::hasColumn('balitas', 'nik_ibu');
    }

    private function generateKodeBalita(): string
    {
        do {
            $kode = 'BLT-' . now('Asia/Jakarta')->format('ym') . '-' . str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (Balita::where('kode_balita', $kode)->exists());

        return $kode;
    }
}