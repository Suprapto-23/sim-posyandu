<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function indexExists(string $table, string $indexName): bool
    {
        $database = DB::getDatabaseName();

        $result = DB::selectOne("
            SELECT COUNT(1) AS total
            FROM information_schema.statistics
            WHERE table_schema = ?
              AND table_name = ?
              AND index_name = ?
        ", [$database, $table, $indexName]);

        return (int) ($result->total ?? 0) > 0;
    }

    public function up(): void
    {
        if (
            Schema::hasTable('absensi_posyandu') &&
            !$this->indexExists('absensi_posyandu', 'absensi_posyandu_kategori_tanggal_unique')
        ) {
            Schema::table('absensi_posyandu', function (Blueprint $table) {
                $table->unique(
                    ['kategori', 'tanggal_posyandu'],
                    'absensi_posyandu_kategori_tanggal_unique'
                );
            });
        }

        if (
            Schema::hasTable('absensi_detail') &&
            !$this->indexExists('absensi_detail', 'absensi_detail_sesi_pasien_unique')
        ) {
            Schema::table('absensi_detail', function (Blueprint $table) {
                $table->unique(
                    ['absensi_id', 'pasien_type', 'pasien_id'],
                    'absensi_detail_sesi_pasien_unique'
                );
            });
        }
    }

    public function down(): void
    {
        if (
            Schema::hasTable('absensi_detail') &&
            $this->indexExists('absensi_detail', 'absensi_detail_sesi_pasien_unique')
        ) {
            Schema::table('absensi_detail', function (Blueprint $table) {
                $table->dropUnique('absensi_detail_sesi_pasien_unique');
            });
        }

        if (
            Schema::hasTable('absensi_posyandu') &&
            $this->indexExists('absensi_posyandu', 'absensi_posyandu_kategori_tanggal_unique')
        ) {
            Schema::table('absensi_posyandu', function (Blueprint $table) {
                $table->dropUnique('absensi_posyandu_kategori_tanggal_unique');
            });
        }
    }
};