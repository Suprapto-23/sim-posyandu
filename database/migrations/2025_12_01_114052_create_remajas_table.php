<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('remajas', function (Blueprint $table) {
            $table->id();
            
            // Identitas Remaja
            $table->string('kode_remaja')->unique()->nullable(); // Kode unik sistem
            $table->string('nama_lengkap');
            $table->string('nik', 16)->nullable()->unique();
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->text('alamat')->nullable();
            
            // Data Tambahan
            $table->string('sekolah')->nullable();
            $table->string('nama_ortu')->nullable();
            $table->string('telepon_ortu')->nullable();
            
            // Tracking siapa yang input
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('remajas');
    }
};