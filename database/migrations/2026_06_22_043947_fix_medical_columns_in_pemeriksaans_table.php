<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pemeriksaans', function (Blueprint $table) {
            // Pastikan semua kolom indikator medis ada
            if (!Schema::hasColumn('pemeriksaans', 'imt')) {
                $table->float('imt')->nullable()->after('tinggi_badan');
            }
            if (!Schema::hasColumn('pemeriksaans', 'lingkar_perut')) {
                $table->float('lingkar_perut')->nullable()->after('lingkar_lengan');
            }
            if (!Schema::hasColumn('pemeriksaans', 'tekanan_darah')) {
                $table->string('tekanan_darah', 20)->nullable()->after('lingkar_perut');
            }
            if (!Schema::hasColumn('pemeriksaans', 'gula_darah')) {
                $table->float('gula_darah')->nullable()->after('tekanan_darah');
            }
            if (!Schema::hasColumn('pemeriksaans', 'kolesterol')) {
                $table->float('kolesterol')->nullable()->after('gula_darah');
            }
            if (!Schema::hasColumn('pemeriksaans', 'asam_urat')) {
                $table->float('asam_urat')->nullable()->after('kolesterol');
            }
        });
    }

    public function down()
    {
        // Aman dikosongkan
    }
};