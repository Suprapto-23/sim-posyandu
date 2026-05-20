<?php
/**
 * PATH   : app/Http/Controllers/Admin/DashboardController.php
 * FUNGSI : Dashboard utama admin — statistik, grafik, jadwal, login activity
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Balita;
use App\Models\Remaja;
use App\Models\Lansia;
use App\Models\JadwalPosyandu;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $stats            = $this->buildStats();
        $jadwalHariIni    = $this->getJadwalHariIni();
        $jadwalMendatang  = $this->getJadwalMendatang();
        $loginTerbaru     = $this->getLoginTerbaru();
        $chartData        = $this->getChartData();
        $userBaruBulanIni = $this->getUserBaruBulanIni();
        

        return view('admin.dashboard', compact(
            'stats', 'jadwalHariIni', 'jadwalMendatang',
            'loginTerbaru', 'chartData', 'userBaruBulanIni'
        ));
    }

    // ── Statistik utama ─────────────────────────
    private function buildStats(): array
    {
        try {
            return [
                'total_user'    => User::where('role', 'user')->count(),
                'user_aktif'    => User::where('role', 'user')->where('status', 'active')->count(),
                'user_nonaktif' => User::where('role', 'user')->where('status', 'inactive')->count(),
                'total_kader'   => User::where('role', 'kader')->count(),
                'total_bidan'   => User::where('role', 'bidan')->count(),
                'total_balita'  => Balita::count(),
                'total_remaja'  => Remaja::count(),
                'total_lansia'  => Lansia::count(),
            ];
        } catch (\Throwable $e) {
            Log::warning('AdminDashboard::buildStats — ' . $e->getMessage());
            return array_fill_keys([
                'total_user','user_aktif','user_nonaktif',
                'total_kader','total_bidan',
                'total_balita','total_remaja','total_lansia',
            ], 0);
        }
    }

    // ── Jadwal hari ini ─────────────────────────
    private function getJadwalHariIni()
    {
        try {
            return JadwalPosyandu::whereDate('tanggal', today())
                ->where('status', 'aktif')->get();
        } catch (\Throwable $e) { return collect(); }
    }

    // ── Jadwal mendatang (maks 5) ───────────────
    private function getJadwalMendatang()
    {
        try {
            return JadwalPosyandu::whereDate('tanggal', '>', today())
                ->where('status', 'aktif')
                ->orderBy('tanggal')->take(5)->get();
        } catch (\Throwable $e) { return collect(); }
    }

    // ── Login terbaru ───────────────────────────
    private function getLoginTerbaru()
    {
        try {
            return DB::table('login_logs')
                ->join('users', 'login_logs.user_id', '=', 'users.id')
                ->leftJoin('profiles', 'users.id', '=', 'profiles.user_id')
                ->select(
                    'users.role',
                    'users.email',
                    DB::raw('COALESCE(profiles.full_name, users.name) as display_name'),
                    'login_logs.login_at',
                    'login_logs.status',
                    'login_logs.ip_address'
                )
                ->orderByDesc('login_logs.login_at')
                ->limit(8)->get();
        } catch (\Throwable $e) { return collect(); }
    }

    // ── Chart: user baru 7 bulan ────────────────
    private function getChartData(): array
    {
        try {
            $labels = []; $data = [];
            for ($i = 6; $i >= 0; $i--) {
                $m = now()->subMonths($i);
                $labels[] = $m->translatedFormat('M Y');
                $data[]   = User::where('role', 'user')
                    ->whereYear('created_at', $m->year)
                    ->whereMonth('created_at', $m->month)->count();
            }
            return ['labels' => $labels, 'userData' => $data];
        } catch (\Throwable $e) {
            return ['labels' => [], 'userData' => []];
        }
    }

    // ── User baru bulan ini ─────────────────────
    private function getUserBaruBulanIni(): int
    {
        try {
            return User::where('role', 'user')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)->count();
        } catch (\Throwable $e) { return 0; }
    }
}