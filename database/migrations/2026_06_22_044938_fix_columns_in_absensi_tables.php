<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('absensi_posyandu', function (Blueprint $table) {
            // Tambahkan relasi ke jadwal Posyandu
            if (!Schema::hasColumn('absensi_posyandu', 'jadwal_id')) {
                $table->unsignedBigInteger('jadwal_id')->nullable()->after('id');
            }
        });

        Schema::table('absensi_detail', function (Blueprint $table) {
            // Letakkan waktu_hadir dengan aman setelah kolom 'hadir'
            if (!Schema::hasColumn('absensi_detail', 'waktu_hadir')) {
                $table->time('waktu_hadir')->nullable()->after('hadir');
            }
        });
    }

    public function down()
    {
        // Aman dikosongkan
    }
};