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
    private string $tz = 'Asia/Jakarta';

    public function index()
    {
        Carbon::setLocale('id');

        $tahun = now($this->tz)->year;
        $awalTahun = Carbon::create($tahun, 1, 1, 0, 0, 0, $this->tz)->startOfYear();
        $akhirTahun = $awalTahun->copy()->endOfYear();

        $totalTahunan = [
            'tahun'  => $tahun,
            'balita' => $this->countPemeriksaan('balita', $awalTahun, $akhirTahun),
            'remaja' => $this->countPemeriksaan('remaja', $awalTahun, $akhirTahun),
            'lansia' => $this->countPemeriksaan('lansia', $awalTahun, $akhirTahun),
        ];

        $riwayatBulanan = $this->monthlyArchive();

        return view('kader.laporan.index', compact('totalTahunan', 'riwayatBulanan'));
    }

    public function preview(Request $request)
    {
        return $this->handleReport($request);
    }

    public function generate(Request $request)
    {
        return $this->handleReport($request);
    }

    private function handleReport(Request $request)
    {
        Carbon::setLocale('id');

        $data = $request->validate([
            'jenis_laporan' => 'required|in:balita,remaja,lansia',
            'periode_bulan' => 'nullable|date_format:Y-m',
            'periode_tahun' => 'nullable|date_format:Y',
            'mode'          => 'nullable|in:preview,download',
        ]);

        [$awal, $akhir, $label] = $this->resolvePeriod($data);

        $payload = $this->buildReport($data['jenis_laporan'], $awal, $akhir);
        $user = Auth::user();

        // 1. Convert Image to Base64 (Solusi Anti-Error Vercel)
        $logo = $this->imageToBase64(public_path('img/logo.webp'));
        $ttd_kades = $this->imageToBase64(public_path('uploads/ttd/ttd_kades.png'));
        $ttd_bidan = $this->imageToBase64(public_path('uploads/ttd/ttd_bidan.png'));

        $payload = array_merge($payload, [
            'jenis_laporan' => $data['jenis_laporan'],
            'periode' => [
                'awal'  => $awal,
                'akhir' => $akhir,
                'label' => $label,
            ],
            'dicetak_oleh' => $user?->name ?? $user?->nama ?? 'Kader Posyandu',
            'dicetak_pada' => now($this->tz),
            'posyandu' => [
                'nama'    => $this->setting('posyandu_name', 'Posyandu Desa Bantarkulon'),
                'alamat'  => $this->setting('posyandu_alamat', 'Desa Bantarkulon Kecamatan Lebakbarang'),
                'telepon' => $this->setting('posyandu_telepon', '-'),
            ],
            // Masukkan variabel base64 ke payload
            'logo'      => $logo,
            'ttd_kades' => $ttd_kades,
            'ttd_bidan' => $ttd_bidan,
        ]);

        if (($data['mode'] ?? 'preview') === 'preview') {
            return view('kader.laporan.preview', $payload);
        }

        $filename = 'laporan-' . $data['jenis_laporan'] . '-' . $awal->format('Ymd') . '-' . $akhir->format('Ymd') . '.pdf';

        // 2. Setup Direktori Font Vercel di /tmp
        if (!is_dir('/tmp/dompdf_fonts')) {
            mkdir('/tmp/dompdf_fonts', 0777, true);
        }

        // 3. Render PDF dengan opsi mutlak Vercel
        return Pdf::setOptions([
                'fontDir' => '/tmp/dompdf_fonts',
                'fontCache' => '/tmp/dompdf_fonts',
                'tempDir' => '/tmp',
                'chroot' => public_path(),
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
            ])
            ->loadView('kader.laporan.templates.pdf', $payload)
            ->setPaper('a4', 'landscape')
            ->download($filename);
    }

    /**
     * Helper untuk mengubah gambar fisik menjadi Base64 Text
     */
    private function imageToBase64($path)
    {
        if (file_exists($path)) {
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $data = file_get_contents($path);
            return 'data:image/' . $type . ';base64,' . base64_encode($data);
        }
        return null;
    }

    private function monthlyArchive(int $limit = 6): array
    {
        $items = [];
        $now = now($this->tz);

        for ($i = 0; $i < $limit; $i++) {
            $date = $now->copy()->subMonths($i);
            $awal = $date->copy()->startOfMonth();
            $akhir = $date->copy()->endOfMonth();

            $items[] = [
                'periode' => $date->format('Y-m'),
                'bulan'   => $date->translatedFormat('F Y'),
                'status'  => $i === 0 ? 'Bulan Berjalan' : 'Selesai',
                'data'    => [
                    'balita' => $this->countPemeriksaan('balita', $awal, $akhir),
                    'remaja' => $this->countPemeriksaan('remaja', $awal, $akhir),
                    'lansia' => $this->countPemeriksaan('lansia', $awal, $akhir),
                ],
            ];
        }

        return $items;
    }

    private function resolvePeriod(array $data): array
    {
        if (!empty($data['periode_bulan'])) {
            $awal = Carbon::createFromFormat('Y-m', $data['periode_bulan'], $this->tz)->startOfMonth();

            return [
                $awal,
                $awal->copy()->endOfMonth(),
                'Bulan ' . $awal->translatedFormat('F Y'),
            ];
        }

        if (!empty($data['periode_tahun'])) {
            $awal = Carbon::createFromFormat('Y', $data['periode_tahun'], $this->tz)->startOfYear();

            return [
                $awal,
                $awal->copy()->endOfYear(),
                'Tahun ' . $awal->format('Y'),
            ];
        }

        $awal = now($this->tz)->startOfMonth();

        return [
            $awal,
            $awal->copy()->endOfMonth(),
            'Bulan ' . $awal->translatedFormat('F Y'),
        ];
    }

    private function buildReport(string $type, Carbon $awal, Carbon $akhir): array
    {
        $config = $this->reportConfig($type);
        $model = $config['model'];

        if (!Schema::hasTable('pemeriksaans')) {
            return $this->emptyReport($config);
        }

        $dateColumn = $this->dateColumn();

        $query = Pemeriksaan::query()
            ->whereBetween($dateColumn, [
                $awal->copy()->startOfDay(),
                $akhir->copy()->endOfDay(),
            ])
            ->orderBy($dateColumn)
            ->orderBy('id')
            ->limit(1500);

        $this->filterKategori($query, $type);

        $pemeriksaans = $query->get();

        $patientIds = $pemeriksaans
            ->map(fn ($item) => $this->patientId($item, $type))
            ->filter()
            ->unique()
            ->values();

        $patients = $this->loadPatients($model, $patientIds);

        $rows = $pemeriksaans->values()
            ->map(function ($pemeriksaan, $index) use ($type, $patients, $dateColumn) {
                $tanggal = $this->dateValue($pemeriksaan->{$dateColumn} ?? $pemeriksaan->created_at ?? null);
                $patientId = $this->patientId($pemeriksaan, $type);
                $patient = $patientId ? $patients->get($patientId) : null;

                return $this->row($type, $pemeriksaan, $patient, $index + 1, $tanggal);
            })
            ->all();

        return [
            'title'    => $config['title'],
            'subtitle' => $config['subtitle'],
            'label'    => $config['label'],
            'rows'     => $rows,
            'groups'   => [],
            'summary'  => [
                ['label' => 'Total Sasaran', 'value' => $this->modelCount($model)],
                ['label' => 'Pemeriksaan', 'value' => count($rows)],
            ],
            'notes' => $config['notes'],
        ];
    }

    private function countPemeriksaan(string $type, Carbon $awal, Carbon $akhir): int
    {
        if (!Schema::hasTable('pemeriksaans')) {
            return 0;
        }

        $query = Pemeriksaan::query()
            ->whereBetween($this->dateColumn(), [
                $awal->copy()->startOfDay(),
                $akhir->copy()->endOfDay(),
            ]);

        $this->filterKategori($query, $type);

        return $query->count();
    }

    private function reportConfig(string $type): array
    {
        return match ($type) {
            'balita' => [
                'label' => 'Balita',
                'model' => Balita::class,
                'title' => 'Laporan Pemeriksaan Balita',
                'subtitle' => 'Rekap pemeriksaan dan pengukuran Balita.',
                'notes' => [
                    'BB: Berat Badan',
                    'TB/PB: Tinggi atau Panjang Badan',
                    'LK: Lingkar Kepala',
                    'LILA: Lingkar Lengan Atas',
                ],
            ],
            'remaja' => [
                'label' => 'Remaja',
                'model' => Remaja::class,
                'title' => 'Laporan Pemeriksaan Remaja',
                'subtitle' => 'Rekap pemeriksaan dan pengukuran Remaja.',
                'notes' => [
                    'LP: Lingkar Perut',
                    'TD: Tekanan Darah',
                    'GDS: Gula Darah Sewaktu',
                ],
            ],
            'lansia' => [
                'label' => 'Lansia',
                'model' => Lansia::class,
                'title' => 'Laporan Pemeriksaan Lansia',
                'subtitle' => 'Rekap pemeriksaan kesehatan dasar Lansia.',
                'notes' => [
                    'LP: Lingkar Perut',
                    'TD: Tekanan Darah',
                    'GDS: Gula Darah Sewaktu',
                    'Koles: Kolesterol',
                ],
            ],
            default => abort(404),
        };
    }

    private function row(string $type, Pemeriksaan $pemeriksaan, $patient, int $no, ?Carbon $tanggal): array
    {
        return match ($type) {
            'balita' => $this->balitaRow($pemeriksaan, $patient, $no, $tanggal),
            'remaja' => $this->remajaRow($pemeriksaan, $patient, $no, $tanggal),
            'lansia' => $this->lansiaRow($pemeriksaan, $patient, $no, $tanggal),
            default => [],
        };
    }

    private function balitaRow(Pemeriksaan $p, $patient, int $no, ?Carbon $tanggal): array
    {
        return [
            'no'          => $no,
            'tanggal'     => $this->dateLabel($tanggal),
            'nama'        => $this->patientName($patient, $p),
            'usia'        => $this->ageText($this->dateValue(data_get($patient, 'tanggal_lahir')), $tanggal),
            'orang_tua'   => trim(($this->text(data_get($patient, 'nama_ibu')) ?: '-') . ' / ' . ($this->text(data_get($patient, 'nama_ayah')) ?: '-')),
            'bb'          => $this->num($p->berat_badan ?? data_get($patient, 'berat_badan')),
            'tb'          => $this->num($p->tinggi_badan ?? data_get($patient, 'tinggi_badan')),
            'lk'          => $this->num($p->lingkar_kepala ?? data_get($patient, 'lingkar_kepala')),
            'lila'        => $this->num($p->lingkar_lengan ?? data_get($patient, 'lingkar_lengan')),
            'status_gizi' => $this->text($p->status_gizi ?? data_get($patient, 'status_gizi')),
            'imunisasi'   => '-',
            'keterangan'  => $this->text($p->catatan ?? $p->keluhan ?? $p->diagnosa ?? null),
        ];
    }

    private function remajaRow(Pemeriksaan $p, $patient, int $no, ?Carbon $tanggal): array
    {
        return [
            'no'            => $no,
            'tanggal'       => $this->dateLabel($tanggal),
            'nama'          => $this->patientName($patient, $p),
            'usia'          => $this->ageText($this->dateValue(data_get($patient, 'tanggal_lahir')), $tanggal),
            'sekolah_kelas' => trim(($this->text(data_get($patient, 'sekolah')) ?: '-') . ' / ' . ($this->text(data_get($patient, 'kelas')) ?: '-')),
            'bb'            => $this->num($p->berat_badan ?? data_get($patient, 'berat_badan')),
            'tb'            => $this->num($p->tinggi_badan ?? data_get($patient, 'tinggi_badan')),
            'imt'           => $this->num($p->imt ?? data_get($patient, 'imt')),
            'lp'            => $this->num($p->lingkar_perut ?? data_get($patient, 'lingkar_perut')),
            'lila'          => $this->num($p->lingkar_lengan ?? data_get($patient, 'lingkar_lengan')),
            'td'            => $this->text($p->tekanan_darah ?? data_get($patient, 'tekanan_darah')),
            'gds'           => $this->num($p->gula_darah ?? data_get($patient, 'gula_darah')),
            'keterangan'    => $this->text($p->catatan ?? $p->keluhan ?? $p->diagnosa ?? null),
        ];
    }

    private function lansiaRow(Pemeriksaan $p, $patient, int $no, ?Carbon $tanggal): array
    {
        return [
            'no'              => $no,
            'tanggal'         => $this->dateLabel($tanggal),
            'nama'            => $this->patientName($patient, $p),
            'usia'            => $this->ageText($this->dateValue(data_get($patient, 'tanggal_lahir')), $tanggal),
            'kemandirian'     => $this->kemandirian($p->kemandirian ?? data_get($patient, 'tingkat_kemandirian')),
            'bb'              => $this->num($p->berat_badan ?? data_get($patient, 'berat_badan')),
            'tb'              => $this->num($p->tinggi_badan ?? data_get($patient, 'tinggi_badan')),
            'imt'             => $this->num($p->imt ?? data_get($patient, 'imt')),
            'lp'              => $this->num($p->lingkar_perut ?? data_get($patient, 'lingkar_perut')),
            'td'              => $this->text($p->tekanan_darah ?? data_get($patient, 'tekanan_darah')),
            'gds'             => $this->num($p->gula_darah ?? data_get($patient, 'gula_darah')),
            'kolesterol'      => $this->num($p->kolesterol ?? data_get($patient, 'kolesterol')),
            'asam_urat'       => $this->num($p->asam_urat ?? data_get($patient, 'asam_urat')),
            'riwayat_keluhan' => $this->text($p->keluhan ?? $p->catatan ?? data_get($patient, 'keluhan') ?? data_get($patient, 'penyakit_bawaan')),
        ];
    }

    private function filterKategori($query, string $type): void
    {
        if (Schema::hasColumn('pemeriksaans', 'kategori_pasien')) {
            $query->where('kategori_pasien', $type);
            return;
        }

        if (Schema::hasColumn('pemeriksaans', 'jenis_sasaran')) {
            $query->where('jenis_sasaran', $type);
            return;
        }

        $directColumn = $type . '_id';

        if (Schema::hasColumn('pemeriksaans', $directColumn)) {
            $query->whereNotNull($directColumn);
        }
    }

    private function patientId($item, string $type)
    {
        $directColumn = $type . '_id';

        return $item->{$directColumn} ?? $item->pasien_id ?? $item->sasaran_id ?? null;
    }

    private function loadPatients(string $model, $ids)
    {
        if ($ids->isEmpty()) {
            return collect();
        }

        $table = (new $model)->getTable();

        if (!Schema::hasTable($table)) {
            return collect();
        }

        return $model::whereIn('id', $ids)->get()->keyBy('id');
    }

    private function emptyReport(array $config): array
    {
        return [
            'title' => $config['title'],
            'subtitle' => $config['subtitle'],
            'label' => $config['label'],
            'rows' => [],
            'groups' => [],
            'summary' => [
                ['label' => 'Total Sasaran', 'value' => 0],
                ['label' => 'Pemeriksaan', 'value' => 0],
            ],
            'notes' => $config['notes'],
        ];
    }

    private function dateColumn(): string
    {
        foreach (['tanggal_periksa', 'tanggal_pemeriksaan', 'tanggal_kunjungan', 'created_at'] as $column) {
            if (Schema::hasColumn('pemeriksaans', $column)) {
                return $column;
            }
        }

        return 'created_at';
    }

    private function modelCount(string $model): int
    {
        $table = (new $model)->getTable();

        return Schema::hasTable($table) ? $model::count() : 0;
    }

    private function setting(string $key, string $default = '-'): string
    {
        if (!Schema::hasTable('settings')) {
            return $default;
        }

        return DB::table('settings')->where('key', $key)->value('value') ?: $default;
    }

    private function patientName($patient, $pemeriksaan): string
    {
        return $this->text(
            data_get($patient, 'nama_lengkap')
            ?? data_get($patient, 'nama')
            ?? data_get($pemeriksaan, 'nama_pasien')
        );
    }

    private function dateValue($value): ?Carbon
    {
        try {
            return $value ? Carbon::parse($value, $this->tz) : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function dateLabel(?Carbon $date): string
    {
        return $date ? $date->format('d/m/Y') : '-';
    }

    private function ageText(?Carbon $birthDate, ?Carbon $referenceDate = null): string
    {
        if (!$birthDate) {
            return '-';
        }

        $diff = $birthDate->diff($referenceDate ?: now($this->tz));

        if ($diff->y > 0) {
            return $diff->y . ' th ' . $diff->m . ' bln';
        }

        return $diff->m > 0 ? $diff->m . ' bln' : $diff->d . ' hari';
    }

    private function num($value): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        if (!is_numeric($value)) {
            return (string) $value;
        }

        $number = number_format((float) $value, 1, ',', '.');

        return rtrim(rtrim($number, '0'), ',');
    }

    private function text($value): string
    {
        return filled($value) ? (string) $value : '-';
    }

    private function kemandirian($value): string
    {
        return match (strtolower((string) $value)) {
            'a', 'mandiri' => 'Mandiri',
            'b', 'bantuan_sebagian', 'bantuan sebagian', 'bantuan_ringan' => 'Bantuan Sebagian',
            'c', 'ketergantungan_penuh', 'ketergantungan penuh' => 'Ketergantungan Penuh',
            default => '-',
        };
    }
}