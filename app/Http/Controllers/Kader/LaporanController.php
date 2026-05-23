<?php

namespace App\Http\Controllers\Kader;

use App\Http\Controllers\Controller;
use App\Models\Balita;
use App\Models\Lansia;
use App\Models\Pemeriksaan;
use App\Models\Remaja;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LaporanController extends Controller
{
    private array $reportTypes = [
        'balita' => 'Laporan Balita',
        'remaja' => 'Laporan Remaja',
        'lansia' => 'Laporan Lansia',
    ];

    public function index()
    {
        $stats = [
            'balita' => $this->safeModelCount(Balita::class),
            'remaja' => $this->safeModelCount(Remaja::class),
            'lansia' => $this->safeModelCount(Lansia::class),

            'pemeriksaan_balita_bulan_ini' => $this->countPemeriksaanThisMonth('balita'),
            'pemeriksaan_remaja_bulan_ini' => $this->countPemeriksaanThisMonth('remaja'),
            'pemeriksaan_lansia_bulan_ini' => $this->countPemeriksaanThisMonth('lansia'),
        ];

        $reportTypes = $this->reportTypes;

        return view('kader.laporan.index', compact('stats', 'reportTypes'));
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'jenis_laporan' => 'required|in:balita,remaja,lansia',
            'tanggal_awal' => 'nullable|date',
            'tanggal_akhir' => 'nullable|date|after_or_equal:tanggal_awal',
            'mode' => 'nullable|in:preview,download',
        ], [
            'jenis_laporan.required' => 'Jenis laporan wajib dipilih.',
            'jenis_laporan.in' => 'Jenis laporan tidak valid.',
            'tanggal_awal.date' => 'Tanggal awal tidak valid.',
            'tanggal_akhir.date' => 'Tanggal akhir tidak valid.',
            'tanggal_akhir.after_or_equal' => 'Tanggal akhir tidak boleh lebih awal dari tanggal awal.',
        ]);

        [$tanggalAwal, $tanggalAkhir] = $this->resolveDateRange($validated);

        $payload = $this->buildSasaranReport(
            $validated['jenis_laporan'],
            $tanggalAwal,
            $tanggalAkhir
        );

        $payload['jenis_laporan'] = $validated['jenis_laporan'];
        $payload['periode'] = [
            'awal' => $tanggalAwal,
            'akhir' => $tanggalAkhir,
            'label' => $tanggalAwal->translatedFormat('d F Y') . ' sampai ' . $tanggalAkhir->translatedFormat('d F Y'),
        ];

        $payload['dicetak_oleh'] = Auth::user()->name ?? Auth::user()->nama ?? 'Kader Posyandu';
        $payload['dicetak_pada'] = now('Asia/Jakarta');

        $payload['posyandu'] = [
            'nama' => $this->setting('posyandu_name', 'Posyandu Desa Bantarkulon'),
            'alamat' => $this->setting('posyandu_alamat', 'Desa Bantarkulon'),
            'telepon' => $this->setting('posyandu_telepon', '-'),
        ];

        $filename = $this->makeFileName(
            $validated['jenis_laporan'],
            $tanggalAwal,
            $tanggalAkhir
        );

        $pdf = Pdf::loadView('kader.laporan.templates.pdf', $payload)
            ->setPaper('a4', 'landscape');

        if (($validated['mode'] ?? 'preview') === 'download') {
            return $pdf->download($filename);
        }

        return $pdf->stream($filename);
    }

    private function buildSasaranReport(string $type, Carbon $awal, Carbon $akhir): array
    {
        $config = $this->reportConfig($type);
        $model = $config['model'];

        if (!Schema::hasTable('pemeriksaans')) {
            return $this->emptyReport($config);
        }

        $dateColumn = $this->firstExistingColumn('pemeriksaans', [
            'tanggal_periksa',
            'tanggal_pemeriksaan',
            'tanggal_kunjungan',
            'created_at',
        ]) ?? 'created_at';

        $query = Pemeriksaan::query()
            ->whereBetween($dateColumn, [$awal, $akhir])
            ->orderBy($dateColumn, 'asc')
            ->orderBy('id', 'asc');

        $this->applyKategoriFilter($query, $type);

        $pemeriksaans = $query->limit(1200)->get();

        $patientIds = $pemeriksaans
            ->map(fn ($item) => $this->patientId($item, $type))
            ->filter()
            ->unique()
            ->values();

        $patients = $this->loadPatients($model, $patientIds);

        $groups = [];

        foreach ($pemeriksaans->groupBy(function ($item) use ($dateColumn) {
            $date = $this->dateValue($item->{$dateColumn} ?? $item->created_at ?? null);
            return $date ? $date->format('Y-m-d') : 'tanpa-tanggal';
        }) as $dateKey => $items) {
            $date = $dateKey !== 'tanpa-tanggal'
                ? Carbon::parse($dateKey, 'Asia/Jakarta')
                : null;

            $rows = [];

            foreach ($items->values() as $index => $pemeriksaan) {
                $patientId = $this->patientId($pemeriksaan, $type);
                $patient = $patientId ? $patients->get($patientId) : null;

                $rows[] = $this->rowForReport($type, $pemeriksaan, $patient, $index + 1);
            }

            $groups[] = [
                'date_key' => $dateKey,
                'date_label' => $date ? $date->translatedFormat('d F Y') : 'Tanpa Tanggal',
                'rows' => $rows,
            ];
        }

        $statusColumn = $this->firstExistingColumn('pemeriksaans', [
            'status_verifikasi',
            'status_validasi',
            'status',
        ]);

        return [
            'title' => $config['title'],
            'subtitle' => $config['subtitle'],
            'label' => $config['label'],
            'groups' => $groups,
            'summary' => [
                [
                    'label' => 'Total Sasaran',
                    'value' => $this->safeModelCount($model),
                    'note' => 'Jumlah seluruh data ' . $config['label'] . ' pada sistem.',
                ],
                [
                    'label' => 'Pemeriksaan Periode Ini',
                    'value' => $pemeriksaans->count(),
                    'note' => 'Jumlah pemeriksaan sesuai periode laporan.',
                ],
                [
                    'label' => 'Sudah Ditinjau',
                    'value' => $this->countStatus($pemeriksaans, $statusColumn, ['verified', 'valid', 'terverifikasi', 'sudah_ditinjau', 'ditinjau']),
                    'note' => 'Data pemeriksaan yang sudah ditinjau.',
                ],
                [
                    'label' => 'Menunggu Review',
                    'value' => $this->countStatus($pemeriksaans, $statusColumn, ['pending', 'menunggu', 'menunggu_review']),
                    'note' => 'Data pemeriksaan yang masih menunggu tinjauan.',
                ],
                [
                    'label' => 'Perlu Perbaikan',
                    'value' => $this->countStatus($pemeriksaans, $statusColumn, ['rejected', 'ditolak', 'revisi', 'perlu_perbaikan']),
                    'note' => 'Data pemeriksaan yang perlu perbaikan.',
                ],
            ],
            'notes' => $config['notes'],
        ];
    }

    private function reportConfig(string $type): array
    {
        return match ($type) {
            'balita' => [
                'label' => 'Balita',
                'model' => Balita::class,
                'title' => 'Laporan Pemeriksaan Balita',
                'subtitle' => 'Rekap pemeriksaan Balita beserta identitas, pengukuran fisik, status gizi, dan imunisasi terakhir.',
                'notes' => [
                    'BB berarti Berat Badan.',
                    'TB/PB berarti Tinggi Badan atau Panjang Badan.',
                    'LK berarti Lingkar Kepala.',
                    'LILA berarti Lingkar Lengan Atas.',
                    'Imunisasi terakhir ditampilkan sebagai ringkasan layanan Balita jika tersedia.',
                ],
            ],
            'remaja' => [
                'label' => 'Remaja',
                'model' => Remaja::class,
                'title' => 'Laporan Pemeriksaan Remaja',
                'subtitle' => 'Rekap pemeriksaan Remaja dengan pengukuran fisik, tekanan darah, GDS jika tersedia, dan catatan pemeriksaan.',
                'notes' => [
                    'LP berarti Lingkar Perut.',
                    'LILA berarti Lingkar Lengan Atas.',
                    'TD berarti Tekanan Darah.',
                    'GDS berarti Gula Darah Sewaktu dan bersifat opsional sesuai data pemeriksaan yang tersedia.',
                ],
            ],
            'lansia' => [
                'label' => 'Lansia',
                'model' => Lansia::class,
                'title' => 'Laporan Pemeriksaan Lansia',
                'subtitle' => 'Rekap pemeriksaan Lansia dengan tingkat kemandirian, tekanan darah, gula darah, kolesterol, asam urat, riwayat penyakit, dan keluhan.',
                'notes' => [
                    'LP berarti Lingkar Perut.',
                    'TD berarti Tekanan Darah.',
                    'GDS berarti Gula Darah Sewaktu.',
                    'Data riwayat dan keluhan diambil dari profil Lansia dan catatan pemeriksaan jika tersedia.',
                ],
            ],
            default => abort(404),
        };
    }

    private function rowForReport(string $type, Pemeriksaan $pemeriksaan, $patient, int $number): array
    {
        return match ($type) {
            'balita' => $this->balitaRow($pemeriksaan, $patient, $number),
            'remaja' => $this->remajaRow($pemeriksaan, $patient, $number),
            'lansia' => $this->lansiaRow($pemeriksaan, $patient, $number),
            default => [],
        };
    }

    private function balitaRow(Pemeriksaan $pemeriksaan, $patient, int $number): array
    {
        $tanggalLahir = $this->dateValue(data_get($patient, 'tanggal_lahir'));
        $patientId = data_get($patient, 'id');

        return [
            'no' => $number,
            'nama' => $this->patientName($patient, $pemeriksaan),
            'usia' => $this->ageText($tanggalLahir),
            'orang_tua' => trim((data_get($patient, 'nama_ibu') ?: '-') . ' / ' . (data_get($patient, 'nama_ayah') ?: '-')),
            'bb' => $this->number($this->firstRaw([$pemeriksaan->berat_badan ?? null, data_get($patient, 'berat_badan')]), ' kg'),
            'tb' => $this->number($this->firstRaw([$pemeriksaan->tinggi_badan ?? null, data_get($patient, 'tinggi_badan')]), ' cm'),
            'lk' => $this->number($this->firstRaw([$pemeriksaan->lingkar_kepala ?? null, data_get($patient, 'lingkar_kepala')]), ' cm'),
            'lila' => $this->number($this->firstRaw([$pemeriksaan->lingkar_lengan ?? null, data_get($patient, 'lingkar_lengan')]), ' cm'),
            'status_gizi' => $this->firstFilled([$pemeriksaan->status_gizi ?? null, data_get($patient, 'status_gizi'), '-']),
            'imunisasi' => $this->latestBalitaImunisasi($patientId, $this->dateValue($pemeriksaan->tanggal_periksa ?? $pemeriksaan->created_at ?? null)),
            'keterangan' => $this->firstFilled([
                $pemeriksaan->diagnosa ?? null,
                $pemeriksaan->keluhan ?? null,
                $pemeriksaan->tindakan ?? null,
                $pemeriksaan->catatan ?? null,
                '-',
            ]),
        ];
    }

    private function remajaRow(Pemeriksaan $pemeriksaan, $patient, int $number): array
    {
        $tanggalLahir = $this->dateValue(data_get($patient, 'tanggal_lahir'));

        return [
            'no' => $number,
            'nama' => $this->patientName($patient, $pemeriksaan),
            'usia' => $this->ageText($tanggalLahir),
            'sekolah_kelas' => trim((data_get($patient, 'sekolah') ?: '-') . ' / ' . (data_get($patient, 'kelas') ?: '-')),
            'bb' => $this->number($this->firstRaw([$pemeriksaan->berat_badan ?? null, data_get($patient, 'berat_badan')]), ' kg'),
            'tb' => $this->number($this->firstRaw([$pemeriksaan->tinggi_badan ?? null, data_get($patient, 'tinggi_badan')]), ' cm'),
            'imt' => $this->number($this->firstRaw([$pemeriksaan->imt ?? null, data_get($patient, 'imt')])),
            'lp' => $this->number($this->firstRaw([$pemeriksaan->lingkar_perut ?? null, data_get($patient, 'lingkar_perut')]), ' cm'),
            'lila' => $this->number($this->firstRaw([$pemeriksaan->lingkar_lengan ?? null, data_get($patient, 'lingkar_lengan')]), ' cm'),
            'td' => $this->firstFilled([$pemeriksaan->tekanan_darah ?? null, data_get($patient, 'tekanan_darah'), '-']),
            'gds' => $this->firstFilled([$pemeriksaan->gula_darah ?? null, data_get($patient, 'gula_darah'), '-']),
            'keterangan' => $this->firstFilled([
                $pemeriksaan->diagnosa ?? null,
                $pemeriksaan->keluhan ?? null,
                $pemeriksaan->tindakan ?? null,
                $pemeriksaan->catatan ?? null,
                '-',
            ]),
        ];
    }

    private function lansiaRow(Pemeriksaan $pemeriksaan, $patient, int $number): array
    {
        $tanggalLahir = $this->dateValue(data_get($patient, 'tanggal_lahir'));

        return [
            'no' => $number,
            'nama' => $this->patientName($patient, $pemeriksaan),
            'usia' => $this->ageText($tanggalLahir),
            'kemandirian' => $this->kemandirianLabel($pemeriksaan->kemandirian ?? data_get($patient, 'tingkat_kemandirian')),
            'bb' => $this->number($this->firstRaw([$pemeriksaan->berat_badan ?? null, data_get($patient, 'berat_badan')]), ' kg'),
            'tb' => $this->number($this->firstRaw([$pemeriksaan->tinggi_badan ?? null, data_get($patient, 'tinggi_badan')]), ' cm'),
            'imt' => $this->number($this->firstRaw([$pemeriksaan->imt ?? null, data_get($patient, 'imt')])),
            'lp' => $this->number($this->firstRaw([$pemeriksaan->lingkar_perut ?? null, data_get($patient, 'lingkar_perut')]), ' cm'),
            'td' => $this->firstFilled([$pemeriksaan->tekanan_darah ?? null, data_get($patient, 'tekanan_darah'), '-']),
            'gds' => $this->firstFilled([$pemeriksaan->gula_darah ?? null, data_get($patient, 'gula_darah'), '-']),
            'kolesterol' => $this->number($this->firstRaw([$pemeriksaan->kolesterol ?? null, data_get($patient, 'kolesterol')])),
            'asam_urat' => $this->number($this->firstRaw([$pemeriksaan->asam_urat ?? null, data_get($patient, 'asam_urat')])),
            'riwayat_keluhan' => $this->firstFilled([
                data_get($patient, 'penyakit_bawaan'),
                data_get($patient, 'keluhan'),
                $pemeriksaan->keluhan ?? null,
                $pemeriksaan->diagnosa ?? null,
                $pemeriksaan->catatan ?? null,
                '-',
            ]),
        ];
    }

    private function applyKategoriFilter($query, string $type): void
    {
        if (Schema::hasColumn('pemeriksaans', 'kategori_pasien')) {
            $query->where('kategori_pasien', $type);
            return;
        }

        if (Schema::hasColumn('pemeriksaans', 'jenis_sasaran')) {
            $query->where('jenis_sasaran', $type);
            return;
        }

        if (Schema::hasColumn('pemeriksaans', 'pasien_type')) {
            $modelClass = match ($type) {
                'balita' => Balita::class,
                'remaja' => Remaja::class,
                'lansia' => Lansia::class,
            };

            $query->where(function ($q) use ($type, $modelClass) {
                $q->where('pasien_type', $modelClass)
                    ->orWhere('pasien_type', 'like', '%' . ucfirst($type) . '%')
                    ->orWhere('pasien_type', 'like', '%' . $type . '%');
            });

            return;
        }

        $directIdColumn = $type . '_id';

        if (Schema::hasColumn('pemeriksaans', $directIdColumn)) {
            $query->whereNotNull($directIdColumn);
        }
    }

    private function patientId($item, string $type)
    {
        $directIdColumn = $type . '_id';

        return $item->pasien_id
            ?? $item->{$directIdColumn}
            ?? $item->sasaran_id
            ?? null;
    }

    private function loadPatients(string $model, $ids)
    {
        if ($ids->isEmpty()) {
            return collect();
        }

        try {
            return $model::whereIn('id', $ids)->get()->keyBy('id');
        } catch (\Throwable) {
            return collect();
        }
    }

    private function countPemeriksaanThisMonth(string $kategori): int
    {
        try {
            if (!Schema::hasTable('pemeriksaans')) {
                return 0;
            }

            $dateColumn = $this->firstExistingColumn('pemeriksaans', [
                'tanggal_periksa',
                'tanggal_pemeriksaan',
                'tanggal_kunjungan',
                'created_at',
            ]) ?? 'created_at';

            $query = Pemeriksaan::query()
                ->whereMonth($dateColumn, now('Asia/Jakarta')->month)
                ->whereYear($dateColumn, now('Asia/Jakarta')->year);

            $this->applyKategoriFilter($query, $kategori);

            return $query->count();
        } catch (\Throwable) {
            return 0;
        }
    }

    private function countStatus($collection, ?string $column, array $values): int
    {
        if (!$column) {
            return 0;
        }

        return $collection->filter(function ($item) use ($column, $values) {
            return in_array(strtolower((string) ($item->{$column} ?? '')), $values, true);
        })->count();
    }

    private function safeModelCount(string $model): int
    {
        try {
            $instance = new $model();

            if (!Schema::hasTable($instance->getTable())) {
                return 0;
            }

            return $model::count();
        } catch (\Throwable) {
            return 0;
        }
    }

    private function firstExistingColumn(string $table, array $columns): ?string
    {
        if (!Schema::hasTable($table)) {
            return null;
        }

        foreach ($columns as $column) {
            if (Schema::hasColumn($table, $column)) {
                return $column;
            }
        }

        return null;
    }

    private function latestBalitaImunisasi($balitaId, ?Carbon $tanggalPeriksa = null): string
    {
        if (!$balitaId || !Schema::hasTable('imunisasis')) {
            return '-';
        }

        try {
            $dateColumn = $this->firstExistingColumn('imunisasis', [
                'tanggal_imunisasi',
                'tanggal',
                'created_at',
            ]) ?? 'created_at';

            $query = DB::table('imunisasis');

            if (Schema::hasColumn('imunisasis', 'balita_id')) {
                $query->where('balita_id', $balitaId);
            } elseif (Schema::hasColumn('imunisasis', 'pasien_id')) {
                $query->where('pasien_id', $balitaId);

                if (Schema::hasColumn('imunisasis', 'kategori_pasien')) {
                    $query->where('kategori_pasien', 'balita');
                }
            } elseif (Schema::hasColumn('imunisasis', 'kunjungan_id') && Schema::hasTable('kunjungans')) {
                $query->join('kunjungans', 'imunisasis.kunjungan_id', '=', 'kunjungans.id')
                    ->where('kunjungans.pasien_id', $balitaId);

                if (Schema::hasColumn('kunjungans', 'pasien_type')) {
                    $query->where(function ($q) {
                        $q->where('kunjungans.pasien_type', Balita::class)
                            ->orWhere('kunjungans.pasien_type', 'like', '%Balita%')
                            ->orWhere('kunjungans.pasien_type', 'like', '%balita%');
                    });
                }
            } else {
                return '-';
            }

            if ($tanggalPeriksa) {
                $query->whereDate('imunisasis.' . $dateColumn, '<=', $tanggalPeriksa->toDateString());
            }

            $selects = ['imunisasis.' . $dateColumn . ' as tanggal'];

            foreach (['jenis_imunisasi', 'nama_imunisasi', 'vaksin', 'dosis'] as $column) {
                if (Schema::hasColumn('imunisasis', $column)) {
                    $selects[] = 'imunisasis.' . $column;
                }
            }

            $item = $query
                ->select($selects)
                ->orderByDesc('imunisasis.' . $dateColumn)
                ->first();

            if (!$item) {
                return '-';
            }

            $parts = array_filter([
                $item->jenis_imunisasi ?? null,
                $item->nama_imunisasi ?? null,
                $item->vaksin ?? null,
                !empty($item->dosis) ? 'Dosis ' . $item->dosis : null,
            ]);

            $tanggal = $this->dateValue($item->tanggal ?? null);

            $label = count($parts) ? implode(' - ', $parts) : 'Imunisasi';

            return $label . ($tanggal ? ' (' . $tanggal->translatedFormat('d M Y') . ')' : '');
        } catch (\Throwable) {
            return '-';
        }
    }

    private function emptyReport(array $config): array
    {
        return [
            'title' => $config['title'],
            'subtitle' => $config['subtitle'],
            'label' => $config['label'],
            'groups' => [],
            'summary' => [
                [
                    'label' => 'Total Sasaran',
                    'value' => 0,
                    'note' => 'Data belum tersedia.',
                ],
                [
                    'label' => 'Pemeriksaan Periode Ini',
                    'value' => 0,
                    'note' => 'Tidak ada pemeriksaan pada periode ini.',
                ],
            ],
            'notes' => $config['notes'],
        ];
    }

    private function resolveDateRange(array $validated): array
    {
        $awal = !empty($validated['tanggal_awal'])
            ? Carbon::parse($validated['tanggal_awal'], 'Asia/Jakarta')->startOfDay()
            : now('Asia/Jakarta')->startOfMonth();

        $akhir = !empty($validated['tanggal_akhir'])
            ? Carbon::parse($validated['tanggal_akhir'], 'Asia/Jakarta')->endOfDay()
            : now('Asia/Jakarta')->endOfDay();

        return [$awal, $akhir];
    }

    private function setting(string $key, string $default = '-'): string
    {
        try {
            if (!Schema::hasTable('settings')) {
                return $default;
            }

            $value = DB::table('settings')->where('key', $key)->value('value');

            return $value ?: $default;
        } catch (\Throwable) {
            return $default;
        }
    }

    private function patientName($patient, $pemeriksaan): string
    {
        return data_get($patient, 'nama_lengkap')
            ?? data_get($patient, 'nama')
            ?? data_get($pemeriksaan, 'nama_pasien')
            ?? '-';
    }

    private function firstFilled(array $values): string
    {
        foreach ($values as $value) {
            if ($value !== null && $value !== '') {
                return (string) $value;
            }
        }

        return '-';
    }

    private function firstRaw(array $values)
    {
        foreach ($values as $value) {
            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        return null;
    }

    private function number($value, string $suffix = ''): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        $number = number_format((float) $value, 1, ',', '.');
        $number = rtrim(rtrim($number, '0'), ',');

        return $number . $suffix;
    }

    private function dateValue($value): ?Carbon
    {
        if (!$value) {
            return null;
        }

        try {
            return Carbon::parse($value, 'Asia/Jakarta');
        } catch (\Throwable) {
            return null;
        }
    }

    private function ageText(?Carbon $tanggalLahir): string
    {
        if (!$tanggalLahir) {
            return '-';
        }

        $diff = $tanggalLahir->diff(now('Asia/Jakarta'));

        if ($diff->y > 0) {
            return $diff->y . ' tahun ' . $diff->m . ' bulan';
        }

        if ($diff->m > 0) {
            return $diff->m . ' bulan';
        }

        return $diff->d . ' hari';
    }

    private function kemandirianLabel($value): string
    {
        return match ($value) {
            'A', 'mandiri' => 'Mandiri',
            'B', 'bantuan_sebagian', 'bantuan_ringan', 'bantuan_sedang' => 'Bantuan Sebagian',
            'C', 'ketergantungan_penuh', 'ketergantungan_tinggi' => 'Ketergantungan Penuh',
            default => 'Belum Diisi',
        };
    }

    private function makeFileName(string $type, Carbon $awal, Carbon $akhir): string
    {
        return 'laporan-' . $type . '-' . $awal->format('Ymd') . '-' . $akhir->format('Ymd') . '.pdf';
    }
}