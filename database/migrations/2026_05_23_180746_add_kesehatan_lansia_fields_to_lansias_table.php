<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lansias', function (Blueprint $table) {
            if (!Schema::hasColumn('lansias', 'tingkat_kemandirian')) {
                $table->string('tingkat_kemandirian')->nullable();
            }

            if (!Schema::hasColumn('lansias', 'tekanan_darah')) {
                $table->string('tekanan_darah')->nullable();
            }

            if (!Schema::hasColumn('lansias', 'gula_darah')) {
                $table->decimal('gula_darah', 6, 2)->nullable();
            }

            if (!Schema::hasColumn('lansias', 'kolesterol')) {
                $table->decimal('kolesterol', 6, 2)->nullable();
            }

            if (!Schema::hasColumn('lansias', 'asam_urat')) {
                $table->decimal('asam_urat', 5, 2)->nullable();
            }

            if (!Schema::hasColumn('lansias', 'lingkar_perut')) {
                $table->decimal('lingkar_perut', 5, 2)->nullable();
            }

            if (!Schema::hasColumn('lansias', 'keluhan')) {
                $table->text('keluhan')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('lansias', function (Blueprint $table) {
            $columns = [
                'tingkat_kemandirian',
                'tekanan_darah',
                'gula_darah',
                'kolesterol',
                'asam_urat',
                'lingkar_perut',
                'keluhan',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('lansias', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};