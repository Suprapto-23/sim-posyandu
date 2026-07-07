<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::create('balitas', function (Blueprint $table) {
        $table->id();
            $table->string('kode_balita')->unique()->nullable();
            $table->string('nik', 16)->unique()->nullable();
            $table->string('nama_lengkap');
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir');
            
            // Data Fisik Lahir
            $table->decimal('berat_lahir', 5, 2)->nullable(); 
            $table->decimal('panjang_lahir', 5, 2)->nullable(); 
            
            // Data Orang Tua
            $table->string('nama_ibu');
            $table->string('nama_ayah')->nullable();
            $table->text('alamat')->nullable();
            
            // Tracking Pendaftar
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('balitas');
    }
};