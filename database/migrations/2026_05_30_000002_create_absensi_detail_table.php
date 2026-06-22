<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Cek apakah tabel SUDAH ADA. Jika belum ada, baru buat.
        if (!Schema::hasTable('absensi_detail')) {
            Schema::create('absensi_detail', function (Blueprint $table) {
                $table->id();
                $table->foreignId('absensi_id')->constrained('absensi_posyandu')->cascadeOnDelete();
                $table->unsignedBigInteger('pasien_id');
                $table->string('pasien_type', 50);
                $table->boolean('hadir')->default(0);
                $table->string('keterangan', 100)->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('absensi_detail');
    }
};