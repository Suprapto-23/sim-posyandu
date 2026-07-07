<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lansias', function (Blueprint $table) {
            $table->id();
            
            // Identitas Lansia
            $table->string('kode_lansia')->unique()->nullable();
            $table->string('nama_lengkap');
            $table->string('nik', 16)->nullable()->unique();
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->text('alamat')->nullable();
            $table->string('nama_keluarga')->nullable();
            $table->string('telepon_keluarga')->nullable();
            
            // Data Kesehatan Dasar
            $table->text('penyakit_bawaan')->nullable();
            $table->string('golongan_darah', 5)->nullable();
            
            // Tracking siapa yang input
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lansias');
    }
};