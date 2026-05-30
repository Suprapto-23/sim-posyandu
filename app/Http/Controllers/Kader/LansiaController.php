<?php

namespace App\Http\Controllers\Kader;

use App\Http\Controllers\Controller;
use App\Models\Lansia;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LansiaController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->get('search', ''));
        $statusAkun = $request->get('status_akun', 'semua');
        $jenisKelamin = $request->get('jenis_kelamin', 'semua');
        $kemandirian = $request->get('kemandirian', 'semua');

        if (!in_array($statusAkun, ['semua', 'terhubung', 'belum'], true)) {
            $statusAkun = 'semua';
        }

        if (!in_array($jenisKelamin, ['semua', 'L', 'P'], true)) {
            $jenisKelamin = 'semua';
        }

        if (!in_array($kemandirian, ['semua', 'mandiri', 'bantuan_sebagian', 'ketergantungan_penuh'], true)) {
            $kemandirian = 'semua';
        }

        $hasUserId = $this->hasColumn('user_id');

        if (!$hasUserId) {
            $statusAkun = 'semua';
        }

        $baseQuery = Lansia::query();

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

        $statMandiri = $this->hasColumn('tingkat_kemandirian')
            ? (clone $baseQuery)->where('tingkat_kemandirian', 'mandiri')->count()
            : 0;

        $statButuhBantuan = $this->hasColumn('tingkat_kemandirian')
            ? (clone $baseQuery)
                ->whereIn('tingkat_kemandirian', [
                    'bantuan_sebagian',
                    'ketergantungan_penuh',
                    'bantuan_ringan',
                    'bantuan_sedang',
                    'ketergantungan_tinggi',
                ])
                ->count()
            : 0;

        $statTensiTercatat = $this->hasColumn('tekanan_darah')
            ? (clone $baseQuery)
                ->whereNotNull('tekanan_darah')
                ->where('tekanan_darah', '<>', '')
                ->count()
            : 0;

        $selectColumns = $this->existingColumns([
            'id',
            'user_id',
            'kode_lansia',
            'nik',
            'nama_lengkap',
            'tempat_lahir',
            'tanggal_lahir',
            'jenis_kelamin',
            'alamat',
            'berat_badan',
            'tinggi_badan',
            'imt',
            'penyakit_bawaan',
            'tingkat_kemandirian',
            'tekanan_darah',
            'gula_darah',
            'kolesterol',
            'asam_urat',
            'lingkar_perut',
            'keluhan',
            'created_by',
            'created_at',
            'updated_at',
        ]);

        $query = Lansia::query()
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

        if ($kemandirian !== 'semua' && $this->hasColumn('tingkat_kemandirian')) {
            $query->where('tingkat_kemandirian', $kemandirian);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%")
                    ->orWhere('tempat_lahir', 'like', "%{$search}%")
                    ->orWhere('alamat', 'like', "%{$search}%");

                if ($this->hasColumn('kode_lansia')) {
                    $q->orWhere('kode_lansia', 'like', "%{$search}%");
                }

                if ($this->hasColumn('penyakit_bawaan')) {
                    $q->orWhere('penyakit_bawaan', 'like', "%{$search}%");
                }

                if ($this->hasColumn('tingkat_kemandirian')) {
                    $q->orWhere('tingkat_kemandirian', 'like', "%{$search}%");
                }

                if ($this->hasColumn('tekanan_darah')) {
                    $q->orWhere('tekanan_darah', 'like', "%{$search}%");
                }

                if ($this->hasColumn('keluhan')) {
                    $q->orWhere('keluhan', 'like', "%{$search}%");
                }
            });
        }

        $items = $query->paginate(10)->withQueryString();

        return view('kader.data.lansia.index', compact(
            'items',
            'search',
            'statusAkun',
            'jenisKelamin',
            'kemandirian',
            'statTotal',
            'statLaki',
            'statPerempuan',
            'statTerhubung',
            'statBelumTerhubung',
            'statBulanIni',
            'statMandiri',
            'statButuhBantuan',
            'statTensiTercatat'
        ));
    }

    public function create(): View
    {
        return view('kader.data.lansia.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules(), $this->messages());

        DB::beginTransaction();

        try {
            $data = $this->prepareData($validated);
            $linkedUser = $this->findLinkedUser($data['nik'] ?? null);

            if ($this->hasColumn('kode_lansia')) {
                $data['kode_lansia'] = $this->generateKodeLansia();
            }

            if ($this->hasColumn('imt')) {
                $data['imt'] = $this->calculateImt(
                    $data['berat_badan'] ?? null,
                    $data['tinggi_badan'] ?? null
                );
            }

            if ($this->hasColumn('user_id')) {
                $data['user_id'] = $linkedUser ? $linkedUser->id : null;
            }

            if ($this->hasColumn('created_by')) {
                $data['created_by'] = Auth::id();
            }

            Lansia::create($data);

            DB::commit();

            if ($linkedUser && $this->hasColumn('user_id')) {
                return redirect()
                    ->route('kader.data.lansia.index')
                    ->with('success', 'Data Lansia berhasil disimpan dan otomatis terhubung dengan akun warga.');
            }

            return redirect()
                ->route('kader.data.lansia.index')
                ->with('warning', 'Data Lansia berhasil disimpan, tetapi belum terhubung dengan akun warga. Pastikan Admin membuat akun warga memakai NIK Lansia yang sama.');
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('KADER_LANSIA_STORE_ERROR', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Gagal menyimpan data Lansia. Periksa kembali data atau struktur database.');
        }
    }

    public function show($id): View
    {
        $query = Lansia::query()
            ->with([
                'pemeriksaan_terakhir',
                'kunjungans' => function ($q) {
                    $q->with(['petugas', 'pemeriksaan'])
                        ->latest('tanggal_kunjungan')
                        ->take(10);
                },
            ]);

        if ($this->hasColumn('user_id')) {
            $query->with(['user:id,name,nik,email,role,status']);
        }

        $lansia = $query->findOrFail($id);

        $userTerhubung = null;

        if ($this->hasColumn('user_id')) {
            $userTerhubung = $lansia->user;
        }

        if (!$userTerhubung) {
            $userTerhubung = $this->findLinkedUser($lansia->nik);
        }

        return view('kader.data.lansia.show', compact(
            'lansia',
            'userTerhubung'
        ));
    }

    public function edit($id): View
    {
        $lansia = Lansia::query()
            ->when($this->hasColumn('user_id'), function ($q) {
                $q->with(['user:id,name,nik,email,role,status']);
            })
            ->findOrFail($id);

        return view('kader.data.lansia.edit', compact('lansia'));
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $lansia = Lansia::findOrFail($id);

        $validated = $request->validate($this->rules($lansia->id), $this->messages());

        DB::beginTransaction();

        try {
            $data = $this->prepareData($validated);
            $linkedUser = $this->findLinkedUser($data['nik'] ?? null);

            if ($this->hasColumn('imt')) {
                $data['imt'] = $this->calculateImt(
                    $data['berat_badan'] ?? null,
                    $data['tinggi_badan'] ?? null
                );
            }

            if ($this->hasColumn('user_id')) {
                $data['user_id'] = $linkedUser ? $linkedUser->id : null;
            }

            $lansia->update($data);

            DB::commit();

            if ($linkedUser && $this->hasColumn('user_id')) {
                return redirect()
                    ->route('kader.data.lansia.index')
                    ->with('success', 'Data Lansia berhasil diperbarui dan akun warga berhasil disinkronkan.');
            }

            return redirect()
                ->route('kader.data.lansia.index')
                ->with('warning', 'Data Lansia berhasil diperbarui, tetapi belum terhubung dengan akun warga.');
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('KADER_LANSIA_UPDATE_ERROR', [
                'message' => $e->getMessage(),
                'lansia_id' => $lansia->id,
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Gagal memperbarui data Lansia. Periksa kembali data atau struktur database.');
        }
    }

    public function destroy($id): RedirectResponse
    {
        try {
            $lansia = Lansia::findOrFail($id);

            if ($lansia->kunjungans()->exists()) {
                return back()
                    ->with('error', 'Data Lansia tidak bisa dihapus karena sudah memiliki riwayat kunjungan atau rekam medis.');
            }

            $nama = $lansia->nama_lengkap;
            $lansia->delete();

            return redirect()
                ->route('kader.data.lansia.index')
                ->with('success', "Data Lansia atas nama {$nama} berhasil dihapus.");
        } catch (\Throwable $e) {
            Log::error('KADER_LANSIA_DELETE_ERROR', [
                'message' => $e->getMessage(),
                'lansia_id' => $id,
            ]);

            return back()
                ->with('error', 'Gagal menghapus data Lansia.');
        }
    }

    public function bulkDelete(Request $request): RedirectResponse
    {
        $ids = $request->input('ids', []);

        if (!is_array($ids) || count($ids) === 0) {
            return back()
                ->with('error', 'Tidak ada data Lansia yang dipilih untuk dihapus.');
        }

        $terpakai = Lansia::whereIn('id', $ids)
            ->has('kunjungans')
            ->count();

        if ($terpakai > 0) {
            return back()
                ->with('error', "{$terpakai} data Lansia tidak bisa dihapus karena sudah memiliki riwayat kunjungan atau rekam medis.");
        }

        DB::beginTransaction();

        try {
            $jumlah = Lansia::whereIn('id', $ids)->delete();

            DB::commit();

            return redirect()
                ->route('kader.data.lansia.index')
                ->with('success', "{$jumlah} data Lansia berhasil dihapus.");
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('KADER_LANSIA_BULK_DELETE_ERROR', [
                'message' => $e->getMessage(),
                'ids' => $ids,
            ]);

            return back()
                ->with('error', 'Gagal menghapus data Lansia secara massal.');
        }
    }

    public function syncUser($id): RedirectResponse
    {
        $lansia = Lansia::findOrFail($id);

        if (!$this->hasColumn('user_id')) {
            return back()
                ->with('error', 'Sinkron akun gagal. Kolom user_id belum tersedia pada tabel lansias.');
        }

        if (blank($lansia->nik)) {
            return back()
                ->with('error', 'Sinkron akun gagal. Data Lansia belum memiliki NIK.');
        }

        $user = $this->findLinkedUser($lansia->nik);

        if (!$user) {
            return back()
                ->with('error', 'Tidak ditemukan akun warga dengan NIK Lansia yang sesuai.');
        }

        $lansia->user_id = $user->id;
        $lansia->save();

        return back()
            ->with('success', 'Data Lansia berhasil disinkronkan dengan akun warga.');
    }

    private function rules(?int $ignoreId = null): array
    {
        return [
            'nik' => [
                'required',
                'digits:16',
                Rule::unique('lansias', 'nik')->ignore($ignoreId),
            ],
            'nama_lengkap' => ['required', 'string', 'max:191'],
            'tempat_lahir' => ['required', 'string', 'max:100'],
            'tanggal_lahir' => ['required', 'date', 'before_or_equal:-45 years'],
            'jenis_kelamin' => ['required', 'in:L,P'],
            'alamat' => ['required', 'string', 'max:1000'],

            'berat_badan' => ['nullable', 'numeric', 'min:1', 'max:300'],
            'tinggi_badan' => ['nullable', 'numeric', 'min:50', 'max:250'],
            'penyakit_bawaan' => ['nullable', 'string', 'max:1000'],

            'tingkat_kemandirian' => [
                'nullable',
                'in:mandiri,bantuan_sebagian,ketergantungan_penuh',
            ],

            'tekanan_darah' => [
                'nullable',
                'regex:/^\d{2,3}\/\d{2,3}$/',
            ],

            'gula_darah' => ['nullable', 'numeric', 'min:0', 'max:999'],
            'kolesterol' => ['nullable', 'numeric', 'min:0', 'max:999'],
            'asam_urat' => ['nullable', 'numeric', 'min:0', 'max:99'],
            'lingkar_perut' => ['nullable', 'numeric', 'min:20', 'max:200'],
            'keluhan' => ['nullable', 'string', 'max:1000'],
        ];
    }

    private function messages(): array
    {
        return [
            'nik.required' => 'NIK Lansia wajib diisi.',
            'nik.digits' => 'NIK Lansia harus terdiri dari 16 digit angka.',
            'nik.unique' => 'NIK ini sudah terdaftar sebagai data Lansia.',

            'nama_lengkap.required' => 'Nama lengkap Lansia wajib diisi.',
            'tempat_lahir.required' => 'Tempat lahir wajib diisi.',
            'tanggal_lahir.required' => 'Tanggal lahir wajib diisi.',
            'tanggal_lahir.before_or_equal' => 'Kategori Lansia/Pra-Lansia minimal harus berusia 45 tahun.',

            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
            'jenis_kelamin.in' => 'Jenis kelamin hanya boleh L atau P.',
            'alamat.required' => 'Alamat tinggal wajib diisi.',

            'berat_badan.numeric' => 'Berat badan harus berupa angka.',
            'berat_badan.min' => 'Berat badan minimal 1 kg.',
            'berat_badan.max' => 'Berat badan maksimal 300 kg.',

            'tinggi_badan.numeric' => 'Tinggi badan harus berupa angka.',
            'tinggi_badan.min' => 'Tinggi badan minimal 50 cm.',
            'tinggi_badan.max' => 'Tinggi badan maksimal 250 cm.',

            'tingkat_kemandirian.in' => 'Tingkat kemandirian tidak valid.',
            'tekanan_darah.regex' => 'Format tekanan darah harus seperti 120/80.',

            'gula_darah.numeric' => 'Gula darah harus berupa angka.',
            'kolesterol.numeric' => 'Kolesterol harus berupa angka.',
            'asam_urat.numeric' => 'Asam urat harus berupa angka.',
            'lingkar_perut.numeric' => 'Lingkar perut harus berupa angka.',
            'keluhan.max' => 'Keluhan maksimal 1000 karakter.',
            'penyakit_bawaan.max' => 'Riwayat penyakit bawaan maksimal 1000 karakter.',
        ];
    }

    private function prepareData(array $validated): array
    {
        $data = $this->normalizeNullableFields($validated);

        return $this->onlyExistingLansiaColumns($data);
    }

    private function normalizeNullableFields(array $data): array
    {
        $nullableFields = [
            'berat_badan',
            'tinggi_badan',
            'penyakit_bawaan',
            'tingkat_kemandirian',
            'tekanan_darah',
            'gula_darah',
            'kolesterol',
            'asam_urat',
            'lingkar_perut',
            'keluhan',
        ];

        foreach ($nullableFields as $field) {
            if (array_key_exists($field, $data) && $data[$field] === '') {
                $data[$field] = null;
            }
        }

        return $data;
    }

    private function calculateImt($beratBadan, $tinggiBadan): ?float
    {
        if (!$beratBadan || !$tinggiBadan || (float) $tinggiBadan <= 0) {
            return null;
        }

        $tinggiMeter = (float) $tinggiBadan / 100;

        return round((float) $beratBadan / ($tinggiMeter * $tinggiMeter), 2);
    }

    private function findLinkedUser(?string $nik): ?User
    {
        $nik = trim((string) $nik);

        if ($nik === '') {
            return null;
        }

        $query = User::query();

        $query->where(function ($q) use ($nik) {
            $hasCondition = false;

            if (Schema::hasColumn('users', 'nik')) {
                $q->orWhere('nik', $nik);
                $hasCondition = true;
            }

            if (Schema::hasColumn('users', 'username')) {
                $q->orWhere('username', $nik);
                $hasCondition = true;
            }

            if (!$hasCondition) {
                $q->whereRaw('1 = 0');
            }
        });

        if (Schema::hasColumn('users', 'role')) {
            $query->whereIn('role', ['user', 'warga', 'masyarakat']);
        }

        return $query->first();
    }

    private function generateKodeLansia(): string
    {
        do {
            $kode = 'LNS-' . now('Asia/Jakarta')->format('ymd') . '-' . strtoupper(Str::random(4));
        } while (Lansia::where('kode_lansia', $kode)->exists());

        return $kode;
    }

    private function hasColumn(string $column): bool
    {
        return Schema::hasColumn('lansias', $column);
    }

    private function existingColumns(array $columns): array
    {
        return array_values(array_filter($columns, function ($column) {
            return $this->hasColumn($column);
        }));
    }

    private function onlyExistingLansiaColumns(array $data): array
    {
        return collect($data)
            ->filter(function ($value, $key) {
                return $this->hasColumn($key);
            })
            ->toArray();
    }
}