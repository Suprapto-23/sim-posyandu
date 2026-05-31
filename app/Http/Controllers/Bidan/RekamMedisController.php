<?php

namespace App\Http\Controllers\Bidan;

use App\Http\Controllers\Controller;
use App\Models\Balita;
use App\Models\Imunisasi;
use App\Models\Lansia;
use App\Models\Pemeriksaan;
use App\Models\Remaja;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class RekamMedisController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        try {
            $type = $this->normalizeType($request->get('type', 'balita'));
            $search = $this->cleanSearch($request->get('search', ''));

            $modelClass = $this->patientModels()[$type];

            $query = $modelClass::query();

            $this->applyPatientSearch($query, $modelClass, $search, $type);

            $data = $query
                ->orderByDesc($this->orderColumn($modelClass))
                ->paginate(12)
                ->withQueryString();

            $typeOptions = $this->typeOptions();

            $stats = [
                'total' => [
                    'balita' => $this->safePatientCount(Balita::class),
                    'remaja' => $this->safePatientCount(Remaja::class),
                    'lansia' => $this->safePatientCount(Lansia::class),
                ],
                'verified' => [
                    'balita' => $this->safeVerifiedCount('balita'),
                    'remaja' => $this->safeVerifiedCount('remaja'),
                    'lansia' => $this->safeVerifiedCount('lansia'),
                ],
            ];

            $stats['total_semua'] = array_sum($stats['total']);
            $stats['verified_semua'] = array_sum($stats['verified']);

            return view('bidan.rekam-medis.index', compact(
                'data',
                'type',
                'search',
                'typeOptions',
                'stats'
            ));
        } catch (\Throwable $e) {
            Log::error('BIDAN_REKAM_MEDIS_INDEX_ERROR', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return redirect()
                ->route('bidan.dashboard')
                ->with('error', 'Gagal memuat direktori Rekam Medis.');
        }
    }

    public function show(string $pasien_type, int|string $pasien_id): View|RedirectResponse
    {
        try {
            $pasienType = $this->normalizeType($pasien_type);
            $modelClass = $this->patientModels()[$pasienType];

            $pasien = $modelClass::query()->findOrFail($pasien_id);

            $riwayatMedis = $this->medicalQueryForPatient($pasienType, $pasien_id, $modelClass)
                ->with(['kunjungan', 'pemeriksa', 'verifikator', 'verifikatorLegacy'])
                ->orderByDesc($this->medicalOrderColumn())
                ->get();

            $riwayatImunisasi = collect();

            if ($pasienType === 'balita') {
                $riwayatImunisasi = $this->immunizationQueryForBalita($pasien_id)
                    ->with(['kunjungan.petugas'])
                    ->orderByDesc($this->immunizationOrderColumn())
                    ->get();
            }

            $chartData = $riwayatMedis
                ->take(7)
                ->reverse()
                ->values();

            $lastMedical = $riwayatMedis->first();

            $summary = [
                'total_medis' => $riwayatMedis->count(),
                'total_imunisasi' => $pasienType === 'balita' ? $riwayatImunisasi->count() : 0,
                'pemeriksaan_terakhir' => $this->formatDateTime($lastMedical?->tanggal_periksa ?? $lastMedical?->created_at),
                'imunisasi_terakhir' => $pasienType === 'balita'
                    ? $this->formatDate($riwayatImunisasi->first()?->tanggal_imunisasi)
                    : null,
                'parameter_terakhir' => $this->lastParameterSummary($lastMedical, $pasienType),
            ];

            $typeOptions = $this->typeOptions();

            $pasien_type = $pasienType;
            $pasienTypeLabel = $typeOptions[$pasienType]['label'] ?? ucfirst($pasienType);

            return view('bidan.rekam-medis.show', compact(
                'pasien',
                'pasien_type',
                'pasienType',
                'pasienTypeLabel',
                'riwayatMedis',
                'riwayatImunisasi',
                'chartData',
                'summary',
                'typeOptions'
            ));
        } catch (\Throwable $e) {
            Log::error('BIDAN_REKAM_MEDIS_SHOW_ERROR', [
                'message' => $e->getMessage(),
                'pasien_type' => $pasien_type,
                'pasien_id' => $pasien_id,
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return redirect()
                ->route('bidan.rekam-medis.index')
                ->with('error', 'Gagal memuat detail Rekam Medis.');
        }
    }

    private function patientModels(): array
    {
        return [
            'balita' => Balita::class,
            'remaja' => Remaja::class,
            'lansia' => Lansia::class,
        ];
    }

    private function typeOptions(): array
    {
        return [
            'balita' => [
                'label' => 'Balita',
                'desc' => 'Riwayat pertumbuhan, pemeriksaan dasar, dan imunisasi.',
                'icon' => 'ph-baby',
                'theme' => 'sky',
            ],
            'remaja' => [
                'label' => 'Remaja',
                'desc' => 'Riwayat pemeriksaan kesehatan remaja.',
                'icon' => 'ph-user-focus',
                'theme' => 'indigo',
            ],
            'lansia' => [
                'label' => 'Lansia',
                'desc' => 'Riwayat pemeriksaan dasar dan pemantauan kesehatan lansia.',
                'icon' => 'ph-heartbeat',
                'theme' => 'emerald',
            ],
        ];
    }

    private function normalizeType(mixed $type): string
    {
        if (is_array($type)) {
            return 'balita';
        }

        $type = strtolower(trim((string) $type));

        return array_key_exists($type, $this->patientModels())
            ? $type
            : 'balita';
    }

    private function cleanSearch(mixed $search): string
    {
        if (is_array($search)) {
            return '';
        }

        return trim((string) $search);
    }

    private function applyPatientSearch(Builder $query, string $modelClass, string $search, string $type): void
    {
        if ($search === '') {
            return;
        }

        $table = (new $modelClass())->getTable();

        $query->where(function (Builder $q) use ($table, $search, $type) {
            foreach ($this->searchableColumns($table, $type) as $column) {
                $q->orWhere($column, 'like', '%' . $search . '%');
            }
        });
    }

    private function searchableColumns(string $table, string $type): array
    {
        $columns = [
            'nama_lengkap',
            'nama',
            'nik',
            'alamat',
            'no_hp',
        ];

        if ($type === 'balita') {
            $columns = array_merge($columns, [
                'nama_ibu',
                'nama_ayah',
                'nik_ibu',
                'nik_ayah',
            ]);
        }

        if ($type === 'remaja') {
            $columns = array_merge($columns, [
                'sekolah',
                'kelas',
            ]);
        }

        if ($type === 'lansia') {
            $columns = array_merge($columns, [
                'penyakit_bawaan',
                'tingkat_kemandirian',
            ]);
        }

        return collect($columns)
            ->filter(fn ($column) => $this->hasColumn($table, $column))
            ->values()
            ->all();
    }

    private function orderColumn(string $modelClass): string
    {
        $table = (new $modelClass())->getTable();

        if ($this->hasColumn($table, 'updated_at')) {
            return 'updated_at';
        }

        if ($this->hasColumn($table, 'created_at')) {
            return 'created_at';
        }

        return 'id';
    }

    private function medicalQueryForPatient(string $type, int|string $patientId, string $modelClass): Builder
    {
        $query = Pemeriksaan::query();

        $query->where(function (Builder $q) use ($type, $patientId, $modelClass) {
            $q->whereHas('kunjungan', function (Builder $kunjungan) use ($patientId, $modelClass) {
                $kunjungan
                    ->where('pasien_id', $patientId)
                    ->whereIn('pasien_type', $this->morphTypeValues($modelClass));
            });

            if (
                $this->hasColumn('pemeriksaans', 'pasien_id') &&
                $this->hasColumn('pemeriksaans', 'kategori_pasien')
            ) {
                $q->orWhere(function (Builder $direct) use ($type, $patientId) {
                    $direct
                        ->where('pasien_id', $patientId)
                        ->where('kategori_pasien', $type);
                });
            }

            $specificColumn = match ($type) {
                'balita' => 'balita_id',
                'remaja' => 'remaja_id',
                'lansia' => 'lansia_id',
                default => null,
            };

            if ($specificColumn && $this->hasColumn('pemeriksaans', $specificColumn)) {
                $q->orWhere($specificColumn, $patientId);
            }
        });

        $this->applyVerifiedFilter($query);

        return $query;
    }

    private function immunizationQueryForBalita(int|string $patientId): Builder
    {
        $query = Imunisasi::query();

        $query->where(function (Builder $q) use ($patientId) {
            $q->whereHas('kunjungan', function (Builder $kunjungan) use ($patientId) {
                $kunjungan
                    ->where('pasien_id', $patientId)
                    ->whereIn('pasien_type', $this->morphTypeValues(Balita::class));
            });

            if ($this->hasColumn('imunisasis', 'balita_id')) {
                $q->orWhere('balita_id', $patientId);
            }
        });

        return $query;
    }

    private function applyVerifiedFilter(Builder $query): void
    {
        if (!$this->hasColumn('pemeriksaans', 'status_verifikasi')) {
            return;
        }

        $query->whereIn('status_verifikasi', [
            'verified',
            'tervalidasi',
            'approved',
        ]);
    }

    private function safePatientCount(string $modelClass): int
    {
        try {
            return $modelClass::query()->count();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function safeVerifiedCount(string $type): int
    {
        try {
            $query = Pemeriksaan::query();

            $this->applyVerifiedFilter($query);

            if ($this->hasColumn('pemeriksaans', 'kategori_pasien')) {
                $query->where('kategori_pasien', $type);
            }

            return $query->count();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function medicalOrderColumn(): string
    {
        if ($this->hasColumn('pemeriksaans', 'tanggal_periksa')) {
            return 'tanggal_periksa';
        }

        if ($this->hasColumn('pemeriksaans', 'created_at')) {
            return 'created_at';
        }

        return 'id';
    }

    private function immunizationOrderColumn(): string
    {
        if ($this->hasColumn('imunisasis', 'tanggal_imunisasi')) {
            return 'tanggal_imunisasi';
        }

        if ($this->hasColumn('imunisasis', 'created_at')) {
            return 'created_at';
        }

        return 'id';
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

    private function lastParameterSummary(?Pemeriksaan $pemeriksaan, string $type): array
    {
        if (!$pemeriksaan) {
            return [];
        }

        if ($type === 'lansia') {
            return [
                'Tekanan Darah' => $this->displayValue($pemeriksaan->tekanan_darah ?? null),
                'Gula Darah' => $this->displayValue($pemeriksaan->gula_darah ?? null, 'mg/dL'),
                'Kolesterol' => $this->displayValue($pemeriksaan->kolesterol ?? null, 'mg/dL'),
                'Asam Urat' => $this->displayValue($pemeriksaan->asam_urat ?? null, 'mg/dL'),
                'Lingkar Perut' => $this->displayValue($pemeriksaan->lingkar_perut ?? null, 'cm'),
                'Kemandirian' => $this->displayValue($pemeriksaan->tingkat_kemandirian ?? null),
            ];
        }

        if ($type === 'remaja') {
            return [
                'Berat Badan' => $this->displayValue($pemeriksaan->berat_badan ?? null, 'kg'),
                'Tinggi Badan' => $this->displayValue($pemeriksaan->tinggi_badan ?? null, 'cm'),
                'IMT' => $this->displayValue($pemeriksaan->imt ?? null),
                'Tekanan Darah' => $this->displayValue($pemeriksaan->tekanan_darah ?? null),
                'LILA' => $this->displayValue($pemeriksaan->lingkar_lengan ?? null, 'cm'),
            ];
        }

        return [
            'Berat Badan' => $this->displayValue($pemeriksaan->berat_badan ?? null, 'kg'),
            'Tinggi Badan' => $this->displayValue($pemeriksaan->tinggi_badan ?? null, 'cm'),
            'Lingkar Kepala' => $this->displayValue($pemeriksaan->lingkar_kepala ?? null, 'cm'),
            'LILA' => $this->displayValue($pemeriksaan->lingkar_lengan ?? null, 'cm'),
            'Status Gizi' => $this->displayValue($pemeriksaan->status_gizi ?? null),
        ];
    }

    private function displayValue(mixed $value, string $suffix = ''): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        return trim((string) $value . ' ' . $suffix);
    }

    private function formatDate(mixed $date): string
    {
        if (!$date) {
            return '-';
        }

        try {
            return Carbon::parse($date)->translatedFormat('d M Y');
        } catch (\Throwable $e) {
            return '-';
        }
    }

    private function formatDateTime(mixed $date): string
    {
        if (!$date) {
            return '-';
        }

        try {
            return Carbon::parse($date)->translatedFormat('d M Y, H:i') . ' WIB';
        } catch (\Throwable $e) {
            return '-';
        }
    }

    private function hasColumn(string $table, string $column): bool
    {
        try {
            return Schema::hasColumn($table, $column);
        } catch (\Throwable $e) {
            return false;
        }
    }
}