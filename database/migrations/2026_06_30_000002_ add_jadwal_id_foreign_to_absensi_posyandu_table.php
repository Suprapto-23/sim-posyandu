<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Bersihin data orphan dulu (jadwal_id yang nunjuk ke jadwal_posyandu yang udah
        // gak ada) -- kalau gak dibersihin, ADD CONSTRAINT di bawah bakal gagal kalau
        // datanya gak konsisten antar environment.
        DB::table('absensi_posyandu')
            ->whereNotNull('jadwal_id')
            ->whereNotIn('jadwal_id', function ($query) {
                $query->select('id')->from('jadwal_posyandu');
            })
            ->update(['jadwal_id' => null]);

        Schema::table('absensi_posyandu', function (Blueprint $table) {
            if (!$this->hasForeignKey('absensi_posyandu', 'absensi_posyandu_jadwal_id_foreign')) {
                $table->foreign('jadwal_id')
                    ->references('id')->on('jadwal_posyandu')
                    ->nullOnDelete();
            }
        });
    }

    public function down()
    {
        Schema::table('absensi_posyandu', function (Blueprint $table) {
            if ($this->hasForeignKey('absensi_posyandu', 'absensi_posyandu_jadwal_id_foreign')) {
                $table->dropForeign('absensi_posyandu_jadwal_id_foreign');
            }
        });
    }

    private function hasForeignKey(string $table, string $constraintName): bool
    {
        $database = DB::getDatabaseName();

        $result = DB::select(
            "SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS
             WHERE CONSTRAINT_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_NAME = ?
             AND CONSTRAINT_TYPE = 'FOREIGN KEY'",
            [$database, $table, $constraintName]
        );

        return count($result) > 0;
    }
};