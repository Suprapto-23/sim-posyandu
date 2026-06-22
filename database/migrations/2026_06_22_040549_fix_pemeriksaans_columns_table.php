<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pemeriksaans', function (Blueprint $table) {
            // Cek dan tambahkan kolom-kolom inti yang hilang
            if (!Schema::hasColumn('pemeriksaans', 'pasien_id')) {
                $table->unsignedBigInteger('pasien_id')->nullable()->after('kunjungan_id');
            }
            if (!Schema::hasColumn('pemeriksaans', 'kategori_pasien')) {
                $table->enum('kategori_pasien', ['balita', 'remaja', 'lansia'])->nullable()->after('pasien_id');
            }
            if (!Schema::hasColumn('pemeriksaans', 'tanggal_periksa')) {
                $table->date('tanggal_periksa')->nullable()->after('kategori_pasien');
            }
            if (!Schema::hasColumn('pemeriksaans', 'status_verifikasi')) {
                $table->enum('status_verifikasi', ['pending', 'verified', 'ditolak'])->default('pending')->after('tingkat_kemandirian');
            }
            if (!Schema::hasColumn('pemeriksaans', 'verified_by')) {
                $table->unsignedBigInteger('verified_by')->nullable()->after('status_verifikasi');
            }
            if (!Schema::hasColumn('pemeriksaans', 'catatan_bidan')) {
                $table->text('catatan_bidan')->nullable()->after('verified_by');
            }
        });
    }

    public function down()
    {
        // Tidak perlu di-drop untuk keamanan data
    }
};