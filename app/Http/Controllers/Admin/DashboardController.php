<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    /**
     * Tampilkan dashboard Admin dengan data teroptimasi.
     */
    public function index()
    {
        Carbon::setLocale('id');

        // --- CACHE STATISTIK AKUN (5 menit) ---
        $accountStats = Cache::remember('admin_dashboard_account_stats', 300, function () {
            $aggregate = User::query()
                ->selectRaw('COUNT(*) as total')
                ->selectRaw('SUM(CASE WHEN role = "admin" THEN 1 ELSE 0 END) as admin')
                ->selectRaw('SUM(CASE WHEN role = "bidan" THEN 1 ELSE 0 END) as bidan')
                ->selectRaw('SUM(CASE WHEN role = "kader" THEN 1 ELSE 0 END) as kader')
                ->selectRaw('SUM(CASE WHEN role = "user" THEN 1 ELSE 0 END) as user')
                ->selectRaw('SUM(CASE WHEN status = "active" THEN 1 ELSE 0 END) as aktif')
                ->selectRaw('SUM(CASE WHEN status = "inactive" THEN 1 ELSE 0 END) as nonaktif')
                ->first();

            return [
                'role' => [
                    'admin' => (int) ($aggregate->admin ?? 0),
                    'bidan' => (int) ($aggregate->bidan ?? 0),
                    'kader' => (int) ($aggregate->kader ?? 0),
                    'user'  => (int) ($aggregate->user ?? 0),
                ],
                'status' => [
                    'total'    => (int) ($aggregate->total ?? 0),
                    'aktif'    => (int) ($aggregate->aktif ?? 0),
                    'nonaktif' => (int) ($aggregate->nonaktif ?? 0),
                ],
            ];
        });

        // --- CACHE SASARAN (5 menit) ---
        $sasaranStats = Cache::remember('admin_dashboard_sasaran_stats', 300, function () {
            $balita = DB::table('balitas')->count();
            $remaja = DB::table('remajas')->count();
            $lansia = DB::table('lansias')->count();

            return [
                'balita' => $balita,
                'remaja' => $remaja,
                'lansia' => $lansia,
                'total'  => $balita + $remaja + $lansia,
            ];
        });

        // --- CACHE MONTHLY SERIES (10 menit) ---
        $monthlySeries = Cache::remember('admin_dashboard_monthly_series', 600, function () {
            return $this->buildMonthlySeries();
        });

        // --- DATA DINAMIS (tidak di-cache) ---
        $recentUsers = User::query()
            ->select('id', 'name', 'email', 'nik', 'role', 'status', 'created_at')
            ->whereIn('role', ['bidan', 'kader', 'user'])
            ->latest('created_at')
            ->limit(10)
            ->get();

        return view('admin.dashboard', [
            'roleStats'      => $accountStats['role'],
            'accountStats'   => $accountStats['status'],
            'sasaranStats'   => $sasaranStats,
            'monthlySeries'  => $monthlySeries,
            'recentUsers'    => $recentUsers,
        ]);
    }

    /**
     * Build data tren bulanan untuk grafik.
     * Menggunakan UNION untuk efisiensi.
     */
    private function buildMonthlySeries(): array
    {
        $months = collect(range(5, 0))->map(function ($index) {
            $date = now()->subMonths($index)->startOfMonth();
            return [
                'key'   => $date->format('Y-m'),
                'label' => Carbon::parse($date)->locale('id')->translatedFormat('M'),
                'start' => $date->copy()->startOfMonth(),
                'end'   => $date->copy()->endOfMonth(),
            ];
        });

        $start = $months->first()['start'];
        $end   = $months->last()['end'];

        // --- Query user (bidan + kader) ---
        $userRows = User::query()
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as bulan')
            ->selectRaw('role')
            ->selectRaw('COUNT(*) as total')
            ->whereIn('role', ['bidan', 'kader'])
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('bulan', 'role')
            ->get()
            ->groupBy(fn ($row) => $row->bulan . '_' . $row->role);

        // --- Query sasaran (balita, remaja, lansia) ---
        $balitaRows  = $this->monthlyCountFromTable('balitas', $months);
        $remajaRows  = $this->monthlyCountFromTable('remajas', $months);
        $lansiaRows  = $this->monthlyCountFromTable('lansias', $months);

        $series = [
            'labels'  => [],
            'bidan'   => [],
            'kader'   => [],
            'balita'  => [],
            'remaja'  => [],
            'lansia'  => [],
        ];

        foreach ($months as $month) {
            $key = $month['key'];
            $series['labels'][] = $month['label'];
            $series['bidan'][]  = (int) optional($userRows->get($key . '_bidan')?->first())->total;
            $series['kader'][]  = (int) optional($userRows->get($key . '_kader')?->first())->total;
            $series['balita'][] = (int) ($balitaRows[$key] ?? 0);
            $series['remaja'][] = (int) ($remajaRows[$key] ?? 0);
            $series['lansia'][] = (int) ($lansiaRows[$key] ?? 0);
        }

        $series['max'] = max(array_merge(
            $series['bidan'],
            $series['kader'],
            $series['balita'],
            $series['remaja'],
            $series['lansia'],
            [1]
        ));

        return $series;
    }

    /**
     * Helper count per bulan dari tabel tertentu.
     */
    private function monthlyCountFromTable(string $table, $months): array
    {
        if (!Schema::hasTable($table)) {
            return [];
        }

        $start = $months->first()['start'];
        $end   = $months->last()['end'];

        return DB::table($table)
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as bulan')
            ->selectRaw('COUNT(*) as total')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('bulan')
            ->pluck('total', 'bulan')
            ->map(fn ($value) => (int) $value)
            ->all();
    }

    /**
     * Flush cache saat ada perubahan data.
     */
    public static function flushCache()
    {
        Cache::forget('admin_dashboard_account_stats');
        Cache::forget('admin_dashboard_sasaran_stats');
        Cache::forget('admin_dashboard_monthly_series');
    }
}