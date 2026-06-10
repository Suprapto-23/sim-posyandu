<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | users
        |--------------------------------------------------------------------------
        | created_by dipakai untuk audit akun:
        | Admin membuat akun Bidan, Kader, dan User/Warga.
        | Jangan tambah must_change_password karena fitur itu sudah dihapus.
        */
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'created_by')) {
                $table->foreignId('created_by')
                    ->nullable()
                    ->after('status')
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });

        /*
        |--------------------------------------------------------------------------
        | pemeriksaans
        |--------------------------------------------------------------------------
        | Kolom ini wajib agar data pemeriksaan tidak hilang:
        | - lingkar_perut untuk Remaja dan Lansia
        | - catatan_kader untuk catatan pemeriksaan awal
        | - hemoglobin untuk kebutuhan pemeriksaan Remaja/Lansia bila dipakai
        */
        Schema::table('pemeriksaans', function (Blueprint $table) {
            if (!Schema::hasColumn('pemeriksaans', 'lingkar_perut')) {
                $table->decimal('lingkar_perut', 5, 2)
                    ->nullable()
                    ->after('lingkar_lengan');
            }

            if (!Schema::hasColumn('pemeriksaans', 'hemoglobin')) {
                $table->decimal('hemoglobin', 5, 2)
                    ->nullable()
                    ->after('asam_urat');
            }

            if (!Schema::hasColumn('pemeriksaans', 'catatan_kader')) {
                $table->text('catatan_kader')
                    ->nullable()
                    ->after('keluhan');
            }
        });

        /*
        |--------------------------------------------------------------------------
        | Backfill aman
        |--------------------------------------------------------------------------
        | Kalau sebelumnya ada created_by di pemeriksaans dan pemeriksa_id sudah ada,
        | samakan datanya agar data lama tetap kebaca.
        */
        if (
            Schema::hasColumn('pemeriksaans', 'created_by') &&
            Schema::hasColumn('pemeriksaans', 'pemeriksa_id')
        ) {
            DB::table('pemeriksaans')
                ->whereNull('created_by')
                ->whereNotNull('pemeriksa_id')
                ->update([
                    'created_by' => DB::raw('pemeriksa_id'),
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('pemeriksaans', function (Blueprint $table) {
            if (Schema::hasColumn('pemeriksaans', 'catatan_kader')) {
                $table->dropColumn('catatan_kader');
            }

            if (Schema::hasColumn('pemeriksaans', 'hemoglobin')) {
                $table->dropColumn('hemoglobin');
            }

            if (Schema::hasColumn('pemeriksaans', 'lingkar_perut')) {
                $table->dropColumn('lingkar_perut');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'created_by')) {
                $table->dropConstrainedForeignId('created_by');
            }
        });
    }
};