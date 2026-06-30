<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $views = [
        'view_balita_usia',
        'view_dashboard_kader',
        'view_imunisasi_jadwal',
        'view_imunisasi_terbaru',
        'view_kunjungan_harian',
        'view_lansia_usia',
        'view_laporan_balita',
        'view_laporan_lansia',
        'view_laporan_remaja',
        'view_statistik_harian',
    ];

    public function up()
    {
        foreach ($this->views as $view) {
            DB::statement("DROP VIEW IF EXISTS `{$view}`");
        }
    }

    public function down()
    {
        // Sengaja dikosongkan -- view ini gak dipakai aplikasi, definisi aslinya
        // masih ada di file dump SQL lama kalau suatu saat dibutuhkan lagi.
    }
};