<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\HomeController;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ChangePasswordController;

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\BidanController as AdminBidanController;
use App\Http\Controllers\Admin\KaderController as AdminKaderController;

use App\Http\Controllers\Bidan\DashboardController as BidanDashboardController;
use App\Http\Controllers\Bidan\PemeriksaanController as BidanPemeriksaanController;
use App\Http\Controllers\Bidan\ImunisasiController as BidanImunisasiController;
use App\Http\Controllers\Bidan\JadwalController as BidanJadwalController;
use App\Http\Controllers\Bidan\RekamMedisController as BidanRekamMedisController;
use App\Http\Controllers\Bidan\NotifikasiController as BidanNotifikasiController;

use App\Http\Controllers\Kader\DashboardController as KaderDashboardController;
use App\Http\Controllers\Kader\BalitaController as KaderBalitaController;
use App\Http\Controllers\Kader\RemajaController as KaderRemajaController;
use App\Http\Controllers\Kader\LansiaController as KaderLansiaController;
use App\Http\Controllers\Kader\PemeriksaanController as KaderPemeriksaanController;
use App\Http\Controllers\Kader\AbsensiController as KaderAbsensiController;
use App\Http\Controllers\Kader\KunjunganController as KaderKunjunganController;
use App\Http\Controllers\Kader\ImunisasiController as KaderImunisasiController;
use App\Http\Controllers\Kader\JadwalController as KaderJadwalController;
use App\Http\Controllers\Kader\LaporanController as KaderLaporanController;
use App\Http\Controllers\Kader\NotifikasiController as KaderNotifikasiController;
use App\Http\Controllers\Kader\ImportController as KaderImportController;
use App\Http\Controllers\Kader\ProfileController as KaderProfileController;

use App\Http\Controllers\User\DashboardController as UserDashboardController;
use App\Http\Controllers\User\MonitoringController as UserMonitoringController;
use App\Http\Controllers\User\BalitaController as UserBalitaController;
use App\Http\Controllers\User\RemajaController as UserRemajaController;
use App\Http\Controllers\User\LansiaController as UserLansiaController;
use App\Http\Controllers\User\JadwalController as UserJadwalController;
use App\Http\Controllers\User\RiwayatController as UserRiwayatController;
use App\Http\Controllers\User\NotifikasiController as UserNotifikasiController;
use App\Http\Controllers\User\ProfileController as UserProfileController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    if (! Auth::check()) {
        return redirect()->route('login');
    }

    return match (Auth::user()->role) {
        'admin' => redirect()->route('admin.dashboard'),
        'bidan' => redirect()->route('bidan.dashboard'),
        'kader' => redirect()->route('kader.dashboard'),
        'user' => redirect()->route('user.dashboard'),
        default => redirect()->route('login'),
    };
});

Route::get('/home', [HomeController::class, 'index'])
    ->middleware('auth')
    ->name('home');

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

Route::get('/login', [LoginController::class, 'showLoginForm'])
    ->name('login');

Route::post('/login', [LoginController::class, 'login'])
    ->name('login.post');

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');


/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Admin fokus pada pengelolaan akun sistem:
| - Akun Warga/User
| - Akun Bidan
| - Akun Kader
| - Dashboard ringkasan sistem
|
*/

Route::middleware(['auth', 'checkstatus', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', fn () => redirect()->route('admin.dashboard'))
            ->name('home');

        Route::get('/dashboard', [AdminDashboardController::class, 'index'])
            ->name('dashboard');

        /*
        |--------------------------------------------------------------------------
        | Admin Kelola Akun Warga/User
        |--------------------------------------------------------------------------
        */

        Route::post('/users/{id}/generate-password', [AdminUserController::class, 'generatePassword'])
            ->whereNumber('id')
            ->name('users.generate-password');

        Route::post('/users/{id}/reset-password', [AdminUserController::class, 'resetPassword'])
            ->whereNumber('id')
            ->name('users.reset-password');

        Route::resource('users', AdminUserController::class);

        /*
        |--------------------------------------------------------------------------
        | Admin Kelola Akun Bidan
        |--------------------------------------------------------------------------
        */

        Route::post('/bidans/{id}/reset-password', [AdminBidanController::class, 'resetPassword'])
            ->whereNumber('id')
            ->name('bidans.reset-password');

        Route::resource('bidans', AdminBidanController::class);

        /*
        |--------------------------------------------------------------------------
        | Admin Kelola Akun Kader
        |--------------------------------------------------------------------------
        */

        Route::post('/kaders/{id}/reset-password', [AdminKaderController::class, 'resetPassword'])
            ->whereNumber('id')
            ->name('kaders.reset-password');

        Route::resource('kaders', AdminKaderController::class);
    });

/*
|--------------------------------------------------------------------------
| Bidan Routes
|--------------------------------------------------------------------------
|
| Bidan fokus pada:
| - Pemeriksaan klinis lanjutan
| - Validasi pemeriksaan
| - Jadwal Posyandu
| - Imunisasi
| - Rekam medis
| - Notifikasi
|
*/

Route::middleware(['auth', 'checkstatus', 'role:bidan'])
    ->prefix('bidan')
    ->name('bidan.')
    ->group(function () {
        Route::get('/', fn () => redirect()->route('bidan.dashboard'))
            ->name('home');

        Route::get('/dashboard', [BidanDashboardController::class, 'index'])
            ->name('dashboard');

        Route::get('/dashboard/trend', [BidanDashboardController::class, 'trend'])
            ->name('dashboard.trend');

        /*
        |--------------------------------------------------------------------------
        | Bidan Pemeriksaan Klinis
        |--------------------------------------------------------------------------
        */

        Route::get('/pemeriksaan', [BidanPemeriksaanController::class, 'index'])
            ->name('pemeriksaan.index');

        Route::get('/pemeriksaan/validasi/{id}', [BidanPemeriksaanController::class, 'validasi'])
            ->whereNumber('id')
            ->name('pemeriksaan.validasi');

        Route::put('/pemeriksaan/validasi/{id}', [BidanPemeriksaanController::class, 'simpanValidasi'])
            ->whereNumber('id')
            ->name('pemeriksaan.simpan-validasi');

        Route::get('/pemeriksaan/{id}', [BidanPemeriksaanController::class, 'show'])
            ->whereNumber('id')
            ->name('pemeriksaan.show');

        Route::put('/pemeriksaan/{id}/verifikasi', [BidanPemeriksaanController::class, 'verifikasi'])
            ->whereNumber('id')
            ->name('pemeriksaan.verifikasi');

        Route::delete('/pemeriksaan/{id}', [BidanPemeriksaanController::class, 'destroy'])
            ->whereNumber('id')
            ->name('pemeriksaan.destroy');

        /*
        |--------------------------------------------------------------------------
        | Bidan Imunisasi
        |--------------------------------------------------------------------------
        */

        Route::get('/imunisasi', [BidanImunisasiController::class, 'index'])
            ->name('imunisasi.index');

        Route::get('/imunisasi/create', [BidanImunisasiController::class, 'create'])
            ->name('imunisasi.create');

        Route::post('/imunisasi', [BidanImunisasiController::class, 'store'])
            ->name('imunisasi.store');

        Route::get('/imunisasi/{id}', [BidanImunisasiController::class, 'show'])
            ->whereNumber('id')
            ->name('imunisasi.show');

        Route::get('/imunisasi/{id}/edit', [BidanImunisasiController::class, 'edit'])
            ->whereNumber('id')
            ->name('imunisasi.edit');

        Route::put('/imunisasi/{id}', [BidanImunisasiController::class, 'update'])
            ->whereNumber('id')
            ->name('imunisasi.update');

        Route::delete('/imunisasi/{id}', [BidanImunisasiController::class, 'destroy'])
            ->whereNumber('id')
            ->name('imunisasi.destroy');

        /*
        |--------------------------------------------------------------------------
        | Bidan Jadwal Posyandu
        |--------------------------------------------------------------------------
        */

        Route::resource('jadwal', BidanJadwalController::class);

        /*
        |--------------------------------------------------------------------------
        | Bidan Rekam Medis
        |--------------------------------------------------------------------------
        */

        Route::get('/rekam-medis', [BidanRekamMedisController::class, 'index'])
            ->name('rekam-medis.index');

        Route::get('/rekam-medis/show/{pasien_type}/{pasien_id}', [BidanRekamMedisController::class, 'show'])
            ->whereNumber('pasien_id')
            ->name('rekam-medis.show');

        /*
        |--------------------------------------------------------------------------
        | Bidan Notifikasi
        |--------------------------------------------------------------------------
        */

        Route::get('/notifikasi', [BidanNotifikasiController::class, 'index'])
            ->name('notifikasi.index');

        Route::get('/notifikasi/fetch', [BidanNotifikasiController::class, 'fetchRecent'])
            ->name('notifikasi.fetch');

        Route::post('/notifikasi/mark-all-read', [BidanNotifikasiController::class, 'markAllRead'])
            ->name('notifikasi.markall');
    });

/*
|--------------------------------------------------------------------------
| Kader Routes
|--------------------------------------------------------------------------
|
| Kader fokus pada:
| - Data sasaran Balita, Remaja, Lansia
| - Absensi Posyandu
| - Pemeriksaan awal atau pengukuran fisik
| - Jadwal read-only
| - Imunisasi read-only
| - Laporan bulanan
| - Notifikasi
|
| Catatan penting:
| Sistem final hanya memakai tiga sasaran: Balita, Remaja, Lansia.
|
*/

Route::middleware(['auth', 'checkstatus', 'role:kader'])
    ->prefix('kader')
    ->name('kader.')
    ->group(function () {
        Route::get('/', fn () => redirect()->route('kader.dashboard'))
            ->name('home');

        Route::get('/dashboard', [KaderDashboardController::class, 'index'])
            ->name('dashboard');

        Route::get('/dashboard/trend', [KaderDashboardController::class, 'trend'])
            ->name('dashboard.trend');

        /*
        |--------------------------------------------------------------------------
        | Kader Data Sasaran: Balita
        |--------------------------------------------------------------------------
        */

        Route::delete('/data/balita/bulk-delete', [KaderBalitaController::class, 'bulkDelete'])
            ->name('data.balita.bulk-delete');

        Route::resource('/data/balita', KaderBalitaController::class)
            ->names('data.balita');

        Route::post('/data/balita/{id}/sync', [KaderBalitaController::class, 'syncUser'])
            ->whereNumber('id')
            ->name('data.balita.sync');

        /*
        |--------------------------------------------------------------------------
        | Kader Data Sasaran: Remaja
        |--------------------------------------------------------------------------
        */

        Route::delete('/data/remaja/bulk-delete', [KaderRemajaController::class, 'bulkDelete'])
            ->name('data.remaja.bulk-delete');

        Route::resource('/data/remaja', KaderRemajaController::class)
            ->names('data.remaja');

        Route::post('/data/remaja/{id}/sync', [KaderRemajaController::class, 'syncUser'])
            ->whereNumber('id')
            ->name('data.remaja.sync');

        /*
        |--------------------------------------------------------------------------
        | Kader Data Sasaran: Lansia
        |--------------------------------------------------------------------------
        */

        Route::delete('/data/lansia/bulk-delete', [KaderLansiaController::class, 'bulkDelete'])
            ->name('data.lansia.bulk-delete');

        Route::resource('/data/lansia', KaderLansiaController::class)
            ->names('data.lansia');

        Route::post('/data/lansia/{id}/sync', [KaderLansiaController::class, 'syncUser'])
            ->whereNumber('id')
            ->name('data.lansia.sync');

        /*
        |--------------------------------------------------------------------------
        | Kader Absensi Posyandu
        |--------------------------------------------------------------------------
        */

        Route::get('/absensi', [KaderAbsensiController::class, 'index'])
            ->name('absensi.index');

        Route::post('/absensi', [KaderAbsensiController::class, 'store'])
            ->name('absensi.store');

        Route::get('/absensi/berhasil/tersimpan', [KaderAbsensiController::class, 'success'])
            ->name('absensi.success');

        Route::get('/absensi/riwayat', [KaderAbsensiController::class, 'riwayat'])
            ->name('absensi.riwayat');

        Route::get('/absensi/{id}', [KaderAbsensiController::class, 'show'])
            ->whereNumber('id')
            ->name('absensi.show');

        Route::delete('/absensi/{id}', [KaderAbsensiController::class, 'destroy'])
            ->whereNumber('id')
            ->name('absensi.destroy');

        /*
        |--------------------------------------------------------------------------
        | Kader Pemeriksaan Awal
        |--------------------------------------------------------------------------
        */

        Route::get('/pemeriksaan/api/pasien', [KaderPemeriksaanController::class, 'getPasienApi'])
            ->name('pemeriksaan.api');

        Route::resource('/pemeriksaan', KaderPemeriksaanController::class)
            ->parameters([
                'pemeriksaan' => 'pemeriksaan',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Kader Kunjungan
        |--------------------------------------------------------------------------
        */

        Route::get('/kunjungan', [KaderKunjunganController::class, 'index'])
            ->name('kunjungan.index');

        Route::get('/kunjungan/{kunjungan}', [KaderKunjunganController::class, 'show'])
            ->name('kunjungan.show');

        Route::delete('/kunjungan/{kunjungan}', [KaderKunjunganController::class, 'destroy'])
            ->name('kunjungan.destroy');

        /*
        |--------------------------------------------------------------------------
        | Kader Jadwal dan Imunisasi Read-Only
        |--------------------------------------------------------------------------
        */

        Route::get('/jadwal', [KaderJadwalController::class, 'index'])->name('jadwal.index');
        Route::get('/jadwal/live', [KaderJadwalController::class, 'live'])->name('jadwal.live');
        Route::get('/jadwal/{jadwal}/live', [KaderJadwalController::class, 'liveShow'])->name('jadwal.live-show');
        Route::get('/jadwal/{jadwal}', [KaderJadwalController::class, 'show'])->name('jadwal.show');

        Route::get('/imunisasi', [KaderImunisasiController::class, 'index'])
            ->name('imunisasi.index');

        Route::get('/imunisasi/{imunisasi}', [KaderImunisasiController::class, 'show'])
            ->name('imunisasi.show');

        /*
        |--------------------------------------------------------------------------
        | Kader Laporan Bulanan
        |--------------------------------------------------------------------------
        */

        Route::get('/laporan', [KaderLaporanController::class, 'index'])
            ->name('laporan.index');

        Route::match(['get', 'post'], '/laporan/preview', [KaderLaporanController::class, 'preview'])
            ->name('laporan.preview');

        /*
        |--------------------------------------------------------------------------
        | Kader Import Data
        |--------------------------------------------------------------------------
        */

        Route::get('/import', [KaderImportController::class, 'index'])
            ->name('import.index');

        Route::get('/import/create', [KaderImportController::class, 'create'])
            ->name('import.create');

        Route::post('/import', [KaderImportController::class, 'store'])
            ->name('import.store');

        Route::get('/import/history', [KaderImportController::class, 'history'])
            ->name('import.history');

        Route::get('/import/template/{type}', [KaderImportController::class, 'downloadTemplate'])
            ->name('import.template');

        Route::get('/import/{id}', [KaderImportController::class, 'show'])
            ->whereNumber('id')
            ->name('import.show');

        Route::delete('/import/{id}', [KaderImportController::class, 'destroy'])
            ->whereNumber('id')
            ->name('import.destroy');

        /*
        |--------------------------------------------------------------------------
        | Kader Notifikasi
        |--------------------------------------------------------------------------
        */

        Route::get('/notifikasi', [KaderNotifikasiController::class, 'index'])
            ->name('notifikasi.index');

        Route::get('/notifikasi/fetch', [KaderNotifikasiController::class, 'fetchRecent'])
            ->name('notifikasi.fetch');

        Route::post('/notifikasi/read-all', [KaderNotifikasiController::class, 'markAllRead'])
            ->name('notifikasi.markAllRead');

        Route::post('/notifikasi/{id}/read', [KaderNotifikasiController::class, 'markAsRead'])
            ->whereNumber('id')
            ->name('notifikasi.read');

        Route::delete('/notifikasi/{id}', [KaderNotifikasiController::class, 'destroy'])
            ->whereNumber('id')
            ->name('notifikasi.destroy');

        /*
        |--------------------------------------------------------------------------
        | Kader Profil
        |--------------------------------------------------------------------------
        */

        Route::get('/profile', [KaderProfileController::class, 'index'])
            ->name('profile.index');

        Route::put('/profile/update', [KaderProfileController::class, 'update'])
            ->name('profile.update');

        Route::get('/profile/password', [KaderProfileController::class, 'password'])
            ->name('profile.password');

        Route::put('/profile/password', [KaderProfileController::class, 'updatePassword'])
            ->name('profile.update-password');
    });

/*
|--------------------------------------------------------------------------
| User atau Warga Routes
|--------------------------------------------------------------------------
|
| User/Warga fokus pada:
| - Monitoring kesehatan anggota keluarga
| - Jadwal Posyandu
| - Riwayat pemeriksaan
| - Notifikasi
| - Profil akun
|
*/

Route::middleware(['auth', 'checkstatus', 'role:user'])
    ->prefix('user')
    ->name('user.')
    ->group(function () {
        Route::get('/', fn () => redirect()->route('user.dashboard'))
            ->name('home');

        Route::get('/dashboard', [UserDashboardController::class, 'index'])
            ->name('dashboard');

        Route::get('/stats', [UserDashboardController::class, 'getStats'])
            ->name('stats');

        /*
        |--------------------------------------------------------------------------
        | User Monitoring Kesehatan
        |--------------------------------------------------------------------------
        */

        Route::get('/monitoring', [UserMonitoringController::class, 'index'])
            ->name('monitoring.index');

        Route::get('/balita/{id}/show', [UserBalitaController::class, 'show'])
            ->whereNumber('id')
            ->name('balita.show');

        Route::get('/remaja/{id}/show', [UserRemajaController::class, 'show'])
            ->whereNumber('id')
            ->name('remaja.show');

        Route::get('/lansia/{id}/show', [UserLansiaController::class, 'show'])
            ->whereNumber('id')
            ->name('lansia.show');

        /*
        |--------------------------------------------------------------------------
        | User Jadwal dan Riwayat
        |--------------------------------------------------------------------------
        */

        Route::get('/jadwal', [UserJadwalController::class, 'index'])
            ->name('jadwal.index');

        Route::get('/riwayat', [UserRiwayatController::class, 'index'])
            ->name('riwayat.index');

        /*
        |--------------------------------------------------------------------------
        | User Notifikasi
        |--------------------------------------------------------------------------
        */

        Route::get('/notifikasi', [UserNotifikasiController::class, 'index'])
            ->name('notifikasi.index');

        Route::get('/notifikasi/fetch', [UserNotifikasiController::class, 'fetchRecent'])
            ->name('notifikasi.fetch');

        Route::post('/notifikasi/mark-all-read', [UserNotifikasiController::class, 'markAllRead'])
            ->name('notifikasi.markall');

        Route::post('/notifikasi/{id}/read', [UserNotifikasiController::class, 'markRead'])
            ->whereNumber('id')
            ->name('notifikasi.read');

        /*
        |--------------------------------------------------------------------------
        | User Profil
        |--------------------------------------------------------------------------
        */

        Route::get('/profile', [UserProfileController::class, 'edit'])
            ->name('profile.edit');

        Route::patch('/profile', [UserProfileController::class, 'update'])
            ->name('profile.update');

        Route::get('/profile/password', fn () => redirect()->route('user.profile.edit'))
            ->name('password.edit');

        Route::put('/profile/password', [UserProfileController::class, 'updatePassword'])
            ->name('password.update');
    });