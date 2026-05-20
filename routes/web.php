<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ChangePasswordController;
use App\Http\Controllers\HomeController;

// Admin
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\UserController      as AdminUser;
use App\Http\Controllers\Admin\BidanController     as AdminBidan;
use App\Http\Controllers\Admin\KaderController     as AdminKader;
use App\Http\Controllers\Admin\SettingController   as AdminSetting;

// Bidan
use App\Http\Controllers\Bidan\DashboardController   as BidanDashboard;
use App\Http\Controllers\Bidan\PemeriksaanController as BidanPemeriksaan;
use App\Http\Controllers\Bidan\JadwalController      as BidanJadwal;
use App\Http\Controllers\Bidan\PasienController      as BidanPasien;
use App\Http\Controllers\Bidan\RekamMedisController as BidanRekamMedisController;

// Kader
use App\Http\Controllers\Kader\DashboardController   as KaderDashboard;
use App\Http\Controllers\Kader\BalitaController;
use App\Http\Controllers\Kader\RemajaController;
use App\Http\Controllers\Kader\LansiaController;
use App\Http\Controllers\Kader\PemeriksaanController;
use App\Http\Controllers\Kader\ImunisasiController;
use App\Http\Controllers\Kader\KunjunganController;
use App\Http\Controllers\Kader\LaporanController;
use App\Http\Controllers\Kader\JadwalController;
use App\Http\Controllers\Kader\ImportController;
use App\Http\Controllers\Kader\ProfileController    as KaderProfile;
use App\Http\Controllers\Kader\NotifikasiController as KaderNotifikasi;
use App\Http\Controllers\Kader\AbsensiController; 

// User (Warga)
use App\Http\Controllers\User\DashboardController   as UserDashboard;
use App\Http\Controllers\User\BalitaController      as UserBalita;
use App\Http\Controllers\User\RemajaController      as UserRemaja;
use App\Http\Controllers\User\LansiaController      as UserLansia;
use App\Http\Controllers\User\JadwalController      as UserJadwal;
use App\Http\Controllers\User\ProfileController     as UserProfile;
use App\Http\Controllers\User\NotifikasiController  as UserNotifikasi;
use App\Http\Controllers\User\RiwayatController;



// ==================== ROOT ====================
Route::get('/', function () {
    return auth()->check() ? redirect()->route('home') : redirect()->route('login');
});

// ==================== AUTH ====================
Route::get('/login',  [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout',[LoginController::class, 'logout'])->name('logout');
Route::get('/logout', [LoginController::class, 'logout'])->name('logout.get');

// ==================== GLOBAL ====================
Route::middleware('auth')->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    Route::get('/password/change',  [ChangePasswordController::class, 'showChangeForm'])->name('password.change');
    Route::post('/password/change', [ChangePasswordController::class, 'change'])->name('password.change.post');
    Route::get('/profile',          [UserProfile::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',        [UserProfile::class, 'update'])->name('profile.update');
});

// ==================== ADMIN ====================
Route::prefix('admin')->name('admin.')->middleware(['auth','checkstatus','role:admin'])->group(function () {
    Route::get('/', fn() => redirect()->route('admin.dashboard'));
    Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');

    Route::resource('users', AdminUser::class);
    Route::post('users/{id}/generate-password', [AdminUser::class, 'generatePassword'])->name('users.generate-password');
    Route::post('users/{id}/reset-password',    [AdminUser::class, 'resetPassword'])->name('users.reset-password');

    Route::resource('bidans', AdminBidan::class);
    Route::post('bidans/{id}/reset-password', [AdminBidan::class, 'resetPassword'])->name('bidans.reset-password');

    Route::resource('kaders', AdminKader::class);
    Route::post('kaders/{id}/reset-password', [AdminKader::class, 'resetPassword'])->name('kaders.reset-password');

    Route::get('/settings',                [AdminSetting::class, 'index'])->name('settings.index');
    Route::put('/settings',                [AdminSetting::class, 'update'])->name('settings.update');
    Route::put('/settings/change-password',[AdminSetting::class, 'changePassword'])->name('settings.change-password');
});

// =========================================================================
// ==================== ROUTE UTAMA KHUSUS ROLE: BIDAN =====================
// =========================================================================
// Semua rute di bawah ini dilindungi oleh middleware keamanan berlapis.
// Urutan penomoran disesuaikan dengan hierarki menu pada sidebar komponen.
Route::prefix('bidan')->name('bidan.')->middleware(['auth', 'checkstatus', 'role:bidan'])->group(function () {

    // ---------------------------------------------------------------
    // 1. DASHBOARD (COMMAND CENTER KLINIS)
    // ---------------------------------------------------------------
    Route::get('/', fn() => redirect()->route('bidan.dashboard'));
    Route::get('/dashboard', [\App\Http\Controllers\Bidan\DashboardController::class, 'index'])->name('dashboard');

    // ---------------------------------------------------------------
    // 2. PEMERIKSAAN MEDIS (TRIASE MEJA 5)
    // ---------------------------------------------------------------
    Route::prefix('pemeriksaan')->name('pemeriksaan.')->group(function () {
        Route::get('/',                [\App\Http\Controllers\Bidan\PemeriksaanController::class, 'index'])->name('index');
        Route::get('/create',          [\App\Http\Controllers\Bidan\PemeriksaanController::class, 'create'])->name('create');
        Route::post('/',               [\App\Http\Controllers\Bidan\PemeriksaanController::class, 'store'])->name('store');
        Route::get('/{id}',            [\App\Http\Controllers\Bidan\PemeriksaanController::class, 'show'])->name('show');
        Route::get('/{id}/edit',       [\App\Http\Controllers\Bidan\PemeriksaanController::class, 'edit'])->name('edit');
        Route::put('/{id}',            [\App\Http\Controllers\Bidan\PemeriksaanController::class, 'update'])->name('update');
        Route::delete('/{id}',         [\App\Http\Controllers\Bidan\PemeriksaanController::class, 'destroy'])->name('destroy');
        
        // Alur Kerja Validasi Hasil Inputan Kader Lapangan
        Route::get('/validasi/{id}',   [\App\Http\Controllers\Bidan\PemeriksaanController::class, 'validasi'])->name('validasi');
        Route::put('/validasi/{id}',   [\App\Http\Controllers\Bidan\PemeriksaanController::class, 'simpanValidasi'])->name('simpan-validasi');
        Route::put('/{id}/verifikasi', [\App\Http\Controllers\Bidan\PemeriksaanController::class, 'verifikasi'])->name('verifikasi');
    });

    // ---------------------------------------------------------------
  

    // ---------------------------------------------------------------
    // 4. BUKU REGISTER IMUNISASI (KIA)
    // ---------------------------------------------------------------
    Route::prefix('imunisasi')->name('imunisasi.')->group(function () {
        Route::get('/',          [\App\Http\Controllers\Bidan\ImunisasiController::class, 'index'])->name('index');
        Route::get('/create',    [\App\Http\Controllers\Bidan\ImunisasiController::class, 'create'])->name('create');
        Route::post('/',         [\App\Http\Controllers\Bidan\ImunisasiController::class, 'store'])->name('store');
        Route::get('/{id}',      [\App\Http\Controllers\Bidan\ImunisasiController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [\App\Http\Controllers\Bidan\ImunisasiController::class, 'edit'])->name('edit');
        Route::put('/{id}',      [\App\Http\Controllers\Bidan\ImunisasiController::class, 'update'])->name('update');
        Route::delete('/{id}',   [\App\Http\Controllers\Bidan\ImunisasiController::class, 'destroy'])->name('destroy');
    });

    // ---------------------------------------------------------------
    // 5. DATA PASIEN & REKAM MEDIS ELEKTRONIK (EMR)
    // ---------------------------------------------------------------
    // Pemantauan Tren Kesehatan per Demografi
    Route::prefix('pasien')->name('pasien.')->group(function () {
        Route::get('/balita',    [\App\Http\Controllers\Bidan\PasienController::class, 'balita'])->name('balita');
        Route::get('/remaja',    [\App\Http\Controllers\Bidan\PasienController::class, 'remaja'])->name('remaja');
        Route::get('/lansia',    [\App\Http\Controllers\Bidan\PasienController::class, 'lansia'])->name('lansia');
    });

    // Buku Induk Rekam Medis (EMR Gateway)
    Route::prefix('rekam-medis')->name('rekam-medis.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Bidan\RekamMedisController::class, 'index'])->name('index');
        Route::get('/show/{pasien_type}/{pasien_id}', [\App\Http\Controllers\Bidan\RekamMedisController::class, 'show'])->name('show');
    });

    

    // ---------------------------------------------------------------
    // 7. AGENDA & JADWAL POSYANDU
    // ---------------------------------------------------------------
    Route::resource('jadwal', \App\Http\Controllers\Bidan\JadwalController::class);

    // ---------------------------------------------------------------
    // 8. LAPORAN REKAPITULASI POSYANDU (PDF BULANAN)
    // ---------------------------------------------------------------
    Route::prefix('laporan')->name('laporan.')->group(function () {
        Route::get('/',           [\App\Http\Controllers\Bidan\LaporanController::class, 'index'])->name('index');
        Route::get('/cetak',      [\App\Http\Controllers\Bidan\LaporanController::class, 'cetak'])->name('cetak');
        Route::post('/upload-ttd', [\App\Http\Controllers\Bidan\LaporanController::class, 'uploadTtd'])->name('upload-ttd');
    });

    // ---------------------------------------------------------------
    // 9. PUSAT NOTIFIKASI REALTIME
    // ---------------------------------------------------------------
    // Perbaikan: pembungkusan prefix dilakukan agar penamaan rute presisi
    Route::prefix('notifikasi')->name('notifikasi.')->group(function () {
        Route::get('/',              [\App\Http\Controllers\Bidan\NotifikasiController::class, 'index'])->name('index');
        Route::get('/fetch',         [\App\Http\Controllers\Bidan\NotifikasiController::class, 'fetchRecent'])->name('fetch');
        Route::post('/mark-all-read', [\App\Http\Controllers\Bidan\NotifikasiController::class, 'markAllRead'])->name('markall');
    });
});

// =========================================================================
// AKSES KADER
// =========================================================================
Route::middleware(['auth', 'role:kader'])
    ->prefix('kader')
    ->name('kader.')
    ->group(function () {

    // Dashboard
    Route::get('/dashboard', [KaderDashboard::class, 'index'])->name('dashboard');

    // Data Sasaran
    Route::prefix('data')->name('data.')->group(function () {
        Route::delete('balita/bulk-delete', [BalitaController::class, 'bulkDelete'])->name('balita.bulk-delete');
        Route::post('balita/{id}/sync', [BalitaController::class, 'syncUser'])->name('balita.sync');
        Route::resource('balita', BalitaController::class);

        Route::delete('remaja/bulk-delete', [RemajaController::class, 'bulkDelete'])->name('remaja.bulk-delete');
        Route::post('remaja/{id}/sync', [RemajaController::class, 'syncUser'])->name('remaja.sync');
        Route::resource('remaja', RemajaController::class);

        Route::delete('lansia/bulk-delete', [LansiaController::class, 'bulkDelete'])->name('lansia.bulk-delete');
        Route::post('lansia/{id}/sync', [LansiaController::class, 'syncUser'])->name('lansia.sync');
        Route::resource('lansia', LansiaController::class);
    });

    // Pengukuran Fisik
    Route::get('pemeriksaan/api/pasien', [PemeriksaanController::class, 'getPasienApi'])->name('pemeriksaan.api');
    Route::resource('pemeriksaan', PemeriksaanController::class);

    // Absensi Posyandu
    Route::prefix('absensi')->name('absensi.')->group(function () {
        Route::get('/', [AbsensiController::class, 'index'])->name('index');
        Route::post('/', [AbsensiController::class, 'store'])->name('store');
        Route::get('/berhasil/tersimpan', [AbsensiController::class, 'success'])->name('success');
        Route::get('/riwayat', [AbsensiController::class, 'riwayat'])->name('riwayat');
        Route::get('/{id}', [AbsensiController::class, 'show'])->name('show');
        Route::delete('/{id}', [AbsensiController::class, 'destroy'])->name('destroy');
    });

    // Kunjungan, read-only sebagian
    Route::resource('kunjungan', KunjunganController::class)
        ->except(['create', 'store', 'update']);

    // Imunisasi, read-only untuk Kader
    Route::resource('imunisasi', ImunisasiController::class)
        ->except(['create', 'store', 'edit', 'update', 'destroy']);

    // Jadwal, read-only dari Bidan
    Route::prefix('jadwal')->name('jadwal.')->group(function () {
        Route::get('/', [JadwalController::class, 'index'])->name('index');
        Route::get('/{jadwal}', [JadwalController::class, 'show'])->name('show');
    });

    // Import Data
    Route::prefix('import')->name('import.')->group(function () {
        Route::get('/', [ImportController::class, 'index'])->name('history');
        Route::get('/create', [ImportController::class, 'create'])->name('index');
        Route::post('/store', [ImportController::class, 'store'])->name('store');
        Route::get('/template/{type}', [ImportController::class, 'downloadTemplate'])->name('template');
        Route::get('/{id}', [ImportController::class, 'show'])->name('show');
        Route::delete('/{id}', [ImportController::class, 'destroy'])->name('destroy');
    });

    // Laporan Bulanan
    Route::get('laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::match(['get', 'post'], 'laporan/generate', [LaporanController::class, 'generate'])->name('laporan.generate');

    // Profil
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [KaderProfile::class, 'index'])->name('index');
        Route::put('/update', [KaderProfile::class, 'update'])->name('update');
        Route::get('/password', [KaderProfile::class, 'password'])->name('password');
        Route::put('/password', [KaderProfile::class, 'updatePassword'])->name('update-password');
    });

    // Notifikasi
    Route::prefix('notifikasi')->name('notifikasi.')->group(function () {
        Route::get('/', [KaderNotifikasi::class, 'index'])->name('index');
        Route::post('/read-all', [KaderNotifikasi::class, 'markAllRead'])->name('markAllRead');
        Route::post('/{id}/read', [KaderNotifikasi::class, 'markAsRead'])->name('read');
        Route::delete('/{id}', [KaderNotifikasi::class, 'destroy'])->name('destroy');
        Route::get('/fetch', [KaderNotifikasi::class, 'fetchRecent'])->name('fetch');
    });
});
// ==================== USER (WARGA) ====================
Route::prefix('user')->name('user.')->middleware(['auth','checkstatus','role:user'])->group(function () {
    
    // Redirect rute dasar ke Dashboard
    Route::get('/', fn() => redirect()->route('user.dashboard'));
 
    // ── 1. Beranda / Dashboard ──────────────────────────────────────────
    Route::get('/dashboard', [UserDashboard::class, 'index'])->name('dashboard');
    Route::get('/stats', [UserDashboard::class, 'getStats'])->name('stats');
 
    // ── 2. Jadwal Posyandu ──────────────────────────────────────────────
    Route::get('/jadwal', [UserJadwal::class, 'index'])->name('jadwal.index');

    // ── 3. Pantau Kesehatan (Monitoring Terpadu) ────────────────────────
    Route::get('/monitoring', [\App\Http\Controllers\User\MonitoringController::class, 'index'])->name('monitoring.index');
 
    // ── 4. Buku Kesehatan Digital (Detail per Demografi) ────────────────
    Route::get('/balita/{id}/show', [UserBalita::class, 'show'])->name('balita.show');
    
    // [TAMBAHAN BARU] Rute untuk melengkapi sistem monitoring
    Route::get('/remaja/{id}/show', [\App\Http\Controllers\User\RemajaController::class, 'show'])->name('remaja.show');
    Route::get('/lansia/{id}/show', [\App\Http\Controllers\User\LansiaController::class, 'show'])->name('lansia.show');
     
 
    // ── 5. Riwayat Rekam Medis Terpadu ──────────────────────────────────
    Route::get('/riwayat', [RiwayatController::class, 'index'])->name('riwayat.index');
 
    // ── 6. Notifikasi / Pesan Bidan ─────────────────────────────────────
    Route::prefix('notifikasi')->name('notifikasi.')->group(function () {
        Route::get('/',              [UserNotifikasi::class, 'index'])->name('index');
        Route::get('/fetch',         [UserNotifikasi::class, 'fetchRecent'])->name('fetch');
        Route::post('/mark-all-read',[UserNotifikasi::class, 'markAllRead'])->name('markall');
        Route::post('/{id}/read',    [UserNotifikasi::class, 'markRead'])->name('read');
    });
 
    // ── 7. Profil & Keamanan ────────────────────────────────────────────
    Route::get('/profile', [\App\Http\Controllers\User\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [\App\Http\Controllers\User\ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [\App\Http\Controllers\User\ProfileController::class, 'updatePassword'])->name('password.update');
    
    // Fallback URL jika user me-refresh halaman ganti sandi
    Route::get('/profile/password', fn() => redirect()->route('user.profile.edit'));
});