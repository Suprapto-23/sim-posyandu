<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('remajas', function (Blueprint $table) {
            if (!Schema::hasColumn('remajas', 'sekolah')) {
                $table->string('sekolah')->nullable()->after('jenis_kelamin');
            }
            if (!Schema::hasColumn('remajas', 'kelas')) {
                $table->string('kelas')->nullable()->after('sekolah');
            }
            if (!Schema::hasColumn('remajas', 'nama_ortu')) {
                $table->string('nama_ortu')->nullable()->after('kelas');
            }
            if (!Schema::hasColumn('remajas', 'telepon_ortu')) {
                $table->string('telepon_ortu')->nullable()->after('nama_ortu');
            }
        });
    }

    public function down()
    {
        // Aman dikosongkan
    }
};