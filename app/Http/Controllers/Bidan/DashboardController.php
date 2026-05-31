<?php

namespace App\Http\Controllers\Bidan;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    private array $tableCache = [];
    private array $columnCache = [];

    public function index()
    {
        $stats = [
            'balita' => $this->safeTableCount('balitas'),
            'remaja' => $this->safeTableCount('remajas'),
            'lansia' => $this->safeTableCount('lansias'),

            'menunggu_validasi' => $this->countPemeriksaanStatus($this->pendingStatuses()),
            'tervalidasi' => $this->countPemeriksaanStatus($this->verifiedStatuses()),
            'perlu_revisi' => $this->countPemeriksaanStatus($this->revisionStatuses()),

            'pemeriksaan_hari_ini' => $this->countByDate(
                'pemeriksaans',
                ['tanggal_periksa', 'tanggal_pemeriksaan', 'tanggal_kunjungan', 'created_at'],
                'today'
            ),

            'pemeriksaan_bulan_ini' => $this->countByDate(
                'pemeriksaans',
                ['tanggal_periksa', 'tanggal_pemeriksaan', 'tanggal_kunjungan', 'created_at'],
                'month'
            ),

            'imunisasi_bulan_ini' => $this->countByDate(
                $this->firstExistingTable(['imunisasis', 'imunisasi']),
                ['tanggal_imunisasi', 'tanggal_pemberian', 'tanggal', 'created_at'],
                'month'
            ),

            'jadwal_aktif' => $this->countJadwalAktif(),
            'notifikasi_belum_dibaca' => $this->countUnreadNotifications(),
        ];

        $stats['total_sasaran'] = $stats['balita'] + $stats['remaja'] + $stats['lansia'];

        // Alias agar aman kalau Blade lama masih memakai nama key berbeda.
        $stats['menunggu_review'] = $stats['menunggu_validasi'];
        $stats['sudah_ditinjau'] = $stats['tervalidasi'];
        $stats['perlu_perbaikan'] = $stats['perlu_revisi'];

        $recentPemeriksaans = $this->latestPemeriksaans(5);
        $recentImunisasi = $this->latestImunisasi(4);
        $jadwalTerdekat = $this->upcomingJadwal(4);
        $notifications = $this->latestNotifications(4);
        $monthlyStats = $this->monthlyPemeriksaanStats(6);

        $statusSummary = $this->statusSummary($stats);
        $sasaranSummary = $this->sasaranSummary($stats);
        $operationalSummary = $this->operationalSummary($stats);

        return view('bidan.dashboard', compact(
            'stats',
            'recentPemeriksaans',
            'recentImunisasi',
            'jadwalTerdekat',
            'notifications',
            'monthlyStats',
            'statusSummary',
            'sasaranSummary',
            'operationalSummary'
        ));
    }

    private function latestPemeriksaans(int $limit = 5): array
    {
        try {
            if (!$this->hasTable('pemeriksaans')) {
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

            $query = DB::table('pemeriksaans')->orderByDesc($dateColumn);

            if ($this->hasColumn('pemeriksaans', 'id')) {
                $query->orderByDesc('id');
            }

            return $query
                ->limit($limit)
                ->get()
                ->map(function ($item) use ($dateColumn, $statusColumn) {
                    $kategori = $this->kategoriFromPemeriksaan($item);
                    $patientId = $this->patientIdFromPemeriksaan($item, $kategori);
                    $patient = $this->findPatient($kategori, $patientId, $item);

                    $date = $this->dateValue($item->{$dateColumn} ?? $item->created_at ?? null);
                    $statusRaw = $statusColumn ? ($item->{$statusColumn} ?? null) : null;

                    return [
                        'id' => $item->id ?? null,
                        'nama' => $patient['nama'] ?? 'Warga tidak ditemukan',
                        'nik' => $patient['nik'] ?? '-',
                        'kategori' => $this->kategoriLabel($kategori),
                        'kategori_raw' => $kategori,
                        'tanggal' => $date ? $date->translatedFormat('d M Y') : '-',
                        'waktu' => $date ? $date->format('H:i') . ' WIB' : '-',
                        'parameter' => $this->parameterRingkas($item, $kategori),
                        'status' => $this->statusLabel($statusRaw),
                        'status_raw' => $statusRaw,
                    ];
                })
                ->toArray();
        } catch (\Throwable) {
            return [];
        }
    }

    private function latestImunisasi(int $limit = 4): array
    {
        try {
            $table = $this->firstExistingTable(['imunisasis', 'imunisasi']);

            if (!$table) {
                return [];
            }

            $dateColumn = $this->firstExistingColumn($table, [
                'tanggal_imunisasi',
                'tanggal_pemberian',
                'tanggal',
                'created_at',
            ]) ?? 'created_at';

            $query = DB::table($table)->orderByDesc($dateColumn);

            if ($this->hasColumn($table, 'id')) {
                $query->orderByDesc('id');
            }

            return $query
                ->limit($limit)
                ->get()
                ->map(function ($item) use ($dateColumn, $table) {
                    $balita = $this->findBalitaFromImunisasi($item, $table);
                    $date = $this->dateValue($item->{$dateColumn} ?? $item->created_at ?? null);

                    return [
                        'id' => $item->id ?? null,
                        'nama' => $balita['nama'] ?? 'Balita tidak ditemukan',
                        'nik' => $balita['nik'] ?? '-',
                        'jenis' => $this->firstFilled([
                            $item->jenis_imunisasi ?? null,
                            $item->nama_imunisasi ?? null,
                            $item->jenis ?? null,
                            'Imunisasi',
                        ]),
                        'vaksin' => $this->firstFilled([
                            $item->vaksin ?? null,
                            $item->nama_vaksin ?? null,
                            '-',
                        ]),
                        'tanggal' => $date ? $date->translatedFormat('d M Y') : '-',
                    ];
                })
                ->toArray();
        } catch (\Throwable) {
            return [];
        }
    }

    private function upcomingJadwal(int $limit = 4): array
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

            $query = DB::table($table)
                ->whereDate($dateColumn, '>=', now('Asia/Jakarta')->toDateString())
                ->orderBy($dateColumn);

            if ($this->hasColumn($table, 'waktu_mulai')) {
                $query->orderBy('waktu_mulai');
            } elseif ($this->hasColumn($table, 'jam_mulai')) {
                $query->orderBy('jam_mulai');
            }

            if ($this->hasColumn($table, 'status')) {
                $query->where(function ($q) {
                    $q->whereNull('status')
                        ->orWhereNotIn('status', ['selesai', 'dibatalkan', 'batal']);
                });
            }

            return $query
                ->limit($limit)
                ->get()
                ->map(function ($item) use ($dateColumn) {
                    $date = $this->dateValue($item->{$dateColumn} ?? null);

                    $waktuMulai = $this->firstFilled([
                        $item->waktu_mulai ?? null,
                        $item->jam_mulai ?? null,
                        $item->jam ?? null,
                        $item->waktu ?? null,
                        null,
                    ]);

                    $waktuSelesai = $this->firstFilled([
                        $item->waktu_selesai ?? null,
                        $item->jam_selesai ?? null,
                        null,
                    ]);

                    return [
                        'id' => $item->id ?? null,
                        'judul' => $this->firstFilled([
                            $item->judul ?? null,
                            $item->nama_kegiatan ?? null,
                            $item->kegiatan ?? null,
                            $item->nama_jadwal ?? null,
                            'Jadwal Posyandu',
                        ]),
                        'tanggal' => $date ? $date->translatedFormat('d M Y') : '-',
                        'waktu' => $this->formatRangeJam($waktuMulai, $waktuSelesai),
                        'lokasi' => $this->firstFilled([
                            $item->lokasi ?? null,
                            $item->tempat ?? null,
                            '-',
                        ]),
                        'target' => $this->targetLabel(
                            $item->target_peserta
                            ?? $item->sasaran
                            ?? $item->kategori
                            ?? null
                        ),
                    ];
                })
                ->toArray();
        } catch (\Throwable) {
            return [];
        }
    }

    private function latestNotifications(int $limit = 4): array
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

            $orderColumn = $this->hasColumn($table, 'created_at') ? 'created_at' : 'id';

            $query = DB::table($table)
                ->orderByDesc($orderColumn)
                ->limit($limit);

            if ($this->hasColumn($table, 'user_id')) {
                $query->where(function ($q) {
                    $q->where('user_id', Auth::id())
                        ->orWhereNull('user_id');
                });
            }

            if ($this->hasColumn($table, 'role')) {
                $query->where(function ($q) {
                    $q->where('role', 'bidan')
                        ->orWhereNull('role');
                });
            }

            return $query
                ->get()
                ->map(function ($item) {
                    $date = $this->dateValue($item->created_at ?? null);

                    return [
                        'id' => $item->id ?? null,
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
                        'is_read' => (bool) (
                            $item->is_read
                            ?? $item->dibaca
                            ?? false
                        ),
                    ];
                })
                ->toArray();
        } catch (\Throwable) {
            return [];
        }
    }

    private function monthlyPemeriksaanStats(int $months = 6): array
    {
        $base = now('Asia/Jakarta')->startOfMonth();

        $items = collect(range($months - 1, 0))
            ->map(function ($monthOffset) use ($base) {
                $date = $base->copy()
                    ->subMonthsNoOverflow($monthOffset)
                    ->startOfMonth();

                return [
                    'date' => $date->toDateString(),
                    'label' => $date->translatedFormat('M Y'),
                    'short' => $date->translatedFormat('M'),
                    'count' => 0,
                ];
            });

        try {
            if (!$this->hasTable('pemeriksaans')) {
                return $items->toArray();
            }

            $dateColumn = $this->firstExistingColumn('pemeriksaans', [
                'tanggal_periksa',
                'tanggal_pemeriksaan',
                'tanggal_kunjungan',
                'created_at',
            ]);

            if (!$dateColumn) {
                return $items->toArray();
            }

            return $items
                ->map(function ($item) use ($dateColumn) {
                    $date = Carbon::parse($item['date'], 'Asia/Jakarta');

                    $item['count'] = DB::table('pemeriksaans')
                        ->whereBetween($dateColumn, [
                            $date->copy()->startOfMonth(),
                            $date->copy()->endOfMonth(),
                        ])
                        ->count();

                    return $item;
                })
                ->toArray();
        } catch (\Throwable) {
            return $items->toArray();
        }
    }

    private function statusSummary(array $stats): array
    {
        $pending = (int) ($stats['menunggu_validasi'] ?? 0);
        $verified = (int) ($stats['tervalidasi'] ?? 0);
        $revision = (int) ($stats['perlu_revisi'] ?? 0);
        $total = $pending + $verified + $revision;

        return [
            'total' => $total,
            'pending' => $pending,
            'verified' => $verified,
            'revision' => $revision,
            'pending_percent' => $total > 0 ? round(($pending / $total) * 100, 2) : 0,
            'verified_percent' => $total > 0 ? round(($verified / $total) * 100, 2) : 0,
            'revision_percent' => $total > 0 ? round(($revision / $total) * 100, 2) : 0,
        ];
    }

    private function sasaranSummary(array $stats): array
    {
        $total = max(1, (int) ($stats['total_sasaran'] ?? 0));

        return [
            [
                'key' => 'balita',
                'label' => 'Balita',
                'value' => (int) ($stats['balita'] ?? 0),
                'percent' => round(((int) ($stats['balita'] ?? 0) / $total) * 100),
                'icon' => 'ph-baby',
            ],
            [
                'key' => 'remaja',
                'label' => 'Remaja',
                'value' => (int) ($stats['remaja'] ?? 0),
                'percent' => round(((int) ($stats['remaja'] ?? 0) / $total) * 100),
                'icon' => 'ph-user-focus',
            ],
            [
                'key' => 'lansia',
                'label' => 'Lansia',
                'value' => (int) ($stats['lansia'] ?? 0),
                'percent' => round(((int) ($stats['lansia'] ?? 0) / $total) * 100),
                'icon' => 'ph-heartbeat',
            ],
        ];
    }

    private function operationalSummary(array $stats): array
    {
        return [
            [
                'label' => 'Pemeriksaan Bulan Ini',
                'value' => (int) ($stats['pemeriksaan_bulan_ini'] ?? 0),
                'icon' => 'ph-stethoscope',
            ],
            [
                'label' => 'Menunggu Validasi',
                'value' => (int) ($stats['menunggu_validasi'] ?? 0),
                'icon' => 'ph-clock-countdown',
            ],
            [
                'label' => 'Jadwal Aktif',
                'value' => (int) ($stats['jadwal_aktif'] ?? 0),
                'icon' => 'ph-calendar-check',
            ],
            [
                'label' => 'Imunisasi Bulan Ini',
                'value' => (int) ($stats['imunisasi_bulan_ini'] ?? 0),
                'icon' => 'ph-syringe',
            ],
        ];
    }

    private function countPemeriksaanStatus(array $statuses): int
    {
        try {
            if (!$this->hasTable('pemeriksaans')) {
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

            $normalStatuses = collect($statuses)
                ->filter(fn ($status) => $status !== null && $status !== '')
                ->values()
                ->all();

            $hasNull = collect($statuses)->contains(null);
            $hasEmpty = collect($statuses)->contains('');

            return DB::table('pemeriksaans')
                ->where(function ($query) use ($statusColumn, $normalStatuses, $hasNull, $hasEmpty) {
                    if (!empty($normalStatuses)) {
                        $query->whereIn($statusColumn, $normalStatuses);
                    }

                    if ($hasNull) {
                        if (!empty($normalStatuses)) {
                            $query->orWhereNull($statusColumn);
                        } else {
                            $query->whereNull($statusColumn);
                        }
                    }

                    if ($hasEmpty) {
                        if (!empty($normalStatuses) || $hasNull) {
                            $query->orWhere($statusColumn, '');
                        } else {
                            $query->where($statusColumn, '');
                        }
                    }
                })
                ->count();
        } catch (\Throwable) {
            return 0;
        }
    }

    private function countByDate(?string $table, array $dateColumns, string $mode): int
    {
        try {
            if (!$table || !$this->hasTable($table)) {
                return 0;
            }

            $dateColumn = $this->firstExistingColumn($table, $dateColumns);

            if (!$dateColumn) {
                return 0;
            }

            $query = DB::table($table);

            if ($mode === 'today') {
                return $query
                    ->whereDate($dateColumn, now('Asia/Jakarta')->toDateString())
                    ->count();
            }

            return $query
                ->whereBetween($dateColumn, [
                    now('Asia/Jakarta')->startOfMonth(),
                    now('Asia/Jakarta')->endOfMonth(),
                ])
                ->count();
        } catch (\Throwable) {
            return 0;
        }
    }

    private function countJadwalAktif(): int
    {
        try {
            $table = $this->firstExistingTable([
                'jadwal_posyandus',
                'jadwal_posyandu',
                'jadwals',
            ]);

            if (!$table) {
                return 0;
            }

            $dateColumn = $this->firstExistingColumn($table, [
                'tanggal',
                'tanggal_kegiatan',
                'tanggal_jadwal',
                'start_date',
                'created_at',
            ]);

            if (!$dateColumn) {
                return 0;
            }

            $query = DB::table($table)
                ->whereDate($dateColumn, '>=', now('Asia/Jakarta')->toDateString());

            if ($this->hasColumn($table, 'status')) {
                $query->where(function ($q) {
                    $q->whereNull('status')
                        ->orWhereNotIn('status', ['selesai', 'dibatalkan', 'batal']);
                });
            }

            return $query->count();
        } catch (\Throwable) {
            return 0;
        }
    }

    private function countUnreadNotifications(): int
    {
        try {
            $table = $this->firstExistingTable([
                'notifikasis',
                'notifikasi',
                'notifications',
            ]);

            if (!$table) {
                return 0;
            }

            $query = DB::table($table);

            if ($this->hasColumn($table, 'user_id')) {
                $query->where(function ($q) {
                    $q->where('user_id', Auth::id())
                        ->orWhereNull('user_id');
                });
            }

            if ($this->hasColumn($table, 'role')) {
                $query->where(function ($q) {
                    $q->where('role', 'bidan')
                        ->orWhereNull('role');
                });
            }

            if ($this->hasColumn($table, 'is_read')) {
                return $query->where('is_read', false)->count();
            }

            if ($this->hasColumn($table, 'dibaca')) {
                return $query->where('dibaca', false)->count();
            }

            if ($this->hasColumn($table, 'read_at')) {
                return $query->whereNull('read_at')->count();
            }

            return 0;
        } catch (\Throwable) {
            return 0;
        }
    }

    private function parameterRingkas(object $item, string $kategori): array
    {
        if ($kategori === 'lansia') {
            return [
                'Tensi' => $this->displayValue($item->tekanan_darah ?? null),
                'Gula' => $this->displayValue($item->gula_darah ?? null, 'mg/dL'),
                'Kolesterol' => $this->displayValue($item->kolesterol ?? null, 'mg/dL'),
                'Kemandirian' => $this->displayValue($item->tingkat_kemandirian ?? null),
            ];
        }

        if ($kategori === 'remaja') {
            return [
                'BB' => $this->displayValue($item->berat_badan ?? null, 'kg'),
                'TB' => $this->displayValue($item->tinggi_badan ?? null, 'cm'),
                'IMT' => $this->displayValue($item->imt ?? null),
                'Tensi' => $this->displayValue($item->tekanan_darah ?? null),
            ];
        }

        return [
            'BB' => $this->displayValue($item->berat_badan ?? null, 'kg'),
            'TB' => $this->displayValue($item->tinggi_badan ?? null, 'cm'),
            'LK' => $this->displayValue($item->lingkar_kepala ?? null, 'cm'),
            'Gizi' => $this->displayValue($item->status_gizi ?? null),
        ];
    }

    private function kategoriFromPemeriksaan(object $item): string
    {
        $raw = strtolower((string) $this->firstFilled([
            $item->kategori_pasien ?? null,
            $item->jenis_sasaran ?? null,
            $item->pasien_type ?? null,
            '',
        ]));

        if (str_contains($raw, 'balita')) {
            return 'balita';
        }

        if (str_contains($raw, 'remaja')) {
            return 'remaja';
        }

        if (str_contains($raw, 'lansia')) {
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

        return 'balita';
    }

    private function patientIdFromPemeriksaan(object $item, string $kategori): mixed
    {
        $directColumn = $kategori . '_id';

        return $item->{$directColumn}
            ?? $item->pasien_id
            ?? $item->sasaran_id
            ?? null;
    }

    private function findPatient(string $kategori, mixed $id, ?object $pemeriksaan = null): ?array
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

        if (!$table || !$this->hasTable($table)) {
            return null;
        }

        $item = DB::table($table)->where('id', $id)->first();

        if (!$item) {
            return null;
        }

        return [
            'nama' => $this->firstFilled([
                $item->nama_lengkap ?? null,
                $item->nama ?? null,
                $item->nama_balita ?? null,
                $item->nama_remaja ?? null,
                $item->nama_lansia ?? null,
                '-',
            ]),
            'nik' => $this->firstFilled([
                $item->nik ?? null,
                $item->nik_anak ?? null,
                '-',
            ]),
        ];
    }

    private function patientFromKunjungan(?object $pemeriksaan): ?array
    {
        if (!$pemeriksaan || empty($pemeriksaan->kunjungan_id)) {
            return null;
        }

        if (!$this->hasTable('kunjungans')) {
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

        if (!$table || !$this->hasTable($table)) {
            return null;
        }

        $pasien = DB::table($table)
            ->where('id', $kunjungan->pasien_id)
            ->first();

        if (!$pasien) {
            return null;
        }

        return [
            'nama' => $this->firstFilled([
                $pasien->nama_lengkap ?? null,
                $pasien->nama ?? null,
                '-',
            ]),
            'nik' => $this->firstFilled([
                $pasien->nik ?? null,
                $pasien->nik_anak ?? null,
                '-',
            ]),
        ];
    }

    private function findBalitaFromImunisasi(object $item, string $table): ?array
    {
        if ($this->hasColumn($table, 'balita_id') && !empty($item->balita_id)) {
            return $this->findPatient('balita', $item->balita_id);
        }

        if (!empty($item->kunjungan_id)) {
            return $this->patientFromKunjungan((object) [
                'kunjungan_id' => $item->kunjungan_id,
            ]);
        }

        return null;
    }

    private function safeTableCount(string $table): int
    {
        try {
            if (!$this->hasTable($table)) {
                return 0;
            }

            return DB::table($table)->count();
        } catch (\Throwable) {
            return 0;
        }
    }

    private function pendingStatuses(): array
    {
        return [
            null,
            '',
            'pending',
            'menunggu',
            'belum_divalidasi',
        ];
    }

    private function verifiedStatuses(): array
    {
        return [
            'verified',
            'tervalidasi',
            'approved',
        ];
    }

    private function revisionStatuses(): array
    {
        return [
            'rejected',
            'ditolak',
            'revisi',
            'perlu_revisi',
            'perlu_perbaikan',
        ];
    }

    private function statusLabel(mixed $status): string
    {
        $status = strtolower((string) $status);

        return match ($status) {
            'verified', 'tervalidasi', 'approved' => 'Tervalidasi',
            'rejected', 'ditolak', 'revisi', 'perlu_revisi', 'perlu_perbaikan' => 'Perlu Revisi',
            default => 'Menunggu Validasi',
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

    private function targetLabel(mixed $target): string
    {
        $target = strtolower((string) $target);

        return match ($target) {
            'balita' => 'Balita',
            'remaja' => 'Remaja',
            'lansia' => 'Lansia',
            'semua', 'all', 'umum' => 'Semua Sasaran',
            default => $target ? ucfirst($target) : 'Semua Sasaran',
        };
    }

    private function displayValue(mixed $value, string $suffix = ''): string
    {
        if ($value === null || $value === '' || $value === '-') {
            return 'Belum diisi';
        }

        return trim((string) $value . ' ' . $suffix);
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

    private function dateValue(mixed $value): ?Carbon
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

    private function formatRangeJam(mixed $start, mixed $end = null): string
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

    private function formatJam(mixed $value): string
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

    private function firstExistingTable(array $tables): ?string
    {
        foreach ($tables as $table) {
            if ($this->hasTable($table)) {
                return $table;
            }
        }

        return null;
    }

    private function firstExistingColumn(string $table, array $columns): ?string
    {
        if (!$this->hasTable($table)) {
            return null;
        }

        foreach ($columns as $column) {
            if ($this->hasColumn($table, $column)) {
                return $column;
            }
        }

        return null;
    }

    private function hasTable(?string $table): bool
    {
        if (!$table) {
            return false;
        }

        if (array_key_exists($table, $this->tableCache)) {
            return $this->tableCache[$table];
        }

        try {
            return $this->tableCache[$table] = Schema::hasTable($table);
        } catch (\Throwable) {
            return $this->tableCache[$table] = false;
        }
    }

    private function hasColumn(string $table, string $column): bool
    {
        $key = $table . '.' . $column;

        if (array_key_exists($key, $this->columnCache)) {
            return $this->columnCache[$key];
        }

        try {
            return $this->columnCache[$key] = Schema::hasColumn($table, $column);
        } catch (\Throwable) {
            return $this->columnCache[$key] = false;
        }
    }
}