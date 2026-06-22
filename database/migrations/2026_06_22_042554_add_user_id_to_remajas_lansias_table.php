<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Tambahkan user_id ke tabel Remaja
        Schema::table('remajas', function (Blueprint $table) {
            if (!Schema::hasColumn('remajas', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('kode_remaja');
            }
        });

        // 2. Tambahkan user_id ke tabel Lansia
        Schema::table('lansias', function (Blueprint $table) {
            if (!Schema::hasColumn('lansias', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('kode_lansia');
            }
        });
    }

    public function down()
    {
        // Aman dikosongkan
    }
};