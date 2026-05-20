<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pemeriksaans', function (Blueprint $table) {
            $table->id();
            // Relasi ke tabel Kunjungan (Wajib)
            $table->foreignId('kunjungan_id')->constrained('kunjungans')->onDelete('cascade');
            
            // Petugas Pemeriksa
            $table->foreignId('pemeriksa_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Data Antropometri (Balita/Umum)
            $table->decimal('berat_badan', 5, 2)->nullable(); // kg
            $table->decimal('tinggi_badan', 5, 2)->nullable(); // cm
            $table->decimal('lingkar_kepala', 5, 2)->nullable(); // cm
            $table->decimal('lingkar_lengan', 5, 2)->nullable(); // cm
            
            // Data Kesehatan Umum (Lansia/Remaja/Umum)
            $table->decimal('suhu_tubuh', 4, 1)->nullable(); // Celcius
            $table->string('tekanan_darah')->nullable(); // mmHg (120/80)
            $table->integer('denyut_nadi')->nullable(); // bpm
            $table->integer('respirasi')->nullable(); // napas/menit
            
            // Lab Sederhana (Lansia)
            $table->integer('gula_darah')->nullable(); // mg/dL
            $table->integer('kolesterol')->nullable(); // mg/dL
            $table->integer('asam_urat')->nullable(); // mg/dL
            $table->decimal('hemoglobin', 4, 1)->nullable(); // g/dL
            
            // Hasil & Diagnosa
            $table->text('keluhan')->nullable();
            $table->text('diagnosa')->nullable();
            $table->text('tindakan')->nullable(); // Pemberian obat/vitamin
            $table->text('catatan')->nullable();
            $table->text('rekomendasi')->nullable(); // Rujuk/Kontrol
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pemeriksaans');
    }
};