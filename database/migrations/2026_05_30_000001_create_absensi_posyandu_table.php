<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Cek apakah tabel SUDAH ADA. Jika belum ada, baru buat.
        if (!Schema::hasTable('absensi_posyandu')) {
            Schema::create('absensi_posyandu', function (Blueprint $table) {
                $table->id();
                $table->string('kode_absensi', 30)->unique();
                $table->unsignedInteger('nomor_pertemuan')->default(1)->comment('Urutan pertemuan per kategori (1,2,3...)');
                $table->enum('kategori', ['balita', 'remaja', 'lansia']);
                $table->date('tanggal_posyandu');
                $table->unsignedTinyInteger('bulan');
                $table->unsignedSmallInteger('tahun');
                $table->text('catatan')->nullable();
                $table->foreignId('dicatat_oleh')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('absensi_posyandu');
    }
};