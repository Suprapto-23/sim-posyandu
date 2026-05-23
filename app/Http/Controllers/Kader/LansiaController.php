<?php

namespace App\Http\Controllers\Kader;

use App\Http\Controllers\Controller;
use App\Models\Lansia;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class LansiaController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->get('search', ''));
        $statusAkun = $request->get('status_akun', 'semua');
        $jenisKelamin = $request->get('jenis_kelamin', 'semua');
        $kemandirian = $request->get('kemandirian', 'semua');

        $has = fn ($column) => Schema::hasColumn('lansias', $column);

        if (!in_array($statusAkun, ['semua', 'terhubung', 'belum'], true)) {
            $statusAkun = 'semua';
        }

        if (!in_array($jenisKelamin, ['semua', 'L', 'P'], true)) {
            $jenisKelamin = 'semua';
        }

        if (!in_array($kemandirian, ['semua', 'mandiri', 'bantuan_sebagian', 'ketergantungan_penuh'], true)) {
            $kemandirian = 'semua';
        }

        $baseQuery = Lansia::query()
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

        $statMandiri = $has('tingkat_kemandirian')
            ? (clone $baseQuery)->where('tingkat_kemandirian', 'mandiri')->count()
            : 0;

        $statButuhBantuan = $has('tingkat_kemandirian')
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

        $statTensiTercatat = $has('tekanan_darah')
            ? (clone $baseQuery)
                ->whereNotNull('tekanan_darah')
                ->where('tekanan_darah', '<>', '')
                ->count()
            : 0;

        $query = Lansia::query()
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

        if ($kemandirian !== 'semua' && $has('tingkat_kemandirian')) {
            $nilaiKemandirian = match ($kemandirian) {
                'mandiri' => ['mandiri'],
                'bantuan_sebagian' => ['bantuan_sebagian', 'bantuan_ringan', 'bantuan_sedang'],
                'ketergantungan_penuh' => ['ketergantungan_penuh', 'ketergantungan_tinggi'],
                default => [],
            };

            if (!empty($nilaiKemandirian)) {
                $query->whereIn('tingkat_kemandirian', $nilaiKemandirian);
            }
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search, $has) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%")
                    ->orWhere('tempat_lahir', 'like', "%{$search}%")
                    ->orWhere('alamat', 'like', "%{$search}%")
                    ->orWhere('penyakit_bawaan', 'like', "%{$search}%");

                if ($has('kode_lansia')) {
                    $q->orWhere('kode_lansia', 'like', "%{$search}%");
                }

                if ($has('tingkat_kemandirian')) {
                    $q->orWhere('tingkat_kemandirian', 'like', "%{$search}%");
                }

                if ($has('tekanan_darah')) {
                    $q->orWhere('tekanan_darah', 'like', "%{$search}%");
                }

                if ($has('keluhan')) {
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

    public function create()
    {
        return view('kader.data.lansia.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules(), $this->messages());
        $data = $this->prepareData($validated);

        DB::beginTransaction();

        try {
            if (Schema::hasColumn('lansias', 'kode_lansia')) {
                $data['kode_lansia'] = $this->generateKodeLansia();
            }

            if (Schema::hasColumn('lansias', 'imt')) {
                $data['imt'] = $this->calculateImt(
                    $data['berat_badan'] ?? null,
                    $data['tinggi_badan'] ?? null
                );
            }

            if (Schema::hasColumn('lansias', 'user_id')) {
                $data['user_id'] = null;

                if (!empty($data['nik'])) {
                    $linkedUser = $this->findLinkedUser($data['nik']);
                    $data['user_id'] = $linkedUser?->id;
                }
            }

            if (Schema::hasColumn('lansias', 'created_by')) {
                $data['created_by'] = Auth::id();
            }

            $lansia = new Lansia();
            $lansia->forceFill($data);
            $lansia->save();

            DB::commit();

            return redirect()
                ->route('kader.data.lansia.index')
                ->with('success', 'Data Lansia berhasil ditambahkan.');
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('KADER_LANSIA_STORE_ERROR', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'data' => $data,
            ]);

            return back()
                ->withInput()
                ->with('error', 'Gagal menyimpan data Lansia: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $lansia = Lansia::with([
            'user',
            'pemeriksaan_terakhir',
            'kunjungans' => function ($q) {
                $q->latest('tanggal_kunjungan');
            },
            'kunjungans.pemeriksaan',
        ])->findOrFail($id);

        return view('kader.data.lansia.show', compact('lansia'));
    }

    public function edit($id)
    {
        $lansia = Lansia::with(['user'])->findOrFail($id);

        return view('kader.data.lansia.edit', compact('lansia'));
    }

    public function update(Request $request, $id)
    {
        $lansia = Lansia::findOrFail($id);

        $validated = $request->validate($this->rules($lansia->id), $this->messages());
        $data = $this->prepareData($validated);

        DB::beginTransaction();

        try {
            if (Schema::hasColumn('lansias', 'imt')) {
                $data['imt'] = $this->calculateImt(
                    $data['berat_badan'] ?? null,
                    $data['tinggi_badan'] ?? null
                );
            }

            if (Schema::hasColumn('lansias', 'user_id')) {
                if (!empty($data['nik'])) {
                    $linkedUser = $this->findLinkedUser($data['nik']);
                    $data['user_id'] = $linkedUser ? $linkedUser->id : $lansia->user_id;
                } else {
                    $data['user_id'] = null;
                }
            }

            $lansia->forceFill($data);
            $lansia->save();

            DB::commit();

            return redirect()
                ->route('kader.data.lansia.show', $lansia->id)
                ->with('success', 'Data Lansia berhasil diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('KADER_LANSIA_UPDATE_ERROR', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'data' => $data,
            ]);

            return back()
                ->withInput()
                ->with('error', 'Gagal memperbarui data Lansia: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $lansia = Lansia::findOrFail($id);

            if ($lansia->kunjungans()->count() > 0) {
                return back()->with(
                    'error',
                    'Data Lansia tidak dapat dihapus karena sudah memiliki riwayat layanan.'
                );
            }

            $nama = $lansia->nama_lengkap;
            $lansia->delete();

            return redirect()
                ->route('kader.data.lansia.index')
                ->with('success', "Data Lansia atas nama {$nama} berhasil dihapus.");
        } catch (\Throwable $e) {
            Log::error('KADER_LANSIA_DELETE_ERROR', [
                'message' => $e->getMessage(),
            ]);

            return back()->with('error', 'Gagal menghapus data Lansia.');
        }
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:lansias,id',
        ], [
            'ids.required' => 'Pilih minimal satu data Lansia untuk dihapus.',
            'ids.array' => 'Format pilihan data tidak valid.',
        ]);

        $ids = $request->ids;

        $terpakai = Lansia::whereIn('id', $ids)
            ->has('kunjungans')
            ->count();

        if ($terpakai > 0) {
            return back()->with(
                'error',
                "Operasi dibatalkan. {$terpakai} data Lansia sudah memiliki riwayat layanan."
            );
        }

        try {
            Lansia::whereIn('id', $ids)->delete();

            return redirect()
                ->route('kader.data.lansia.index')
                ->with('success', count($ids) . ' data Lansia berhasil dihapus.');
        } catch (\Throwable $e) {
            Log::error('KADER_LANSIA_BULK_DELETE_ERROR', [
                'message' => $e->getMessage(),
            ]);

            return back()->with('error', 'Gagal menghapus data Lansia yang dipilih.');
        }
    }

    public function syncUser($id)
    {
        $lansia = Lansia::findOrFail($id);

        if (empty($lansia->nik)) {
            return back()->with(
                'error',
                'Sinkron akun gagal. Data Lansia belum memiliki NIK.'
            );
        }

        $user = $this->findLinkedUser($lansia->nik);

        if (!$user) {
            return back()->with(
                'error',
                'Tidak ditemukan akun warga dengan NIK yang sesuai.'
            );
        }

        $lansia->forceFill([
            'user_id' => $user->id,
        ]);

        $lansia->save();

        return back()->with(
            'success',
            'Data Lansia berhasil dihubungkan dengan akun warga.'
        );
    }

    private function rules(?int $ignoreId = null): array
    {
        $uniqueNikRule = 'nullable|digits:16|unique:lansias,nik';

        if ($ignoreId) {
            $uniqueNikRule .= ',' . $ignoreId;
        }

        return [
            'nik' => $uniqueNikRule,
            'nama_lengkap' => 'required|string|max:191',
            'tempat_lahir' => 'required|string|max:100',
            'tanggal_lahir' => 'required|date|before_or_equal:-45 years',
            'jenis_kelamin' => 'required|in:L,P',
            'alamat' => 'required|string|max:1000',

            'berat_badan' => 'nullable|numeric|min:1|max:300',
            'tinggi_badan' => 'nullable|numeric|min:50|max:250',
            'penyakit_bawaan' => 'nullable|string|max:1000',

            'tingkat_kemandirian' => 'nullable|in:mandiri,bantuan_sebagian,ketergantungan_penuh',
            'tekanan_darah' => ['nullable', 'regex:/^\d{2,3}\/\d{2,3}$/'],
            'gula_darah' => 'nullable|numeric|min:0|max:999',
            'kolesterol' => 'nullable|numeric|min:0|max:999',
            'asam_urat' => 'nullable|numeric|min:0|max:99',
            'lingkar_perut' => 'nullable|numeric|min:20|max:200',
            'keluhan' => 'nullable|string|max:1000',
        ];
    }

    private function messages(): array
    {
        return [
            'nik.digits' => 'NIK harus terdiri dari 16 digit angka.',
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
            'lingkar_perut.min' => 'Lingkar perut minimal 20 cm.',
            'lingkar_perut.max' => 'Lingkar perut maksimal 200 cm.',
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
            'nik',
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

    private function onlyExistingLansiaColumns(array $data): array
    {
        return collect($data)
            ->filter(function ($value, $key) {
                return Schema::hasColumn('lansias', $key);
            })
            ->toArray();
    }

    private function calculateImt($beratBadan, $tinggiBadan): ?float
    {
        if (!$beratBadan || !$tinggiBadan || (float) $tinggiBadan <= 0) {
            return null;
        }

        $tinggiMeter = (float) $tinggiBadan / 100;

        return round((float) $beratBadan / ($tinggiMeter * $tinggiMeter), 2);
    }

    private function generateKodeLansia(): string
    {
        do {
            $kode = 'LNS-' . now('Asia/Jakarta')->format('Ymd') . '-' . strtoupper(Str::random(4));
        } while (Lansia::where('kode_lansia', $kode)->exists());

        return $kode;
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
}