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
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class PemeriksaanController extends Controller
{
    private array $kategoriAktif = ['balita', 'remaja', 'lansia'];

    private array $reviewedStatuses = [
        'verified',
        'tervalidasi',
        'approved',
        'disetujui',
    ];

    private array $revisionStatuses = [
        'ditolak',
        'revisi',
        'perlu_revisi',
        'rejected',
        'dikembalikan',
    ];

    public function index(Request $request)
    {
        $kategori = $request->get('kategori');
        $status = $request->get('status');
        $search = trim((string) $request->get('search'));

        $query = Pemeriksaan::with([
                'kunjungan.pasien',
                'kunjungan.petugas',
                'pemeriksa',
                'verifikator',
            ])
            ->whereIn('kategori_pasien', $this->kategoriAktif)
            ->latest('tanggal_periksa')
            ->latest('id');

        if (in_array($kategori, $this->kategoriAktif, true)) {
            $query->where('kategori_pasien', $kategori);
        }

        if ($status) {
            $this->applyStatusFilter($query, $status);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->whereHas('kunjungan', function ($kunjungan) use ($search) {
                    $kunjungan->whereHasMorph(
                        'pasien',
                        [Balita::class, Remaja::class, Lansia::class],
                        function ($pasien) use ($search) {
                            $pasien->where('nama_lengkap', 'like', "%{$search}%")
                                ->orWhere('nik', 'like', "%{$search}%");
                        }
                    );
                });
            });
        }

        $pemeriksaans = $query->paginate(5)->withQueryString();

        return view('kader.pemeriksaan.index', compact(
            'pemeriksaans',
            'kategori',
            'status',
            'search'
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
        $validated = $this->validatedData($request);
        $kategori = $validated['kategori_pasien'];
        $pasienId = (int) $validated['pasien_id'];

        if (!$this->pasienExists($kategori, $pasienId)) {
            return back()
                ->withInput()
                ->with('error', 'Data sasaran tidak valid.');
        }

        try {
            $this->guardRequiredColumns($validated);

            DB::transaction(function () use ($validated, $kategori, $pasienId) {
                $kunjungan = $this->createKunjungan($validated);

                $payload = $this->pemeriksaanPayload(
                    validated: $validated,
                    kategori: $kategori,
                    pasienId: $pasienId,
                    kunjunganId: $kunjungan->id
                );

                $this->storeModel(new Pemeriksaan(), $payload);
            });

            return redirect()
                ->route('kader.pemeriksaan.index')
                ->with('success', 'Pengukuran fisik berhasil disimpan dan menunggu review Bidan.');
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    public function show(Pemeriksaan $pemeriksaan)
    {
        $pemeriksaan->load([
            'kunjungan.pasien',
            'kunjungan.petugas',
            'pemeriksa',
            'verifikator',
        ]);

        return view('kader.pemeriksaan.show', compact('pemeriksaan'));
    }

    public function edit(Pemeriksaan $pemeriksaan)
    {
        if ($this->isReviewed($pemeriksaan->status_verifikasi)) {
            return back()->with('error', 'Data sudah ditinjau Bidan dan tidak dapat diubah.');
        }

        $pemeriksaan->load(['kunjungan.pasien', 'pemeriksa']);

        return view('kader.pemeriksaan.edit', compact('pemeriksaan'));
    }

    public function update(Request $request, Pemeriksaan $pemeriksaan)
    {
        if ($this->isReviewed($pemeriksaan->status_verifikasi)) {
            return $this->failed($request, 'Data sudah ditinjau Bidan dan tidak dapat diubah.');
        }

        $kategori = $pemeriksaan->kategori_pasien;

        if (!in_array($kategori, $this->kategoriAktif, true)) {
            return $this->failed($request, 'Kategori pemeriksaan tidak valid.');
        }

        $validated = $this->validatedData($request, false, $kategori);

        try {
            $this->guardRequiredColumns($validated);

            DB::transaction(function () use ($validated, $kategori, $pemeriksaan) {
                $payload = $this->pemeriksaanPayload(
                    validated: $validated,
                    kategori: $kategori,
                    pasienId: (int) $pemeriksaan->pasien_id
                );

                if ($this->needsRevision($pemeriksaan->status_verifikasi)) {
                    $payload['status_verifikasi'] = 'pending';
                    $payload = array_merge($payload, $this->resetReviewPayload());
                }

                $this->storeModel($pemeriksaan, $payload);

                if ($pemeriksaan->kunjungan) {
                    $this->storeModel($pemeriksaan->kunjungan, [
                        'tanggal_kunjungan' => $validated['tanggal_periksa'],
                        'keluhan' => $validated['keluhan'] ?? null,
                    ]);
                }
            });

            $message = 'Pengukuran fisik berhasil diperbarui.';

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
            return $this->failed($request, 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }

    public function destroy(Pemeriksaan $pemeriksaan)
    {
        if ($this->isReviewed($pemeriksaan->status_verifikasi)) {
            return back()->with('error', 'Data sudah ditinjau Bidan dan tidak dapat dihapus.');
        }

        DB::transaction(function () use ($pemeriksaan) {
            $kunjungan = $pemeriksaan->kunjungan;

            $pemeriksaan->delete();

            if ($kunjungan && !$kunjungan->pemeriksaan()->exists()) {
                $hasImunisasi = method_exists($kunjungan, 'imunisasis')
                    ? $kunjungan->imunisasis()->exists()
                    : false;

                if (!$hasImunisasi) {
                    $kunjungan->delete();
                }
            }
        });

        return redirect()
            ->route('kader.pemeriksaan.index')
            ->with('success', 'Data pengukuran fisik berhasil dihapus.');
    }

    public function getPasienApi(Request $request)
    {
        $kategori = $request->get('kategori');

        if (!in_array($kategori, $this->kategoriAktif, true)) {
            return response()->json([
                'status' => 'error',
                'data' => [],
                'message' => 'Kategori tidak valid.',
            ], 422);
        }

        $model = $this->pasienModel($kategori);
        $table = (new $model())->getTable();

        $columns = collect(['id', 'nama_lengkap', 'nik', 'jenis_kelamin', 'tanggal_lahir'])
            ->filter(fn ($column) => Schema::hasColumn($table, $column))
            ->values()
            ->all();

        $data = $model::select($columns)
            ->orderBy(Schema::hasColumn($table, 'nama_lengkap') ? 'nama_lengkap' : 'id')
            ->get()
            ->map(fn ($item) => [
                'id' => $item->id,
                'nama' => $item->nama_lengkap ?? 'Tanpa Nama',
                'nik' => $item->nik ?? '-',
                'jenis_kelamin' => $item->jenis_kelamin ?? null,
                'tanggal_lahir' => $item->tanggal_lahir ?? null,
            ]);

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }

    private function validatedData(Request $request, bool $isStore = true, ?string $kategoriExisting = null): array
    {
        $kategori = $isStore ? $request->input('kategori_pasien') : $kategoriExisting;

        $rules = [
            'tanggal_periksa' => 'required|date|before_or_equal:today',
            'berat_badan' => 'required|numeric|min:0.1|max:300',
            'tinggi_badan' => 'required|numeric|min:10|max:250',
            'suhu_tubuh' => 'nullable|numeric|min:30|max:45',
            'lingkar_kepala' => 'nullable|numeric|min:10|max:100',
            'lingkar_lengan' => 'nullable|numeric|min:5|max:100',
            'lingkar_perut' => 'nullable|numeric|min:20|max:200',
            'tekanan_darah' => ['nullable', 'string', 'max:20', 'regex:/^[0-9]{2,3}\/[0-9]{2,3}$/'],
            'denyut_nadi' => 'nullable|integer|min:20|max:250',
            'respirasi' => 'nullable|integer|min:5|max:80',
            'gula_darah' => 'nullable|numeric|min:10|max:1000',
            'kolesterol' => 'nullable|integer|min:10|max:1000',
            'asam_urat' => 'nullable|numeric|min:1|max:30',
            'hemoglobin' => 'nullable|numeric|min:1|max:30',
            'tingkat_kemandirian' => 'nullable|in:mandiri,bantuan_sebagian,bantuan_penuh',
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
            $rules['tekanan_darah'] = ['required', 'string', 'max:20', 'regex:/^[0-9]{2,3}\/[0-9]{2,3}$/'];
        }

        if ($kategori === 'lansia') {
            $rules['lingkar_perut'] = 'required|numeric|min:20|max:200';
            $rules['tekanan_darah'] = ['required', 'string', 'max:20', 'regex:/^[0-9]{2,3}\/[0-9]{2,3}$/'];
            $rules['tingkat_kemandirian'] = 'required|in:mandiri,bantuan_sebagian,bantuan_penuh';
        }

        return $request->validate($rules, [
            'pasien_id.required' => 'Sasaran wajib dipilih.',
            'kategori_pasien.required' => 'Kategori sasaran wajib dipilih.',
            'tanggal_periksa.required' => 'Tanggal pengukuran wajib diisi.',
            'berat_badan.required' => 'Berat badan wajib diisi.',
            'tinggi_badan.required' => 'Tinggi badan wajib diisi.',
            'lingkar_kepala.required' => 'Lingkar kepala wajib diisi untuk Balita.',
            'lingkar_lengan.required' => 'LiLA wajib diisi untuk kategori ini.',
            'lingkar_perut.required' => 'Lingkar perut wajib diisi untuk kategori ini.',
            'tekanan_darah.required' => 'Tekanan darah wajib diisi untuk kategori ini.',
            'tekanan_darah.regex' => 'Format tekanan darah harus seperti 120/80.',
            'tingkat_kemandirian.required' => 'Tingkat kemandirian wajib dipilih untuk Lansia.',
        ]);
    }

    private function pemeriksaanPayload(array $validated, string $kategori, int $pasienId, ?int $kunjunganId = null): array
    {
        $payload = [
            'kunjungan_id' => $kunjunganId,
            'pasien_id' => $pasienId,
            'kategori_pasien' => $kategori,
            'tanggal_periksa' => $validated['tanggal_periksa'],
            'berat_badan' => $validated['berat_badan'] ?? null,
            'tinggi_badan' => $validated['tinggi_badan'] ?? null,
            'imt' => $this->hitungImt($validated['berat_badan'] ?? null, $validated['tinggi_badan'] ?? null, $kategori),
            'suhu_tubuh' => $validated['suhu_tubuh'] ?? null,
            'lingkar_kepala' => $validated['lingkar_kepala'] ?? null,
            'lingkar_lengan' => $validated['lingkar_lengan'] ?? null,
            'lingkar_perut' => $validated['lingkar_perut'] ?? null,
            'tekanan_darah' => $validated['tekanan_darah'] ?? null,
            'denyut_nadi' => $validated['denyut_nadi'] ?? null,
            'respirasi' => $validated['respirasi'] ?? null,
            'gula_darah' => $validated['gula_darah'] ?? null,
            'kolesterol' => $validated['kolesterol'] ?? null,
            'asam_urat' => $validated['asam_urat'] ?? null,
            'hemoglobin' => $validated['hemoglobin'] ?? null,
            'tingkat_kemandirian' => $validated['tingkat_kemandirian'] ?? null,
            'keluhan' => $validated['keluhan'] ?? null,
            'catatan_kader' => $validated['catatan_kader'] ?? null,
            'pemeriksa_id' => Auth::id(),
            'status_verifikasi' => 'pending',
        ];

        if (Schema::hasColumn('pemeriksaans', 'created_by')) {
            $payload['created_by'] = Auth::id();
        }

        return $payload;
    }

    private function createKunjungan(array $validated): Kunjungan
    {
        $payload = [
            'pasien_id' => $validated['pasien_id'],
            'pasien_type' => $this->pasienModel($validated['kategori_pasien']),
            'tanggal_kunjungan' => $validated['tanggal_periksa'],
            'jenis_kunjungan' => 'pemeriksaan',
            'keluhan' => $validated['keluhan'] ?? null,
            'petugas_id' => Auth::id(),
        ];

        return tap(new Kunjungan(), fn ($model) => $this->storeModel($model, $payload));
    }

    private function storeModel($model, array $payload): void
    {
        foreach ($payload as $column => $value) {
            // Cukup pastikan kolomnya ada di database
            if (Schema::hasColumn($model->getTable(), $column)) {
                $model->{$column} = $value;
            }
        }
        $model->save();
    }
    private function guardRequiredColumns(array $validated): void
    {
        $required = ['pasien_id', 'kategori_pasien', 'tanggal_periksa', 'pemeriksa_id', 'berat_badan', 'tinggi_badan'];
        $filledFields = ['lingkar_perut', 'catatan_kader', 'hemoglobin', 'gula_darah', 'kolesterol', 'asam_urat'];

        $missing = collect($required)
            ->merge(collect($filledFields)->filter(fn ($field) => filled($validated[$field] ?? null)))
            ->reject(fn ($field) => Schema::hasColumn('pemeriksaans', $field))
            ->values()
            ->all();

        if ($missing) {
            throw new RuntimeException('Kolom pemeriksaans belum tersedia: ' . implode(', ', $missing));
        }
    }

    private function applyStatusFilter($query, string $status): void
    {
        if ($status === 'pending') {
            $query->where(function ($q) {
                $q->whereNull('status_verifikasi')
                    ->orWhereIn('status_verifikasi', ['pending', 'menunggu', 'menunggu_review']);
            });

            return;
        }

        if ($status === 'verified') {
            $query->whereIn('status_verifikasi', $this->reviewedStatuses);
            return;
        }

        if ($status === 'ditolak') {
            $query->whereIn('status_verifikasi', $this->revisionStatuses);
            return;
        }

        $query->where('status_verifikasi', $status);
    }

    private function resetReviewPayload(): array
    {
        return [
            'verified_by' => null,
            'verified_at' => null,
            'catatan_validasi' => null,
            'catatan_bidan' => null,
        ];
    }

    private function pasienModel(string $kategori): string
    {
        return match ($kategori) {
            'balita' => Balita::class,
            'remaja' => Remaja::class,
            'lansia' => Lansia::class,
            default => Balita::class,
        };
    }

    private function pasienExists(string $kategori, int $id): bool
    {
        return $this->pasienModel($kategori)::whereKey($id)->exists();
    }

    private function hitungImt($berat, $tinggi, string $kategori): ?float
    {
        if ($kategori === 'balita' || !$berat || !$tinggi) {
            return null;
        }

        $meter = ((float) $tinggi) / 100;

        return $meter > 0 ? round(((float) $berat) / ($meter * $meter), 2) : null;
    }

    private function isReviewed(?string $status): bool
    {
        return in_array(strtolower($status ?? 'pending'), $this->reviewedStatuses, true);
    }

    private function needsRevision(?string $status): bool
    {
        return in_array(strtolower($status ?? 'pending'), $this->revisionStatuses, true);
    }

    private function failed(Request $request, string $message)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status' => 'error',
                'message' => $message,
            ], 422);
        }

        return back()->withInput()->with('error', $message);
    }
}