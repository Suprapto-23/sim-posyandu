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

        // 4. OPTIMASI 1: View Share untuk Settings (Super Ringan)
        // Mengeksekusi cache hanya SEKALI per request, lalu dibagikan ke seluruh view secara statis.
        $settings = cache()->remember('app_settings', 3600, function () {
            try { 
                return Setting::getAll(); 
            } catch (\Exception $e) { 
                return []; 
            }
        });
        View::share('settings', $settings);

        // 5. OPTIMASI 2: View Composer Terarah & Static Caching
        // Ganti '*' dengan spesifik layout utama agar tidak dieksekusi di partial view kecil
        $viewTargets = [
            'layouts.user',
            'layouts.admin',
            'layouts.bidan',
            'layouts.kader',
            'partials.sidebar.*' // Tambahkan ini jika sidebar Anda butuh data peran
        ];

        View::composer($viewTargets, function ($view) {
            // TRIK RAHASIA: Gunakan static variable agar jika composer kepanggil 2x di satu halaman, 
            // query database yang memakan memori tidak diulang.
            static $peranUserCache = null;

            if ($peranUserCache !== null) {
                $view->with('peranUser', $peranUserCache);
                return;
            }

            $peranUser = ['umum'];

            if (Auth::check() && Auth::user()->role === 'user') {
                $user    = Auth::user();

                $nikUser = $user->nik ?? ($user->profile?->nik ?? null);
                if (empty($nikUser) && !empty($user->username) && is_numeric($user->username)) {
                    $nikUser = $user->username;
                }

                $peranDitemukan = [];

                // Cek Orang Tua
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

                // Cek Remaja
                try {
                    if (!empty($nikUser) && Remaja::where('nik', $nikUser)->exists()) {
                        $peranDitemukan[] = 'remaja';
                    }
                } catch (\Exception $e) {}

                // Cek Lansia
                try {
                    if (!empty($nikUser) && Lansia::where('nik', $nikUser)->exists()) {
                        $peranDitemukan[] = 'lansia';
                    }
                } catch (\Exception $e) {}

                if (!empty($peranDitemukan)) {
                    $peranUser = $peranDitemukan;
                }
            }

            // Simpan ke cache lokal agar eksekusi di sub-view tidak mengulang query
            $peranUserCache = $peranUser; 
            
            $view->with('peranUser', $peranUser);
        });
    }
}