<?php

namespace App\Http\Controllers\Bidan;

use App\Http\Controllers\Controller;
use App\Models\Balita;
use App\Models\Lansia;
use App\Models\Remaja;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'balita' => $this->safeModelCount(Balita::class),
            'remaja' => $this->safeModelCount(Remaja::class),
            'lansia' => $this->safeModelCount(Lansia::class),

            'menunggu_review' => $this->countPemeriksaanStatus([
                'pending',
                'menunggu',
                'menunggu_review',
                null,
            ]),

            'sudah_ditinjau' => $this->countPemeriksaanStatus([
                'verified',
                'valid',
                'terverifikasi',
                'approved',
                'sudah_ditinjau',
                'ditinjau',
            ]),

            'perlu_perbaikan' => $this->countPemeriksaanStatus([
                'rejected',
                'ditolak',
                'revisi',
                'perlu_perbaikan',
            ]),

            'pemeriksaan_hari_ini' => $this->countPemeriksaanToday(),
            'pemeriksaan_bulan_ini' => $this->countPemeriksaanThisMonth(),
        ];

        $recentPemeriksaans = $this->latestPemeriksaans(6);
        $jadwalTerdekat = $this->upcomingJadwal(5);
        $notifications = $this->latestNotifications(5);
        $weeklyStats = $this->weeklyPemeriksaanStats();

        return view('bidan.dashboard', compact(
            'stats',
            'recentPemeriksaans',
            'jadwalTerdekat',
            'notifications',
            'weeklyStats'
        ));
    }

    private function safeModelCount(string $model): int
    {
        try {
            if (!class_exists($model)) {
                return 0;
            }

            $instance = new $model();
            $table = $instance->getTable();

            if (!Schema::hasTable($table)) {
                return 0;
            }

            return $model::count();
        } catch (\Throwable) {
            return 0;
        }
    }

    private function countPemeriksaanStatus(array $values): int
    {
        try {
            if (!Schema::hasTable('pemeriksaans')) {
                return 0;
            }

            $statusColumn = $this->firstExistingColumn('pemeriksaans', [
                'status_verifikasi',
                'status_validasi',
                'status',
            ]);

            if (!$statusColumn) {
                return 0;
            }

            $query = DB::table('pemeriksaans');

            $normalValues = collect($values)
                ->filter(fn ($value) => $value !== null)
                ->values()
                ->all();

            $hasNull = collect($values)->contains(null);

            $query->where(function ($q) use ($statusColumn, $normalValues, $hasNull) {
                if (!empty($normalValues)) {
                    $q->whereIn($statusColumn, $normalValues);
                }

                if ($hasNull) {
                    if (!empty($normalValues)) {
                        $q->orWhereNull($statusColumn);
                    } else {
                        $q->whereNull($statusColumn);
                    }
                }
            });

            return $query->count();
        } catch (\Throwable) {
            return 0;
        }
    }

    private function countPemeriksaanToday(): int
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
            ]);

            if (!$dateColumn) {
                return 0;
            }

            return DB::table('pemeriksaans')
                ->whereDate($dateColumn, now('Asia/Jakarta')->toDateString())
                ->count();
        } catch (\Throwable) {
            return 0;
        }
    }

    private function countPemeriksaanThisMonth(): int
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
            ]);

            if (!$dateColumn) {
                return 0;
            }

            return DB::table('pemeriksaans')
                ->whereMonth($dateColumn, now('Asia/Jakarta')->month)
                ->whereYear($dateColumn, now('Asia/Jakarta')->year)
                ->count();
        } catch (\Throwable) {
            return 0;
        }
    }

    private function latestPemeriksaans(int $limit = 6): array
    {
        try {
            if (!Schema::hasTable('pemeriksaans')) {
                return [];
            }

            $dateColumn = $this->firstExistingColumn('pemeriksaans', [
                'tanggal_periksa',
                'tanggal_pemeriksaan',
                'tanggal_kunjungan',
                'created_at',
            ]) ?? 'created_at';

            $statusColumn = $this->firstExistingColumn('pemeriksaans', [
                'status_verifikasi',
                'status_validasi',
                'status',
            ]);

            $items = DB::table('pemeriksaans')
                ->orderByDesc($dateColumn)
                ->orderByDesc('id')
                ->limit($limit)
                ->get();

            return $items->map(function ($item) use ($dateColumn, $statusColumn) {
                $kategori = $this->kategoriFromPemeriksaan($item);
                $patientId = $this->patientId($item, $kategori);
                $patient = $this->findPatient($kategori, $patientId, $item);
                $date = $this->dateValue($item->{$dateColumn} ?? $item->created_at ?? null);

                return [
                    'id' => $item->id ?? null,
                    'nama' => $patient['nama'] ?? '-',
                    'nik' => $patient['nik'] ?? '-',
                    'kategori' => $this->kategoriLabel($kategori),
                    'kategori_raw' => $kategori,
                    'tanggal' => $date ? $date->translatedFormat('d M Y') : '-',
                    'waktu' => $date ? $date->format('H:i') . ' WIB' : '-',
                    'bb' => $this->number($item->berat_badan ?? null, ' kg'),
                    'tb' => $this->number($item->tinggi_badan ?? null, ' cm'),
                    'imt' => $this->number($item->imt ?? null),
                    'tensi' => $item->tekanan_darah ?? '-',
                    'status' => $this->statusLabel($statusColumn ? ($item->{$statusColumn} ?? null) : null),
                    'status_raw' => $statusColumn ? ($item->{$statusColumn} ?? null) : null,
                    'catatan' => $this->firstFilled([
                        $item->diagnosa ?? null,
                        $item->catatan_bidan ?? null,
                        $item->catatan_validasi ?? null,
                        $item->keluhan ?? null,
                        $item->catatan ?? null,
                        '-',
                    ]),
                ];
            })->toArray();
        } catch (\Throwable) {
            return [];
        }
    }

    private function upcomingJadwal(int $limit = 5): array
    {
        try {
            $table = $this->firstExistingTable([
                'jadwal_posyandus',
                'jadwal_posyandu',
                'jadwals',
            ]);

            if (!$table) {
                return [];
            }

            $dateColumn = $this->firstExistingColumn($table, [
                'tanggal',
                'tanggal_kegiatan',
                'tanggal_jadwal',
                'start_date',
                'created_at',
            ]);

            if (!$dateColumn) {
                return [];
            }

            $items = DB::table($table)
                ->whereDate($dateColumn, '>=', now('Asia/Jakarta')->toDateString())
                ->orderBy($dateColumn, 'asc')
                ->limit($limit)
                ->get();

            return $items->map(function ($item) use ($dateColumn) {
                $date = $this->dateValue($item->{$dateColumn} ?? null);

                $waktuMulai = $this->firstFilled([
                    $item->waktu_mulai ?? null,
                    $item->jam_mulai ?? null,
                    $item->jam ?? null,
                    $item->waktu ?? null,
                ]);

                $waktuSelesai = $this->firstFilled([
                    $item->waktu_selesai ?? null,
                    $item->jam_selesai ?? null,
                    null,
                ]);

                $waktu = $this->formatRangeJam($waktuMulai, $waktuSelesai);

                return [
                    'judul' => $this->firstFilled([
                        $item->judul ?? null,
                        $item->nama_kegiatan ?? null,
                        $item->kegiatan ?? null,
                        $item->nama_jadwal ?? null,
                        'Jadwal Posyandu',
                    ]),
                    'tanggal' => $date ? $date->translatedFormat('d M Y') : '-',
                    'waktu' => $waktu,
                    'lokasi' => $this->firstFilled([
                        $item->lokasi ?? null,
                        $item->tempat ?? null,
                        '-',
                    ]),
                ];
            })->toArray();
        } catch (\Throwable) {
            return [];
        }
    }

    private function latestNotifications(int $limit = 5): array
    {
        try {
            $table = $this->firstExistingTable([
                'notifikasis',
                'notifikasi',
                'notifications',
            ]);

            if (!$table) {
                return [];
            }

            $query = DB::table($table)->orderByDesc('created_at')->limit($limit);

            if (Schema::hasColumn($table, 'user_id')) {
                $query->where('user_id', Auth::id());
            }

            $items = $query->get();

            return $items->map(function ($item) {
                $date = $this->dateValue($item->created_at ?? null);

                return [
                    'title' => $this->firstFilled([
                        $item->title ?? null,
                        $item->judul ?? null,
                        'Notifikasi',
                    ]),
                    'message' => $this->firstFilled([
                        $item->message ?? null,
                        $item->pesan ?? null,
                        $item->deskripsi ?? null,
                        '-',
                    ]),
                    'time' => $date ? $date->diffForHumans() : '-',
                    'is_read' => (bool) ($item->is_read ?? $item->dibaca ?? false),
                ];
            })->toArray();
        } catch (\Throwable) {
            return [];
        }
    }

    private function weeklyPemeriksaanStats(): array
    {
        $days = collect(range(6, 0))->map(function ($day) {
            $date = now('Asia/Jakarta')->subDays($day);

            return [
                'date' => $date->toDateString(),
                'label' => $date->translatedFormat('d M'),
                'count' => 0,
            ];
        });

        try {
            if (!Schema::hasTable('pemeriksaans')) {
                return $days->toArray();
            }

            $dateColumn = $this->firstExistingColumn('pemeriksaans', [
                'tanggal_periksa',
                'tanggal_pemeriksaan',
                'tanggal_kunjungan',
                'created_at',
            ]);

            if (!$dateColumn) {
                return $days->toArray();
            }

            $start = now('Asia/Jakarta')->subDays(6)->startOfDay();
            $end = now('Asia/Jakarta')->endOfDay();

            $rows = DB::table('pemeriksaans')
                ->selectRaw("DATE({$dateColumn}) as tanggal, COUNT(*) as total")
                ->whereBetween($dateColumn, [$start, $end])
                ->groupBy('tanggal')
                ->pluck('total', 'tanggal');

            return $days->map(function ($item) use ($rows) {
                $item['count'] = (int) ($rows[$item['date']] ?? 0);

                return $item;
            })->toArray();
        } catch (\Throwable) {
            return $days->toArray();
        }
    }

    private function kategoriFromPemeriksaan($item): string
    {
        $value = strtolower((string) $this->firstFilled([
            $item->kategori_pasien ?? null,
            $item->jenis_sasaran ?? null,
            $item->pasien_type ?? null,
            '-',
        ]));

        if (str_contains($value, 'balita')) {
            return 'balita';
        }

        if (str_contains($value, 'remaja')) {
            return 'remaja';
        }

        if (str_contains($value, 'lansia')) {
            return 'lansia';
        }

        if (!empty($item->balita_id)) {
            return 'balita';
        }

        if (!empty($item->remaja_id)) {
            return 'remaja';
        }

        if (!empty($item->lansia_id)) {
            return 'lansia';
        }

        return '-';
    }

    private function patientId($item, string $kategori)
    {
        $directColumn = $kategori . '_id';

        return $item->pasien_id
            ?? $item->{$directColumn}
            ?? $item->sasaran_id
            ?? null;
    }

    private function findPatient(string $kategori, $id, $pemeriksaan = null): ?array
    {
        $fromKunjungan = $this->patientFromKunjungan($pemeriksaan);

        if ($fromKunjungan) {
            return $fromKunjungan;
        }

        if (!$id) {
            return null;
        }

        $table = match ($kategori) {
            'balita' => 'balitas',
            'remaja' => 'remajas',
            'lansia' => 'lansias',
            default => null,
        };

        if (!$table || !Schema::hasTable($table)) {
            return null;
        }

        $item = DB::table($table)->where('id', $id)->first();

        if (!$item) {
            return null;
        }

        return [
            'nama' => $item->nama_lengkap ?? $item->nama ?? '-',
            'nik' => $item->nik ?? '-',
        ];
    }

    private function patientFromKunjungan($pemeriksaan): ?array
    {
        if (!$pemeriksaan || empty($pemeriksaan->kunjungan_id)) {
            return null;
        }

        if (!Schema::hasTable('kunjungans')) {
            return null;
        }

        $kunjungan = DB::table('kunjungans')
            ->where('id', $pemeriksaan->kunjungan_id)
            ->first();

        if (!$kunjungan || empty($kunjungan->pasien_id)) {
            return null;
        }

        $pasienType = strtolower((string) ($kunjungan->pasien_type ?? ''));

        $table = null;

        if (str_contains($pasienType, 'balita')) {
            $table = 'balitas';
        }

        if (str_contains($pasienType, 'remaja')) {
            $table = 'remajas';
        }

        if (str_contains($pasienType, 'lansia')) {
            $table = 'lansias';
        }

        if (!$table || !Schema::hasTable($table)) {
            return null;
        }

        $pasien = DB::table($table)
            ->where('id', $kunjungan->pasien_id)
            ->first();

        if (!$pasien) {
            return null;
        }

        return [
            'nama' => $pasien->nama_lengkap ?? $pasien->nama ?? '-',
            'nik' => $pasien->nik ?? '-',
        ];
    }

    private function firstExistingTable(array $tables): ?string
    {
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                return $table;
            }
        }

        return null;
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

    private function statusLabel($status): string
    {
        return match (strtolower((string) $status)) {
            'verified', 'valid', 'terverifikasi', 'approved', 'sudah_ditinjau', 'ditinjau' => 'Sudah Ditinjau',
            'rejected', 'ditolak', 'revisi', 'perlu_perbaikan' => 'Perlu Perbaikan',
            default => 'Menunggu Review',
        };
    }

    private function kategoriLabel(string $kategori): string
    {
        return match ($kategori) {
            'balita' => 'Balita',
            'remaja' => 'Remaja',
            'lansia' => 'Lansia',
            default => 'Sasaran',
        };
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

    private function firstFilled(array $values): string
    {
        foreach ($values as $value) {
            if ($value !== null && $value !== '') {
                return (string) $value;
            }
        }

        return '-';
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

    private function formatRangeJam($start, $end = null): string
    {
        $startText = $this->formatJam($start);
        $endText = $this->formatJam($end);

        if ($startText !== '-' && $endText !== '-') {
            return "{$startText} - {$endText} WIB";
        }

        if ($startText !== '-') {
            return "{$startText} WIB";
        }

        return '-';
    }

    private function formatJam($value): string
    {
        if (!$value || $value === '-') {
            return '-';
        }

        try {
            return Carbon::parse($value)->format('H:i');
        } catch (\Throwable) {
            return (string) $value;
        }
    }
}