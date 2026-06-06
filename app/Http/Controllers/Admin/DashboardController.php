<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index()
    {
        $roleStats = [
            'admin' => $this->countUsersByRole('admin'),
            'bidan' => $this->countUsersByRole('bidan'),
            'kader' => $this->countUsersByRole('kader'),
            'user' => $this->countUsersByRole('user'),
        ];

        $accountStats = [
            'total' => $this->countTable('users'),
            'aktif' => $this->countUsersByStatus('active'),
            'nonaktif' => $this->countUsersByStatus('inactive'),
        ];

        $sasaranStats = [
            'balita' => $this->countTable('balitas'),
            'remaja' => $this->countTable('remajas'),
            'lansia' => $this->countTable('lansias'),
            'total' => $this->countTable('balitas') + $this->countTable('remajas') + $this->countTable('lansias'),
        ];

        $serviceStats = [
            'jadwal' => $this->countTable('jadwals'),
            'jadwal_aktif' => $this->countByColumnValue('jadwals', 'status', 'aktif'),
            'pemeriksaan' => $this->countTable('pemeriksaans'),
            'imunisasi' => $this->countTable('imunisasis'),
            'absensi' => $this->countTable('absensi_posyandus'),
            'pengukuran' => $this->countFirstAvailableTable(['pengukuran_fisiks', 'pengukurans']),
            'laporan' => $this->countFirstAvailableTable(['laporan_bulanans', 'laporans']),
        ];

        $monthlySeries = $this->monthlySeries();

        $recentUsers = $this->recentUsers();

        $recentJadwals = $this->recentRows('jadwals', [
            'id',
            'judul',
            'tanggal',
            'waktu_mulai',
            'waktu_selesai',
            'lokasi',
            'kategori',
            'target_peserta',
            'status',
            'created_at',
        ]);

        $recentPemeriksaans = $this->recentRows('pemeriksaans', [
            'id',
            'nama',
            'pasien_nama',
            'pasien_type',
            'pasien_id',
            'status',
            'created_at',
            'updated_at',
        ], 5);

        return view('admin.dashboard', compact(
            'roleStats',
            'accountStats',
            'sasaranStats',
            'serviceStats',
            'monthlySeries',
            'recentUsers',
            'recentJadwals',
            'recentPemeriksaans'
        ));
    }

    private function countUsersByRole(string $role): int
    {
        if (! Schema::hasTable('users') || ! Schema::hasColumn('users', 'role')) {
            return 0;
        }

        return User::where('role', $role)->count();
    }

    private function countUsersByStatus(string $status): int
    {
        if (! Schema::hasTable('users') || ! Schema::hasColumn('users', 'status')) {
            return 0;
        }

        return User::where('status', $status)->count();
    }

    private function countTable(string $table): int
    {
        if (! Schema::hasTable($table)) {
            return 0;
        }

        return DB::table($table)->count();
    }

    private function countByColumnValue(string $table, string $column, string $value): int
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return 0;
        }

        return DB::table($table)
            ->where($column, $value)
            ->count();
    }

    private function countFirstAvailableTable(array $tables): int
    {
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                return DB::table($table)->count();
            }
        }

        return 0;
    }

    private function recentUsers(int $limit = 6)
    {
        if (! Schema::hasTable('users')) {
            return collect();
        }

        $query = User::query()
            ->select($this->existingColumns('users', [
                'id',
                'name',
                'email',
                'nik',
                'role',
                'status',
                'created_at',
            ]));

        if (Schema::hasColumn('users', 'role')) {
            $query->whereIn('role', ['bidan', 'kader', 'user']);
        }

        if (Schema::hasColumn('users', 'created_at')) {
            $query->latest('created_at');
        } else {
            $query->latest('id');
        }

        return $query->limit($limit)->get();
    }

    private function recentRows(string $table, array $columns, int $limit = 6)
    {
        if (! Schema::hasTable($table)) {
            return collect();
        }

        $selectedColumns = $this->existingColumns($table, $columns);

        if (empty($selectedColumns)) {
            return collect();
        }

        $query = DB::table($table)->select($selectedColumns);

        if (Schema::hasColumn($table, 'created_at')) {
            $query->latest('created_at');
        } else {
            $query->latest('id');
        }

        return $query->limit($limit)->get();
    }

    private function monthlySeries(): array
    {
        $months = collect(range(5, 0))
            ->map(function ($index) {
                $date = now()->subMonths($index)->startOfMonth();

                return [
                    'key' => $date->format('Y-m'),
                    'label' => Carbon::parse($date)->translatedFormat('M'),
                    'start' => $date->copy()->startOfMonth(),
                    'end' => $date->copy()->endOfMonth(),
                ];
            });

        $akun = [];
        $pemeriksaan = [];
        $jadwal = [];

        foreach ($months as $month) {
            $akun[] = $this->countMonthly('users', 'created_at', $month['start'], $month['end']);
            $pemeriksaan[] = $this->countMonthly('pemeriksaans', 'created_at', $month['start'], $month['end']);

            if (Schema::hasTable('jadwals') && Schema::hasColumn('jadwals', 'tanggal')) {
                $jadwal[] = $this->countMonthly('jadwals', 'tanggal', $month['start'], $month['end']);
            } else {
                $jadwal[] = $this->countMonthly('jadwals', 'created_at', $month['start'], $month['end']);
            }
        }

        return [
            'labels' => $months->pluck('label')->values()->all(),
            'akun' => $akun,
            'pemeriksaan' => $pemeriksaan,
            'jadwal' => $jadwal,
            'max' => max(array_merge($akun, $pemeriksaan, $jadwal, [1])),
        ];
    }

    private function countMonthly(string $table, string $column, Carbon $start, Carbon $end): int
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return 0;
        }

        return DB::table($table)
            ->whereBetween($column, [$start, $end])
            ->count();
    }

    private function existingColumns(string $table, array $columns): array
    {
        if (! Schema::hasTable($table)) {
            return [];
        }

        return collect($columns)
            ->filter(fn ($column) => Schema::hasColumn($table, $column))
            ->values()
            ->all();
    }
}