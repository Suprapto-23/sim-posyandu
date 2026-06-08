<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $accountAggregate = User::query()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN role = "admin" THEN 1 ELSE 0 END) as admin')
            ->selectRaw('SUM(CASE WHEN role = "bidan" THEN 1 ELSE 0 END) as bidan')
            ->selectRaw('SUM(CASE WHEN role = "kader" THEN 1 ELSE 0 END) as kader')
            ->selectRaw('SUM(CASE WHEN role = "user" THEN 1 ELSE 0 END) as user')
            ->selectRaw('SUM(CASE WHEN status = "active" THEN 1 ELSE 0 END) as aktif')
            ->selectRaw('SUM(CASE WHEN status = "inactive" THEN 1 ELSE 0 END) as nonaktif')
            ->first();

        $roleStats = [
            'admin' => (int) ($accountAggregate->admin ?? 0),
            'bidan' => (int) ($accountAggregate->bidan ?? 0),
            'kader' => (int) ($accountAggregate->kader ?? 0),
            'user' => (int) ($accountAggregate->user ?? 0),
        ];

        $accountStats = [
            'total' => (int) ($accountAggregate->total ?? 0),
            'aktif' => (int) ($accountAggregate->aktif ?? 0),
            'nonaktif' => (int) ($accountAggregate->nonaktif ?? 0),
        ];

        $sasaranStats = [
            'balita' => DB::table('balitas')->count(),
            'remaja' => DB::table('remajas')->count(),
            'lansia' => DB::table('lansias')->count(),
        ];

        $sasaranStats['total'] = $sasaranStats['balita'] + $sasaranStats['remaja'] + $sasaranStats['lansia'];

        $monthlySeries = $this->monthlySeries();

        $recentUsers = User::query()
            ->select('id', 'name', 'email', 'nik', 'role', 'status', 'created_at')
            ->whereIn('role', ['bidan', 'kader', 'user'])
            ->latest('created_at')
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact(
            'roleStats',
            'accountStats',
            'sasaranStats',
            'monthlySeries',
            'recentUsers'
        ));
    }

    private function monthlySeries(): array
    {
        $months = collect(range(5, 0))->map(function ($index) {
            $date = now()->subMonths($index)->startOfMonth();

            return [
                'key' => $date->format('Y-m'),
                'label' => Carbon::parse($date)->locale('id')->translatedFormat('M'),
                'start' => $date->copy()->startOfMonth(),
                'end' => $date->copy()->endOfMonth(),
            ];
        });

        $userRows = User::query()
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as bulan')
            ->selectRaw('role')
            ->selectRaw('COUNT(*) as total')
            ->whereIn('role', ['bidan', 'kader'])
            ->whereBetween('created_at', [$months->first()['start'], $months->last()['end']])
            ->groupBy('bulan', 'role')
            ->get()
            ->groupBy(fn ($row) => $row->bulan . '_' . $row->role);

        $balitaRows = $this->monthlyCountFromTable('balitas', $months);
        $remajaRows = $this->monthlyCountFromTable('remajas', $months);
        $lansiaRows = $this->monthlyCountFromTable('lansias', $months);

        $series = [
            'labels' => [],
            'bidan' => [],
            'kader' => [],
            'balita' => [],
            'remaja' => [],
            'lansia' => [],
        ];

        foreach ($months as $month) {
            $key = $month['key'];

            $series['labels'][] = $month['label'];
            $series['bidan'][] = (int) optional($userRows->get($key . '_bidan')?->first())->total;
            $series['kader'][] = (int) optional($userRows->get($key . '_kader')?->first())->total;
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

    private function monthlyCountFromTable(string $table, $months): array
    {
        return DB::table($table)
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as bulan')
            ->selectRaw('COUNT(*) as total')
            ->whereBetween('created_at', [$months->first()['start'], $months->last()['end']])
            ->groupBy('bulan')
            ->pluck('total', 'bulan')
            ->map(fn ($value) => (int) $value)
            ->all();
    }
}