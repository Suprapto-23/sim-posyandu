<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('imunisasis', function (Blueprint $table) {
            // Pastikan kolom vaksin ada
            if (!Schema::hasColumn('imunisasis', 'vaksin')) {
                $table->string('vaksin')->nullable()->after('jenis_imunisasi');
            }
            // Pastikan kolom dosis ada
            if (!Schema::hasColumn('imunisasis', 'dosis')) {
                $table->string('dosis')->nullable()->after('vaksin');
            }
            // Pastikan kolom penyelenggara ada
            if (!Schema::hasColumn('imunisasis', 'penyelenggara')) {
                $table->string('penyelenggara')->nullable()->after('tanggal_imunisasi');
            }
        });
    }

    public function down()
    {
        // Aman dikosongkan
    }
};