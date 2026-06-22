<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Controllers\User\Concerns\ResolvesUserHealthContext;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class MonitoringController extends Controller
{
    use ResolvesUserHealthContext;

    public function index(): View
    {
        try {
            $user = auth()->user();

            // SINKRONISASI CACHE DENGAN DASHBOARD
            $contextKey = 'user_dashboard_context_' . $user->id;
            $context = Cache::remember($contextKey, 300, function () use ($user) {
                return $this->getUserContext($user);
            });

            $balitas = $this->loadMonitoringRelations($context['balitas']);
            $remajas = $this->loadMonitoringRelations($context['remajas']);
            $lansias = $this->loadMonitoringRelations($context['lansias']);

            $counts = [
                'total' => $balitas->count() + $remajas->count() + $lansias->count(),
                'balita' => $balitas->count(),
                'remaja' => $remajas->count(),
                'lansia' => $lansias->count(),
            ];

            return view('user.monitoring.index', [
                'context' => $context,
                'balitas' => $balitas,
                'remajas' => $remajas,
                'lansias' => $lansias,
                'counts' => $counts,
                'hasData' => $counts['total'] > 0,
            ]);
        } catch (\Throwable $e) {
            Log::error('User monitoring index error', [
                'message' => $e->getMessage(),
            ]);

            return redirect()
                ->route('user.dashboard')
                ->with('error', 'Data pemantauan kesehatan belum dapat dimuat. Silakan coba kembali.');
        }
    }

    private function loadMonitoringRelations(Collection $items): Collection
    {
        return $items->map(function ($item) {
            try {
                $relations = [];

                if (method_exists($item, 'pemeriksaan_terakhir')) {
                    $relations[] = 'pemeriksaan_terakhir';
                }

                if (method_exists($item, 'kunjungans')) {
                    $relations['kunjungans'] = fn ($query) => $query
                        ->latest('tanggal_kunjungan')
                        ->limit(5);

                    $relations[] = 'kunjungans.pemeriksaan';
                }

                if (! empty($relations)) {
                    $item->loadMissing($relations);
                }
            } catch (\Throwable $e) {
                Log::warning('Monitoring relation load skipped', [
                    'model' => get_class($item),
                    'id' => $item->id ?? null,
                    'message' => $e->getMessage(),
                ]);
            }

            return $item;
        });
    }
}