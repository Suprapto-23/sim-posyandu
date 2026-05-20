<?php

namespace App\Http\Controllers\Kader;

use App\Http\Controllers\Controller;
use App\Models\Balita;
use App\Models\Kunjungan;
use App\Models\Lansia;
use App\Models\Pemeriksaan;
use App\Models\Remaja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class PemeriksaanController extends Controller
{
    private array $kategoriAktif = ['balita', 'remaja', 'lansia'];

    private array $reviewedStatuses = [
        'verified',
        'terverifikasi',
        'approved',
        'valid',
        'tervalidasi',
        'sudah_ditinjau',
    ];

    private array $needFixStatuses = [
        'ditolak',
        'rejected',
        'direvisi',
        'perlu_perbaikan',
    ];

    public function index(Request $request)
    {
        $kategori = $request->get('kategori', '');
        $search = $request->get('search', '');
        $status = $request->get('status', '');

        $query = Pemeriksaan::with(['kunjungan.pasien', 'kunjungan.petugas', 'verifikator'])
            ->whereIn('kategori_pasien', $this->kategoriAktif)
            ->latest('tanggal_periksa')
            ->latest('created_at');

        if (!empty($kategori) && in_array($kategori, $this->kategoriAktif, true)) {
            $query->where('kategori_pasien', $kategori);
        }

        if (!empty($status)) {
            $this->applyStatusFilter($query, $status);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('kunjungan', function ($kunjunganQ) use ($search) {
                    $kunjunganQ->whereHasMorph(
                        'pasien',
                        [Balita::class, Remaja::class, Lansia::class],
                        function ($morphQ) use ($search) {
                            $morphQ->where('nama_lengkap', 'like', "%{$search}%")
                                ->orWhere('nik', 'like', "%{$search}%");
                        }
                    );
                });

                if (Schema::hasColumn('pemeriksaans', 'nama_pasien')) {
                    $q->orWhere('nama_pasien', 'like', "%{$search}%");
                }

                if (Schema::hasColumn('pemeriksaans', 'nik_pasien')) {
                    $q->orWhere('nik_pasien', 'like', "%{$search}%");
                }
            });
        }

        $pemeriksaans = $query->paginate(15)->withQueryString();

        return view('kader.pemeriksaan.index', compact(
            'pemeriksaans',
            'kategori',
            'search',
            'status'
        ));
    }

    public function create(Request $request)
    {
        $kategori_awal = $request->get('kategori', 'balita');

        if (!in_array($kategori_awal, $this->kategoriAktif, true)) {
            $kategori_awal = 'balita';
        }

        $pasien_id_awal = $request->get('pasien_id');

        return view('kader.pemeriksaan.create', compact(
            'kategori_awal',
            'pasien_id_awal'
        ));
    }

    public function store(Request $request)
    {
        $validated = $this->validatePemeriksaan($request);

        $kategori = $validated['kategori_pasien'];
        $pasienId = $validated['pasien_id'];

        if (!$this->isValidPasien($kategori, $pasienId)) {
            return back()
                ->withInput()
                ->with('error', 'Data sasaran tidak valid. Pilih pasien dari kategori yang benar.');
        }

        $imt = $this->calculateImt(
            $validated['berat_badan'] ?? null,
            $validated['tinggi_badan'] ?? null,
            $kategori
        );

        DB::beginTransaction();

        try {
            $kunjungan = Kunjungan::create($this->buildKunjunganPayload($validated));

            $payload = [
    'kunjungan_id' => $kunjungan->id,
    'pasien_id' => $pasienId,
    'kategori_pasien' => $kategori,
    'tanggal_periksa' => $validated['tanggal_periksa'],

    'berat_badan' => $validated['berat_badan'] ?? null,
    'tinggi_badan' => $validated['tinggi_badan'] ?? null,
    'suhu_tubuh' => $validated['suhu_tubuh'] ?? null,
    'imt' => $imt,

    'lingkar_kepala' => $validated['lingkar_kepala'] ?? null,
    'lingkar_lengan' => $validated['lingkar_lengan'] ?? null,
    'lingkar_perut' => $validated['lingkar_perut'] ?? null,

    'tekanan_darah' => $validated['tekanan_darah'] ?? null,
    'gula_darah' => $validated['gula_darah'] ?? null,
    'kolesterol' => $validated['kolesterol'] ?? null,
    'asam_urat' => $validated['asam_urat'] ?? null,
    'hemoglobin' => $validated['hemoglobin'] ?? null,
    'tingkat_kemandirian' => $validated['tingkat_kemandirian'] ?? null,

    'keluhan' => $validated['keluhan'] ?? null,
    'catatan_kader' => $validated['catatan_kader'] ?? null,

    'status_verifikasi' => 'pending',
    'created_by' => Auth::id(),
];

            $payload = $this->filterColumns('pemeriksaans', $payload);

            Pemeriksaan::create($payload);

            DB::commit();

            return redirect()
                ->route('kader.pemeriksaan.index')
                ->with('success', 'Pengukuran fisik berhasil disimpan dan menunggu review Bidan.');
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('PEMERIKSAAN_STORE_ERROR', [
                'message' => $e->getMessage(),
                'user_id' => Auth::id(),
                'kategori' => $kategori,
            ]);

            return back()
                ->withInput()
                ->with('error', 'Gagal menyimpan pengukuran fisik: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $pemeriksaan = Pemeriksaan::with([
            'kunjungan.pasien',
            'kunjungan.petugas',
            'verifikator',
        ])->findOrFail($id);

        return view('kader.pemeriksaan.show', compact('pemeriksaan'));
    }

    public function edit($id)
    {
        $pemeriksaan = Pemeriksaan::with('kunjungan.pasien')->findOrFail($id);

        if ($this->isReviewed($pemeriksaan->status_verifikasi)) {
            return back()->with(
                'error',
                'Data sudah ditinjau Bidan dan tidak dapat diubah oleh Kader.'
            );
        }

        return view('kader.pemeriksaan.edit', compact('pemeriksaan'));
    }

    public function update(Request $request, $id)
    {
        $pemeriksaan = Pemeriksaan::with('kunjungan')->findOrFail($id);

        if ($this->isReviewed($pemeriksaan->status_verifikasi)) {
            return $this->errorResponse(
                $request,
                'Data sudah ditinjau Bidan dan tidak dapat diubah oleh Kader.'
            );
        }

        $validated = $this->validatePemeriksaan($request, false, $pemeriksaan->kategori_pasien);

        $kategori = $pemeriksaan->kategori_pasien;

        $imt = $this->calculateImt(
            $validated['berat_badan'] ?? null,
            $validated['tinggi_badan'] ?? null,
            $kategori
        );

        DB::beginTransaction();

        try {
            $payload = [
    'tanggal_periksa' => $validated['tanggal_periksa'],

    'berat_badan' => $validated['berat_badan'] ?? null,
    'tinggi_badan' => $validated['tinggi_badan'] ?? null,
    'suhu_tubuh' => $validated['suhu_tubuh'] ?? null,
    'imt' => $imt,

    'lingkar_kepala' => $validated['lingkar_kepala'] ?? null,
    'lingkar_lengan' => $validated['lingkar_lengan'] ?? null,
    'lingkar_perut' => $validated['lingkar_perut'] ?? null,

    'tekanan_darah' => $validated['tekanan_darah'] ?? null,
    'gula_darah' => $validated['gula_darah'] ?? null,
    'kolesterol' => $validated['kolesterol'] ?? null,
    'asam_urat' => $validated['asam_urat'] ?? null,
    'hemoglobin' => $validated['hemoglobin'] ?? null,
    'tingkat_kemandirian' => $validated['tingkat_kemandirian'] ?? null,

    'keluhan' => $validated['keluhan'] ?? null,
    'catatan_kader' => $validated['catatan_kader'] ?? null,
];

            /*
             * Kalau data sebelumnya dikembalikan oleh Bidan,
             * setelah Kader memperbaiki, status otomatis balik ke Menunggu Review.
             * Ini alur paling waras. Sistem tidak boleh dendam ke data.
             */
            if ($this->needFix($pemeriksaan->status_verifikasi)) {
                $payload['status_verifikasi'] = 'pending';

                foreach (['catatan_validasi', 'catatan_bidan', 'catatan_review'] as $column) {
                    if (Schema::hasColumn('pemeriksaans', $column)) {
                        $payload[$column] = null;
                    }
                }

                foreach (['verified_by', 'divalidasi_oleh', 'reviewed_by'] as $column) {
                    if (Schema::hasColumn('pemeriksaans', $column)) {
                        $payload[$column] = null;
                    }
                }

                foreach (['verified_at', 'tanggal_validasi', 'reviewed_at'] as $column) {
                    if (Schema::hasColumn('pemeriksaans', $column)) {
                        $payload[$column] = null;
                    }
                }
            }

            $payload = $this->filterColumns('pemeriksaans', $payload);

            $pemeriksaan->update($payload);

            if ($pemeriksaan->kunjungan) {
                $kunjunganPayload = [
                    'tanggal_kunjungan' => $validated['tanggal_periksa'],
                    'keluhan' => $validated['keluhan'] ?? null,
                ];

                $pemeriksaan->kunjungan->update(
                    $this->filterColumns('kunjungans', $kunjunganPayload)
                );
            }

            DB::commit();

            $message = $this->needFix($pemeriksaan->getOriginal('status_verifikasi'))
                ? 'Pengukuran berhasil diperbaiki dan kembali menunggu review Bidan.'
                : 'Pengukuran fisik berhasil diperbarui.';

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'success',
                    'message' => $message,
                    'redirect' => route('kader.pemeriksaan.index'),
                ]);
            }

            return redirect()
                ->route('kader.pemeriksaan.index')
                ->with('success', $message);
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('PEMERIKSAAN_UPDATE_ERROR', [
                'message' => $e->getMessage(),
                'pemeriksaan_id' => $id,
                'user_id' => Auth::id(),
            ]);

            return $this->errorResponse(
                $request,
                'Gagal memperbarui pengukuran fisik: ' . $e->getMessage()
            );
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $pemeriksaan = Pemeriksaan::findOrFail($id);

            if ($this->isReviewed($pemeriksaan->status_verifikasi)) {
                return back()->with(
                    'error',
                    'Data sudah ditinjau Bidan dan tidak dapat dihapus oleh Kader.'
                );
            }

            $kunjunganId = $pemeriksaan->kunjungan_id;

            $pemeriksaan->delete();

            if ($kunjunganId) {
                $kunjungan = Kunjungan::find($kunjunganId);

                if ($kunjungan) {
                    $hasPemeriksaanLain = $kunjungan->pemeriksaan()->exists();

                    $hasImunisasi = method_exists($kunjungan, 'imunisasis')
                        ? $kunjungan->imunisasis()->exists()
                        : false;

                    $hasVitamin = method_exists($kunjungan, 'vitamins')
                        ? $kunjungan->vitamins()->exists()
                        : false;

                    if (!$hasPemeriksaanLain && !$hasImunisasi && !$hasVitamin) {
                        $kunjungan->delete();
                    }
                }
            }

            DB::commit();

            return redirect()
                ->route('kader.pemeriksaan.index')
                ->with('success', 'Data pengukuran fisik berhasil dihapus.');
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('PEMERIKSAAN_DELETE_ERROR', [
                'message' => $e->getMessage(),
                'pemeriksaan_id' => $id,
                'user_id' => Auth::id(),
            ]);

            return back()->with('error', 'Gagal menghapus data pengukuran fisik.');
        }
    }

    public function getPasienApi(Request $request)
    {
        $kategori = $request->get('kategori');

        try {
            if (!in_array($kategori, $this->kategoriAktif, true)) {
                return response()->json([
                    'status' => 'error',
                    'data' => [],
                    'message' => 'Kategori tidak valid.',
                ], 422);
            }

            $modelClass = $this->mapPasienModel($kategori);
            $model = new $modelClass();
            $table = $model->getTable();

            $select = ['id'];

            foreach (['nama_lengkap', 'nik', 'jenis_kelamin', 'tanggal_lahir'] as $column) {
                if (Schema::hasColumn($table, $column)) {
                    $select[] = $column;
                }
            }

            $data = $modelClass::query()
                ->select($select)
                ->orderBy(Schema::hasColumn($table, 'nama_lengkap') ? 'nama_lengkap' : 'id')
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'nama' => $item->nama_lengkap ?? 'Tanpa Nama',
                        'nik' => $item->nik ?? '-',
                        'jenis_kelamin' => $item->jenis_kelamin ?? null,
                        'tanggal_lahir' => $item->tanggal_lahir ?? null,
                    ];
                });

            return response()->json([
                'status' => 'success',
                'data' => $data,
                'message' => 'Data sasaran berhasil dimuat.',
            ]);
        } catch (\Throwable $e) {
            Log::error('API_PASIEN_FETCH_ERROR', [
                'message' => $e->getMessage(),
                'kategori' => $kategori,
            ]);

            return response()->json([
                'status' => 'error',
                'data' => [],
                'message' => 'Sistem gagal memuat data sasaran.',
            ], 500);
        }
    }

    private function validatePemeriksaan(Request $request, bool $isStore = true, ?string $kategoriFromExisting = null): array
{
    $kategori = $isStore
        ? $request->input('kategori_pasien')
        : $kategoriFromExisting;

    $rules = [
        'tanggal_periksa' => 'required|date|before_or_equal:today',

        // Parameter dasar wajib untuk semua kategori
        'berat_badan' => 'required|numeric|min:0.1|max:300',
        'tinggi_badan' => 'required|numeric|min:10|max:250',

        // Parameter tambahan umum
        'suhu_tubuh' => 'nullable|numeric|min:30|max:45',
        'lingkar_kepala' => 'nullable|numeric|min:10|max:100',
        'lingkar_lengan' => 'nullable|numeric|min:5|max:100',
        'lingkar_perut' => 'nullable|numeric|min:20|max:200',

        'tekanan_darah' => [
            'nullable',
            'string',
            'max:20',
            'regex:/^[0-9]{2,3}\/[0-9]{2,3}$/',
        ],

        'gula_darah' => 'nullable|numeric|min:10|max:1000',
        'kolesterol' => 'nullable|integer|min:10|max:1000',
        'asam_urat' => 'nullable|numeric|min:1|max:30',
        'hemoglobin' => 'nullable|numeric|min:1|max:30',
        'tingkat_kemandirian' => 'nullable|string|in:mandiri,bantuan_sebagian,bantuan_penuh',

        'keluhan' => 'nullable|string|max:1000',
        'catatan_kader' => 'nullable|string|max:1000',
    ];

    if ($isStore) {
        $rules['pasien_id'] = 'required|integer';
        $rules['kategori_pasien'] = 'required|in:balita,remaja,lansia';
    }

    if ($kategori === 'balita') {
        $rules['lingkar_kepala'] = 'required|numeric|min:10|max:100';
        $rules['lingkar_lengan'] = 'required|numeric|min:5|max:100';
    }

    if ($kategori === 'remaja') {
        $rules['lingkar_lengan'] = 'required|numeric|min:5|max:100';
        $rules['lingkar_perut'] = 'required|numeric|min:20|max:200';
        $rules['tekanan_darah'] = [
            'required',
            'string',
            'max:20',
            'regex:/^[0-9]{2,3}\/[0-9]{2,3}$/',
        ];
    }

    if ($kategori === 'lansia') {
        $rules['lingkar_perut'] = 'required|numeric|min:20|max:200';
        $rules['tekanan_darah'] = [
            'required',
            'string',
            'max:20',
            'regex:/^[0-9]{2,3}\/[0-9]{2,3}$/',
        ];
        $rules['tingkat_kemandirian'] = 'required|string|in:mandiri,bantuan_sebagian,bantuan_penuh';
    }

    return $request->validate($rules, [
        'pasien_id.required' => 'Warga wajib dipilih terlebih dahulu.',
        'kategori_pasien.required' => 'Kategori sasaran wajib dipilih.',
        'kategori_pasien.in' => 'Kategori sasaran tidak valid.',

        'tanggal_periksa.required' => 'Tanggal pengukuran wajib diisi.',
        'tanggal_periksa.before_or_equal' => 'Tanggal pengukuran tidak boleh melebihi hari ini.',

        'berat_badan.required' => 'Berat badan wajib diisi.',
        'berat_badan.numeric' => 'Berat badan harus berupa angka.',
        'berat_badan.min' => 'Berat badan tidak valid.',

        'tinggi_badan.required' => 'Tinggi atau panjang badan wajib diisi.',
        'tinggi_badan.numeric' => 'Tinggi atau panjang badan harus berupa angka.',
        'tinggi_badan.min' => 'Tinggi atau panjang badan tidak valid.',

        'lingkar_kepala.required' => 'Lingkar kepala wajib diisi untuk kategori Balita / Anak.',
        'lingkar_lengan.required' => 'LiLA wajib diisi untuk kategori ini.',
        'lingkar_perut.required' => 'Lingkar perut wajib diisi untuk kategori ini.',

        'tekanan_darah.required' => 'Tekanan darah wajib diisi untuk kategori ini.',
        'tekanan_darah.regex' => 'Format tekanan darah harus seperti 120/80.',

        'tingkat_kemandirian.required' => 'Tingkat kemandirian wajib dipilih untuk kategori Lansia.',
        'tingkat_kemandirian.in' => 'Tingkat kemandirian tidak valid.',
    ]);
}

    private function applyStatusFilter($query, string $status): void
    {
        if ($status === 'pending') {
            $query->where(function ($q) {
                $q->whereNull('status_verifikasi')
                    ->orWhere('status_verifikasi', 'pending')
                    ->orWhere('status_verifikasi', 'menunggu')
                    ->orWhere('status_verifikasi', 'menunggu_review');
            });

            return;
        }

        if ($status === 'verified') {
            $query->whereIn('status_verifikasi', $this->reviewedStatuses);

            return;
        }

        if ($status === 'ditolak') {
            $query->whereIn('status_verifikasi', $this->needFixStatuses);

            return;
        }

        $query->where('status_verifikasi', $status);
    }

    private function buildKunjunganPayload(array $validated): array
    {
        $payload = [
            'pasien_id' => $validated['pasien_id'],
            'pasien_type' => $this->mapPasienType($validated['kategori_pasien']),
            'tanggal_kunjungan' => $validated['tanggal_periksa'],
            'keluhan' => $validated['keluhan'] ?? null,
            'jenis_kunjungan' => 'pemeriksaan',
        ];

        if (Schema::hasColumn('kunjungans', 'pemeriksa_id')) {
            $payload['pemeriksa_id'] = Auth::id();
        }

        if (Schema::hasColumn('kunjungans', 'petugas_id')) {
            $payload['petugas_id'] = Auth::id();
        }

        if (Schema::hasColumn('kunjungans', 'created_by')) {
            $payload['created_by'] = Auth::id();
        }

        return $this->filterColumns('kunjungans', $payload);
    }

    private function mapPasienType(string $kategori): string
    {
        return match ($kategori) {
            'balita' => Balita::class,
            'remaja' => Remaja::class,
            'lansia' => Lansia::class,
            default => Balita::class,
        };
    }

    private function mapPasienModel(string $kategori): string
    {
        return match ($kategori) {
            'balita' => Balita::class,
            'remaja' => Remaja::class,
            'lansia' => Lansia::class,
            default => Balita::class,
        };
    }

    private function isValidPasien(string $kategori, int|string $pasienId): bool
    {
        $modelClass = $this->mapPasienModel($kategori);

        return $modelClass::whereKey($pasienId)->exists();
    }

    private function calculateImt($beratBadan, $tinggiBadan, string $kategori): ?float
    {
        if ($kategori === 'balita') {
            return null;
        }

        if (!$beratBadan || !$tinggiBadan) {
            return null;
        }

        $tinggiMeter = ((float) $tinggiBadan) / 100;

        if ($tinggiMeter <= 0) {
            return null;
        }

        return round(((float) $beratBadan) / ($tinggiMeter * $tinggiMeter), 2);
    }

    private function isReviewed(?string $status): bool
    {
        return in_array(strtolower($status ?? 'pending'), $this->reviewedStatuses, true);
    }

    private function needFix(?string $status): bool
    {
        return in_array(strtolower($status ?? 'pending'), $this->needFixStatuses, true);
    }

    private function filterColumns(string $table, array $payload): array
    {
        return collect($payload)
            ->filter(function ($value, $column) use ($table) {
                return Schema::hasColumn($table, $column);
            })
            ->toArray();
    }

    private function errorResponse(Request $request, string $message)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status' => 'error',
                'message' => $message,
            ], 422);
        }

        return back()
            ->withInput()
            ->with('error', $message);
    }
}