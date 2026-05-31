<?php

namespace App\Http\Controllers\Bidan;

use App\Http\Controllers\Controller;
use App\Models\Balita;
use App\Models\Lansia;
use App\Models\Pemeriksaan;
use App\Models\Remaja;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class PemeriksaanController extends Controller
{
    private array $columnCache = [];

    public function index(Request $request): View|RedirectResponse
    {
        try {
            $tab = $this->normalizeTab($request->get('tab', 'pending'));
            $kategori = $this->normalizeKategori($request->get('kategori', 'semua'));
            $search = $this->cleanSearch($request->get('search', ''));

            $query = Pemeriksaan::query()
                ->with([
                    'kunjungan.pasien',
                    'pemeriksa',
                    'verifikator',
                    'verifikatorLegacy',
                    'balita',
                    'remaja',
                    'lansia',
                ]);

            $this->applyTargetSasaran($query);
            $this->applyTabFilter($query, $tab);
            $this->applyKategoriFilter($query, $kategori);
            $this->applySearch($query, $search);
            $this->applyLatestOrder($query);

            $pemeriksaans = $query
                ->paginate(10)
                ->withQueryString();

            $stats = $this->stats();
            $kategoriOptions = $this->kategoriOptions();

            return view('bidan.pemeriksaan.index', compact(
                'pemeriksaans',
                'tab',
                'kategori',
                'search',
                'stats',
                'kategoriOptions'
            ));
        } catch (\Throwable $e) {
            Log::error('BIDAN_PEMERIKSAAN_INDEX_ERROR', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return redirect()
                ->route('bidan.dashboard')
                ->with('error', 'Gagal memuat data pemeriksaan klinis.');
        }
    }

    public function create(): RedirectResponse
    {
        return redirect()
            ->route('bidan.pemeriksaan.index')
            ->with('info', 'Data pemeriksaan awal dibuat oleh Kader. Bidan meninjau dan memvalidasi data yang sudah masuk.');
    }

    public function store(Request $request): RedirectResponse
    {
        return redirect()
            ->route('bidan.pemeriksaan.index')
            ->with('info', 'Data pemeriksaan baru dibuat melalui modul Kader. Bidan melakukan validasi pada data yang sudah tercatat.');
    }

    public function validasi(int|string $id): View|RedirectResponse
    {
        try {
            $pemeriksaan = $this->findPemeriksaan($id);
            $pasien = $this->pasienFromPemeriksaan($pemeriksaan);

            if (!$pasien) {
                return redirect()
                    ->route('bidan.pemeriksaan.index')
                    ->with('error', 'Data sasaran tidak ditemukan pada pemeriksaan ini.');
            }

            if ($this->isVerified($pemeriksaan)) {
                return redirect()
                    ->route('bidan.pemeriksaan.show', $pemeriksaan->id)
                    ->with('info', 'Pemeriksaan ini sudah tervalidasi dan ditampilkan sebagai arsip.');
            }

            $kategori = $this->normalizeKategori(data_get($pemeriksaan, 'kategori_pasien', ''));
            $parameter = $this->parameterPemeriksaan($pemeriksaan, $kategori);
            $ringkasan = $this->ringkasanPemeriksaan($pemeriksaan, $pasien, $kategori);

            return view('bidan.pemeriksaan.validasi', compact(
                'pemeriksaan',
                'pasien',
                'kategori',
                'parameter',
                'ringkasan'
            ));
        } catch (\Throwable $e) {
            Log::error('BIDAN_PEMERIKSAAN_VALIDASI_ERROR', [
                'message' => $e->getMessage(),
                'pemeriksaan_id' => $id,
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return redirect()
                ->route('bidan.pemeriksaan.index')
                ->with('error', 'Gagal membuka halaman validasi pemeriksaan.');
        }
    }

    public function simpanValidasi(Request $request, int|string $id): RedirectResponse
    {
        $validated = $request->validate([
            'status_gizi' => ['nullable', 'string', 'max:100'],
            'kesimpulan_pemeriksaan' => ['nullable', 'string', 'max:1000'],
            'diagnosa' => ['nullable', 'string', 'max:1000'],
            'tindakan' => ['nullable', 'string', 'max:1000'],
            'catatan_edukasi' => ['nullable', 'string', 'max:1000'],
            'catatan_bidan' => ['nullable', 'string', 'max:1000'],
        ], [
            'status_gizi.max' => 'Status ringkas maksimal 100 karakter.',
            'kesimpulan_pemeriksaan.max' => 'Kesimpulan pemeriksaan maksimal 1000 karakter.',
            'diagnosa.max' => 'Kesimpulan pemeriksaan maksimal 1000 karakter.',
            'tindakan.max' => 'Tindakan atau layanan maksimal 1000 karakter.',
            'catatan_edukasi.max' => 'Edukasi maksimal 1000 karakter.',
            'catatan_bidan.max' => 'Catatan Bidan maksimal 1000 karakter.',
        ]);

        DB::beginTransaction();

        try {
            $pemeriksaan = Pemeriksaan::query()
                ->with(['kunjungan.pasien'])
                ->findOrFail($id);

            if ($this->isVerified($pemeriksaan)) {
                DB::rollBack();

                return redirect()
                    ->route('bidan.pemeriksaan.show', $pemeriksaan->id)
                    ->with('info', 'Pemeriksaan ini sudah tervalidasi.');
            }

            $kesimpulan = $this->firstFilled([
                $validated['kesimpulan_pemeriksaan'] ?? null,
                $validated['diagnosa'] ?? null,
                'Pemeriksaan telah divalidasi oleh Bidan.',
            ]);

            $tindakan = $this->firstFilled([
                $validated['tindakan'] ?? null,
                '-',
            ]);

            $edukasi = $this->firstFilled([
                $validated['catatan_edukasi'] ?? null,
                '-',
            ]);

            $catatanBidan = $this->firstFilled([
                $validated['catatan_bidan'] ?? null,
                $kesimpulan,
            ]);

            $payload = [
                'imt' => $this->hitungImt(
                    data_get($pemeriksaan, 'berat_badan'),
                    data_get($pemeriksaan, 'tinggi_badan'),
                    data_get($pemeriksaan, 'imt')
                ),
                'status_gizi' => $validated['status_gizi'] ?? data_get($pemeriksaan, 'status_gizi'),
                'diagnosa' => $kesimpulan,
                'kesimpulan_pemeriksaan' => $kesimpulan,
                'tindakan' => $tindakan,
                'edukasi' => $edukasi,
                'catatan_edukasi' => $edukasi,
                'catatan_bidan' => $catatanBidan,
                'status_verifikasi' => 'verified',
                'tanggal_periksa' => now(),
            ];

            $payload = $this->lengkapiKolomVerifikasi($payload);
            $payload = $this->onlyExistingColumns($this->pemeriksaanTable(), $payload);

            $pemeriksaan->forceFill($payload)->save();

            DB::commit();

            return redirect()
                ->route('bidan.pemeriksaan.show', $pemeriksaan->id)
                ->with('success', 'Pemeriksaan berhasil divalidasi dan masuk ke arsip rekam medis.');
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('BIDAN_PEMERIKSAAN_SIMPAN_VALIDASI_ERROR', [
                'message' => $e->getMessage(),
                'pemeriksaan_id' => $id,
                'user_id' => Auth::id(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Gagal menyimpan validasi pemeriksaan.');
        }
    }

    public function show(int|string $id): View|RedirectResponse
    {
        try {
            $pemeriksaan = $this->findPemeriksaan($id);
            $pasien = $this->pasienFromPemeriksaan($pemeriksaan);

            if (!$pasien) {
                return redirect()
                    ->route('bidan.pemeriksaan.index')
                    ->with('error', 'Data sasaran tidak ditemukan pada pemeriksaan ini.');
            }

            $kategori = $this->normalizeKategori(data_get($pemeriksaan, 'kategori_pasien', ''));
            $parameter = $this->parameterPemeriksaan($pemeriksaan, $kategori);
            $ringkasan = $this->ringkasanPemeriksaan($pemeriksaan, $pasien, $kategori);

            return view('bidan.pemeriksaan.show', compact(
                'pemeriksaan',
                'pasien',
                'kategori',
                'parameter',
                'ringkasan'
            ));
        } catch (\Throwable $e) {
            Log::error('BIDAN_PEMERIKSAAN_SHOW_ERROR', [
                'message' => $e->getMessage(),
                'pemeriksaan_id' => $id,
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return redirect()
                ->route('bidan.pemeriksaan.index')
                ->with('error', 'Data pemeriksaan tidak ditemukan.');
        }
    }

    public function edit(int|string $id): View|RedirectResponse
    {
        return $this->validasi($id);
    }

    public function update(Request $request, int|string $id): RedirectResponse
    {
        return $this->simpanValidasi($request, $id);
    }

    public function verifikasi(Request $request, int|string $id): RedirectResponse
    {
        try {
            $pemeriksaan = Pemeriksaan::query()->findOrFail($id);

            $request->merge([
                'status_gizi' => $request->input('status_gizi', data_get($pemeriksaan, 'status_gizi')),
                'kesimpulan_pemeriksaan' => $request->input(
                    'kesimpulan_pemeriksaan',
                    data_get($pemeriksaan, 'diagnosa') ?: 'Pemeriksaan telah divalidasi oleh Bidan.'
                ),
                'tindakan' => $request->input('tindakan', data_get($pemeriksaan, 'tindakan') ?: '-'),
                'catatan_edukasi' => $request->input('catatan_edukasi', data_get($pemeriksaan, 'edukasi') ?: '-'),
            ]);

            return $this->simpanValidasi($request, $id);
        } catch (\Throwable $e) {
            Log::error('BIDAN_PEMERIKSAAN_VERIFIKASI_ERROR', [
                'message' => $e->getMessage(),
                'pemeriksaan_id' => $id,
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return back()->with('error', 'Gagal memvalidasi pemeriksaan.');
        }
    }

    public function destroy(int|string $id): RedirectResponse
    {
        try {
            $pemeriksaan = Pemeriksaan::query()->findOrFail($id);

            if ($this->isVerified($pemeriksaan)) {
                return back()
                    ->with('error', 'Pemeriksaan yang sudah tervalidasi tidak dapat dihapus dari modul pemeriksaan.');
            }

            $pemeriksaan->delete();

            return redirect()
                ->route('bidan.pemeriksaan.index')
                ->with('success', 'Data pemeriksaan menunggu validasi berhasil dihapus.');
        } catch (\Throwable $e) {
            Log::error('BIDAN_PEMERIKSAAN_DESTROY_ERROR', [
                'message' => $e->getMessage(),
                'pemeriksaan_id' => $id,
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return back()->with('error', 'Gagal menghapus data pemeriksaan.');
        }
    }

    private function findPemeriksaan(int|string $id): Pemeriksaan
    {
        return Pemeriksaan::query()
            ->with([
                'kunjungan.pasien',
                'kunjungan.petugas',
                'pemeriksa',
                'verifikator',
                'verifikatorLegacy',
                'balita',
                'remaja',
                'lansia',
            ])
            ->findOrFail($id);
    }

    private function applyTargetSasaran(Builder $query): void
    {
        $kategoriList = array_keys($this->kategoriOptions());

        $query->where(function (Builder $q) use ($kategoriList) {
            if ($this->hasColumn($this->pemeriksaanTable(), 'kategori_pasien')) {
                $q->whereIn('kategori_pasien', $kategoriList);
            }

            $q->orWhereHas('kunjungan', function (Builder $kunjungan) {
                $kunjungan->whereIn('pasien_type', $this->allowedMorphTypes());
            });

            foreach (['balita_id', 'remaja_id', 'lansia_id'] as $column) {
                if ($this->hasColumn($this->pemeriksaanTable(), $column)) {
                    $q->orWhereNotNull($column);
                }
            }
        });
    }

    private function applyTabFilter(Builder $query, string $tab): void
    {
        if ($tab === 'verified') {
            $query->whereIn('status_verifikasi', $this->verifiedStatuses());
            return;
        }

        $query->where(function (Builder $q) {
            $q->whereNull('status_verifikasi')
                ->orWhere('status_verifikasi', '')
                ->orWhereIn('status_verifikasi', $this->pendingStatuses());
        });
    }

    private function applyKategoriFilter(Builder $query, string $kategori): void
    {
        if ($kategori === 'semua') {
            return;
        }

        if ($this->hasColumn($this->pemeriksaanTable(), 'kategori_pasien')) {
            $query->where('kategori_pasien', $kategori);
        }
    }

    private function applySearch(Builder $query, string $search): void
    {
        if ($search === '') {
            return;
        }

        $isNumeric = preg_match('/^[0-9]+$/', $search) === 1;

        $query->where(function (Builder $q) use ($search, $isNumeric) {
            $q->whereHas('kunjungan', function (Builder $kunjungan) use ($search, $isNumeric) {
                $kunjungan->whereHasMorph('pasien', [
                    Balita::class,
                    Remaja::class,
                    Lansia::class,
                ], function (Builder $pasien) use ($search, $isNumeric) {
                    $this->applyPatientSearch($pasien, $search, $isNumeric);
                });
            });

            $this->applyDirectPatientSearch($q, $search, $isNumeric);
        });
    }

    private function applyPatientSearch(Builder $query, string $search, bool $isNumeric): void
    {
        $table = $query->getModel()->getTable();

        $query->where(function (Builder $q) use ($table, $search, $isNumeric) {
            if ($isNumeric) {
                foreach (['nik', 'nik_anak', 'no_kk'] as $column) {
                    if ($this->hasColumn($table, $column)) {
                        $q->orWhere($column, 'like', '%' . $search . '%');
                    }
                }

                return;
            }

            foreach (['nama_lengkap', 'nama', 'nama_balita', 'nama_remaja', 'nama_lansia'] as $column) {
                if ($this->hasColumn($table, $column)) {
                    $q->orWhere($column, 'like', '%' . $search . '%');
                }
            }
        });
    }

    private function applyDirectPatientSearch(Builder $query, string $search, bool $isNumeric): void
    {
        $targets = [
            'balita' => [
                'model' => Balita::class,
                'direct_column' => 'balita_id',
            ],
            'remaja' => [
                'model' => Remaja::class,
                'direct_column' => 'remaja_id',
            ],
            'lansia' => [
                'model' => Lansia::class,
                'direct_column' => 'lansia_id',
            ],
        ];

        foreach ($targets as $kategori => $target) {
            $model = $target['model'];
            $modelInstance = new $model();
            $modelTable = $modelInstance->getTable();

            $ids = $model::query()
                ->where(function (Builder $patient) use ($search, $isNumeric, $modelTable) {
                    if ($isNumeric) {
                        foreach (['nik', 'nik_anak', 'no_kk'] as $column) {
                            if ($this->hasColumn($modelTable, $column)) {
                                $patient->orWhere($column, 'like', '%' . $search . '%');
                            }
                        }

                        return;
                    }

                    foreach (['nama_lengkap', 'nama', 'nama_balita', 'nama_remaja', 'nama_lansia'] as $column) {
                        if ($this->hasColumn($modelTable, $column)) {
                            $patient->orWhere($column, 'like', '%' . $search . '%');
                        }
                    }
                })
                ->limit(300)
                ->pluck('id');

            if ($ids->isEmpty()) {
                continue;
            }

            if ($this->hasColumn($this->pemeriksaanTable(), $target['direct_column'])) {
                $query->orWhereIn($target['direct_column'], $ids);
            }

            if (
                $this->hasColumn($this->pemeriksaanTable(), 'pasien_id')
                && $this->hasColumn($this->pemeriksaanTable(), 'kategori_pasien')
            ) {
                $query->orWhere(function (Builder $pemeriksaan) use ($ids, $kategori) {
                    $pemeriksaan
                        ->where('kategori_pasien', $kategori)
                        ->whereIn('pasien_id', $ids);
                });
            }
        }
    }

    private function applyLatestOrder(Builder $query): void
    {
        if ($this->hasColumn($this->pemeriksaanTable(), 'tanggal_periksa')) {
            $query->orderByDesc('tanggal_periksa');
        }

        if ($this->hasColumn($this->pemeriksaanTable(), 'created_at')) {
            $query->orderByDesc('created_at');
        }

        $query->orderByDesc('id');
    }

    private function stats(): array
    {
        $base = Pemeriksaan::query();
        $this->applyTargetSasaran($base);

        $pending = clone $base;
        $verified = clone $base;
        $bulanIni = clone $base;

        $kategoriCounts = [];

        foreach (array_keys($this->kategoriOptions()) as $kategori) {
            $kategoriQuery = clone $base;
            $this->applyKategoriFilter($kategoriQuery, $kategori);

            $kategoriCounts[$kategori] = $kategoriQuery->count();
        }

        if ($this->hasColumn($this->pemeriksaanTable(), 'tanggal_periksa')) {
            $bulanIni
                ->whereMonth('tanggal_periksa', now()->month)
                ->whereYear('tanggal_periksa', now()->year);
        } elseif ($this->hasColumn($this->pemeriksaanTable(), 'created_at')) {
            $bulanIni
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year);
        } else {
            $bulanIni->whereRaw('1 = 0');
        }

        return [
            'total' => (clone $base)->count(),
            'pending' => $pending
                ->where(function (Builder $q) {
                    $q->whereNull('status_verifikasi')
                        ->orWhere('status_verifikasi', '')
                        ->orWhereIn('status_verifikasi', $this->pendingStatuses());
                })
                ->count(),
            'verified' => $verified
                ->whereIn('status_verifikasi', $this->verifiedStatuses())
                ->count(),
            'bulan_ini' => $bulanIni->count(),
            'kategori' => $kategoriCounts,
        ];
    }

    private function pasienFromPemeriksaan(Pemeriksaan $pemeriksaan): ?object
    {
        $pasien = data_get($pemeriksaan, 'kunjungan.pasien');

        if ($pasien) {
            return $pasien;
        }

        $kategori = $this->normalizeKategori(data_get($pemeriksaan, 'kategori_pasien', ''));

        if ($kategori === 'balita' && data_get($pemeriksaan, 'balita')) {
            return data_get($pemeriksaan, 'balita');
        }

        if ($kategori === 'remaja' && data_get($pemeriksaan, 'remaja')) {
            return data_get($pemeriksaan, 'remaja');
        }

        if ($kategori === 'lansia' && data_get($pemeriksaan, 'lansia')) {
            return data_get($pemeriksaan, 'lansia');
        }

        return null;
    }

    private function parameterPemeriksaan(Pemeriksaan $pemeriksaan, string $kategori): array
    {
        if ($kategori === 'lansia') {
            return [
                ['label' => 'Berat Badan', 'value' => $this->displayValue(data_get($pemeriksaan, 'berat_badan'), 'kg'), 'icon' => 'ph-scales'],
                ['label' => 'Tinggi Badan', 'value' => $this->displayValue(data_get($pemeriksaan, 'tinggi_badan'), 'cm'), 'icon' => 'ph-ruler'],
                ['label' => 'IMT', 'value' => $this->displayValue($this->hitungImt(data_get($pemeriksaan, 'berat_badan'), data_get($pemeriksaan, 'tinggi_badan'), data_get($pemeriksaan, 'imt'))), 'icon' => 'ph-chart-line-up'],
                ['label' => 'Tekanan Darah', 'value' => $this->displayValue(data_get($pemeriksaan, 'tekanan_darah')), 'icon' => 'ph-heartbeat'],
                ['label' => 'Gula Darah', 'value' => $this->displayValue(data_get($pemeriksaan, 'gula_darah'), 'mg/dL'), 'icon' => 'ph-drop'],
                ['label' => 'Kolesterol', 'value' => $this->displayValue(data_get($pemeriksaan, 'kolesterol'), 'mg/dL'), 'icon' => 'ph-waveform'],
                ['label' => 'Asam Urat', 'value' => $this->displayValue(data_get($pemeriksaan, 'asam_urat'), 'mg/dL'), 'icon' => 'ph-flask'],
                ['label' => 'Lingkar Perut', 'value' => $this->displayValue(data_get($pemeriksaan, 'lingkar_perut'), 'cm'), 'icon' => 'ph-ruler'],
                ['label' => 'Kemandirian', 'value' => $this->displayValue(data_get($pemeriksaan, 'tingkat_kemandirian')), 'icon' => 'ph-person-simple-walk'],
            ];
        }

        if ($kategori === 'remaja') {
            return [
                ['label' => 'Berat Badan', 'value' => $this->displayValue(data_get($pemeriksaan, 'berat_badan'), 'kg'), 'icon' => 'ph-scales'],
                ['label' => 'Tinggi Badan', 'value' => $this->displayValue(data_get($pemeriksaan, 'tinggi_badan'), 'cm'), 'icon' => 'ph-ruler'],
                ['label' => 'IMT', 'value' => $this->displayValue($this->hitungImt(data_get($pemeriksaan, 'berat_badan'), data_get($pemeriksaan, 'tinggi_badan'), data_get($pemeriksaan, 'imt'))), 'icon' => 'ph-chart-line-up'],
                ['label' => 'Tekanan Darah', 'value' => $this->displayValue(data_get($pemeriksaan, 'tekanan_darah')), 'icon' => 'ph-heartbeat'],
                ['label' => 'LILA', 'value' => $this->displayValue(data_get($pemeriksaan, 'lingkar_lengan') ?? data_get($pemeriksaan, 'lila'), 'cm'), 'icon' => 'ph-ruler'],
                ['label' => 'Hemoglobin', 'value' => $this->displayValue(data_get($pemeriksaan, 'hb'), 'g/dL'), 'icon' => 'ph-drop'],
            ];
        }

        return [
            ['label' => 'Berat Badan', 'value' => $this->displayValue(data_get($pemeriksaan, 'berat_badan'), 'kg'), 'icon' => 'ph-scales'],
            ['label' => 'Tinggi Badan', 'value' => $this->displayValue(data_get($pemeriksaan, 'tinggi_badan'), 'cm'), 'icon' => 'ph-ruler'],
            ['label' => 'Lingkar Kepala', 'value' => $this->displayValue(data_get($pemeriksaan, 'lingkar_kepala'), 'cm'), 'icon' => 'ph-circle'],
            ['label' => 'LILA', 'value' => $this->displayValue(data_get($pemeriksaan, 'lingkar_lengan') ?? data_get($pemeriksaan, 'lila'), 'cm'), 'icon' => 'ph-ruler'],
            ['label' => 'Status Gizi', 'value' => $this->displayValue(data_get($pemeriksaan, 'status_gizi')), 'icon' => 'ph-bowl-food'],
        ];
    }

    private function ringkasanPemeriksaan(Pemeriksaan $pemeriksaan, object $pasien, string $kategori): array
    {
        return [
            'kategori' => $kategori,
            'kategori_label' => $this->kategoriOptions()[$kategori]['label'] ?? ucfirst($kategori),
            'nama_pasien' => data_get($pasien, 'nama_lengkap') ?? data_get($pasien, 'nama') ?? 'Nama tidak tersedia',
            'nik_pasien' => data_get($pasien, 'nik') ?? data_get($pasien, 'nik_anak') ?? '-',
            'tanggal_kunjungan' => $this->formatDate(
                data_get($pemeriksaan, 'tanggal_kunjungan')
                ?? data_get($pemeriksaan, 'kunjungan.tanggal_kunjungan')
                ?? data_get($pemeriksaan, 'created_at')
            ),
            'tanggal_periksa' => $this->formatDate(
                data_get($pemeriksaan, 'tanggal_periksa')
                ?? data_get($pemeriksaan, 'updated_at')
                ?? data_get($pemeriksaan, 'created_at')
            ),
            'status_verifikasi' => $this->statusLabel(data_get($pemeriksaan, 'status_verifikasi')),
            'petugas_input' => data_get($pemeriksaan, 'pemeriksa.name')
                ?? data_get($pemeriksaan, 'pemeriksa.nama')
                ?? data_get($pemeriksaan, 'kunjungan.petugas.name')
                ?? data_get($pemeriksaan, 'kunjungan.petugas.nama')
                ?? '-',
            'bidan_validasi' => data_get($pemeriksaan, 'verifikator.name')
                ?? data_get($pemeriksaan, 'verifikator.nama')
                ?? data_get($pemeriksaan, 'verifikatorLegacy.name')
                ?? data_get($pemeriksaan, 'verifikatorLegacy.nama')
                ?? '-',
        ];
    }

    private function hitungImt(mixed $beratBadan, mixed $tinggiBadan, mixed $imtSaatIni = null): ?float
    {
        if ($imtSaatIni !== null && $imtSaatIni !== '' && (float) $imtSaatIni > 0) {
            return round((float) $imtSaatIni, 2);
        }

        $bb = (float) ($beratBadan ?? 0);
        $tb = (float) ($tinggiBadan ?? 0);

        if ($bb <= 0 || $tb <= 0) {
            return null;
        }

        return round($bb / (($tb / 100) ** 2), 2);
    }

    private function lengkapiKolomVerifikasi(array $payload): array
    {
        $table = $this->pemeriksaanTable();

        if ($this->hasColumn($table, 'verified_by')) {
            $payload['verified_by'] = Auth::id();
        }

        if ($this->hasColumn($table, 'verified_at')) {
            $payload['verified_at'] = now();
        }

        if ($this->hasColumn($table, 'user_id_verifikator')) {
            $payload['user_id_verifikator'] = Auth::id();
        }

        if ($this->hasColumn($table, 'bidan_id')) {
            $payload['bidan_id'] = Auth::id();
        }

        if ($this->hasColumn($table, 'updated_by')) {
            $payload['updated_by'] = Auth::id();
        }

        return $payload;
    }

    private function kategoriOptions(): array
    {
        return [
            'balita' => [
                'label' => 'Balita',
                'icon' => 'ph-baby',
                'desc' => 'Pemeriksaan pertumbuhan dan status gizi Balita.',
            ],
            'remaja' => [
                'label' => 'Remaja',
                'icon' => 'ph-user-focus',
                'desc' => 'Pemeriksaan kesehatan dasar Remaja.',
            ],
            'lansia' => [
                'label' => 'Lansia',
                'icon' => 'ph-heartbeat',
                'desc' => 'Pemeriksaan dasar dan pemantauan kesehatan Lansia.',
            ],
        ];
    }

    private function normalizeTab(mixed $tab): string
    {
        return in_array($tab, ['pending', 'verified'], true) ? $tab : 'pending';
    }

    private function normalizeKategori(mixed $kategori): string
    {
        $kategori = strtolower(trim((string) $kategori));

        return in_array($kategori, ['balita', 'remaja', 'lansia'], true)
            ? $kategori
            : 'semua';
    }

    private function verifiedStatuses(): array
    {
        return ['verified', 'tervalidasi', 'approved'];
    }

    private function pendingStatuses(): array
    {
        return ['pending', 'menunggu', 'belum_divalidasi'];
    }

    private function isVerified(Pemeriksaan $pemeriksaan): bool
    {
        return in_array((string) data_get($pemeriksaan, 'status_verifikasi'), $this->verifiedStatuses(), true);
    }

    private function statusLabel(mixed $status): string
    {
        return match ((string) $status) {
            'verified', 'tervalidasi', 'approved' => 'Tervalidasi',
            'ditolak', 'rejected' => 'Perlu Revisi',
            default => 'Menunggu Validasi',
        };
    }

    private function allowedMorphTypes(): array
    {
        return array_values(array_unique(array_merge(
            $this->morphTypeValues(Balita::class),
            $this->morphTypeValues(Remaja::class),
            $this->morphTypeValues(Lansia::class),
        )));
    }

    private function morphTypeValues(string $modelClass): array
    {
        $baseName = class_basename($modelClass);

        return array_values(array_unique([
            $modelClass,
            $baseName,
            strtolower($baseName),
        ]));
    }

    private function cleanSearch(mixed $value): string
    {
        if (is_array($value)) {
            return '';
        }

        return trim((string) $value);
    }

    private function firstFilled(array $values): string
    {
        foreach ($values as $value) {
            $value = trim((string) ($value ?? ''));

            if ($value !== '') {
                return $value;
            }
        }

        return '-';
    }

    private function displayValue(mixed $value, string $suffix = ''): string
    {
        if ($value === null || $value === '' || $value === '-') {
            return '-';
        }

        return trim((string) $value . ' ' . $suffix);
    }

    private function formatDate(mixed $date): string
    {
        if (!$date || $date === '-') {
            return '-';
        }

        try {
            return Carbon::parse($date)->translatedFormat('d M Y');
        } catch (\Throwable $e) {
            return '-';
        }
    }

    private function onlyExistingColumns(string $table, array $payload): array
    {
        return collect($payload)
            ->filter(fn ($value, string $column) => $this->hasColumn($table, $column))
            ->all();
    }

    private function hasColumn(string $table, string $column): bool
    {
        $key = $table . '.' . $column;

        if (array_key_exists($key, $this->columnCache)) {
            return $this->columnCache[$key];
        }

        try {
            return $this->columnCache[$key] = Schema::hasColumn($table, $column);
        } catch (\Throwable $e) {
            return $this->columnCache[$key] = false;
        }
    }

    private function pemeriksaanTable(): string
    {
        return (new Pemeriksaan())->getTable();
    }
}