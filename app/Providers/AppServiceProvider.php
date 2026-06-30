<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Models\Setting;
use App\Models\Balita;
use App\Models\Remaja;
use App\Models\Lansia;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 1. Force HTTPS di Production
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        // 2. Fix error limit string pada database MySQL lawas
        Schema::defaultStringLength(191);

        // 3. Standardisasi Polymorphic Relation secara Global
        Relation::morphMap([
            'balita' => \App\Models\Balita::class,
            'remaja' => \App\Models\Remaja::class,
            'lansia' => \App\Models\Lansia::class,
        ]);

        // 4. View Composer untuk Global Variable
        View::composer('*', function ($view) {
            $settings = cache()->remember('app_settings', 3600, function () {
                try { 
                    return Setting::getAll(); 
                } catch (\Exception $e) { 
                    return []; 
                }
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
                    if (!empty($nikUser)) {
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
                    } else {
                        if (Balita::where('user_id', $user->id)->exists()) {
                             $peranDitemukan[] = 'orang_tua';
                        }
                    }
                } catch (\Exception $e) {}

                try {
                    if (!empty($nikUser) && Remaja::where('nik', $nikUser)->exists()) {
                        $peranDitemukan[] = 'remaja';
                    }
                } catch (\Exception $e) {}

                try {
                    if (!empty($nikUser) && Lansia::where('nik', $nikUser)->exists()) {
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