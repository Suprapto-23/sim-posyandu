<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('balitas', 'user_id')) {
            Schema::table('balitas', function (Blueprint $table) {
                $table->foreignId('user_id')
                    ->nullable()
                    ->after('kode_balita')
                    ->constrained('users')
                    ->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('balitas', 'nik_ibu')) {
            Schema::table('balitas', function (Blueprint $table) {
                $table->string('nik_ibu', 16)
                    ->nullable()
                    ->after('nama_ibu')
                    ->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('balitas', 'user_id')) {
            Schema::table('balitas', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            });
        }

        if (Schema::hasColumn('balitas', 'nik_ibu')) {
            Schema::table('balitas', function (Blueprint $table) {
                $table->dropColumn('nik_ibu');
            });
        }
    }
};