<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kunjungans', function (Blueprint $table) {
            $table->id();
            $table->string('kode_kunjungan')->unique()->nullable();
            $table->unsignedBigInteger('pasien_id');
            $table->string('pasien_type');
            $table->foreignId('petugas_id')->constrained('users')->onDelete('cascade');
            $table->date('tanggal_kunjungan');
            $table->enum('jenis_kunjungan', ['umum', 'imunisasi', 'pemeriksaan', 'konsultasi', 'darurat'])->default('umum');
            $table->text('keluhan')->nullable();
            $table->timestamps();
            $table->index(['pasien_id', 'pasien_type']);
            $table->index('tanggal_kunjungan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kunjungans');
    }
};