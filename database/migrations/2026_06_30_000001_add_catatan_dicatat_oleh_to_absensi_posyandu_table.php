<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('absensi_posyandu', function (Blueprint $table) {
            // Model AbsensiPosyandu mengisi kolom ini di setiap save(), tapi kolomnya
            // belum pernah dibuat di DB -> insert absensi selalu error "Unknown column".
            if (!Schema::hasColumn('absensi_posyandu', 'catatan')) {
                $table->text('catatan')->nullable()->after('keterangan');
            }

            if (!Schema::hasColumn('absensi_posyandu', 'dicatat_oleh')) {
                $table->foreignId('dicatat_oleh')
                    ->nullable()
                    ->after('jadwal_id')
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });
    }

    public function down()
    {
        Schema::table('absensi_posyandu', function (Blueprint $table) {
            if (Schema::hasColumn('absensi_posyandu', 'dicatat_oleh')) {
                $table->dropForeign(['dicatat_oleh']);
                $table->dropColumn('dicatat_oleh');
            }

            if (Schema::hasColumn('absensi_posyandu', 'catatan')) {
                $table->dropColumn('catatan');
            }
        });
    }
};