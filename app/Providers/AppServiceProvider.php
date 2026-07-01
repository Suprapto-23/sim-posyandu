<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL; // ← TAMBAH INI
use App\Models\Setting;
use App\Models\Balita;
use App\Models\Remaja;
use App\Models\Lansia;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // ← TAMBAH INI
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        Schema::defaultStringLength(191);

        View::composer('*', function ($view) {
            $settings = cache()->remember('app_settings', 3600, function () {
                try { return Setting::getAll(); } catch (\Exception $e) { return []; }
            });
            $view->with('settings', $settings);

            $peranUser = ['umum'];

            if (Auth::check() && Auth::user()->role === 'user') {
                $user    = Auth::user();

                $nikUser = $user->nik ?? ($user->profile?->nik ?? null);
                if (empty($nikUser) && !empty($user->username) && is_numeric($user->username)) {
                    $nikUser = $user->username;
                }

                $peranDitemukan = [];

                try {
                    $adaBalita = Balita::where(function ($q) use ($nikUser) {
                            $q->where('nik_ibu', $nikUser)
                              ->orWhere('nik_ayah', $nikUser)
                              ->orWhere('nik', $nikUser);
                        })
                        ->orWhere('user_id', $user->id)
                        ->exists();

                    if ($adaBalita) {
                        $peranDitemukan[] = 'orang_tua';
                    }
                } catch (\Exception $e) {}

                try {
                    if ($nikUser && Remaja::where('nik', $nikUser)->exists()) {
                        $peranDitemukan[] = 'remaja';
                    }
                } catch (\Exception $e) {}

                try {
                    if ($nikUser && Lansia::where('nik', $nikUser)->exists()) {
                        $peranDitemukan[] = 'lansia';
                    }
                } catch (\Exception $e) {}

                if (!empty($peranDitemukan)) {
                    $peranUser = $peranDitemukan;
                }
            }

            $view->with('peranUser', $peranUser);
        });
    }
}