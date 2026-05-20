<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pemeriksaans', function (Blueprint $table) {
            if (!Schema::hasColumn('pemeriksaans', 'tingkat_kemandirian')) {
                $table->string('tingkat_kemandirian')->nullable()->after('hemoglobin');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pemeriksaans', function (Blueprint $table) {
            if (Schema::hasColumn('pemeriksaans', 'tingkat_kemandirian')) {
                $table->dropColumn('tingkat_kemandirian');
            }
        });
    }
};