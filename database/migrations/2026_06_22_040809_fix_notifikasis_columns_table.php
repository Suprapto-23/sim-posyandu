<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('notifikasis', function (Blueprint $table) {
            // 1. Pastikan kolom user_id (Relasi ke siapa notif ini dikirim) ada
            if (!Schema::hasColumn('notifikasis', 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            }
            
            // 2. Pastikan kolom tipe (jadwal, pemeriksaan, dll) ada
            if (!Schema::hasColumn('notifikasis', 'tipe')) {
                $table->string('tipe', 50)->nullable();
            }
            
            // 3. Pastikan kolom judul ada
            if (!Schema::hasColumn('notifikasis', 'judul')) {
                $table->string('judul')->nullable();
            }
            
            // 4. Pastikan kolom pesan ada
            if (!Schema::hasColumn('notifikasis', 'pesan')) {
                $table->text('pesan')->nullable();
            }
            
            // 5. Pastikan status baca (is_read) ada
            if (!Schema::hasColumn('notifikasis', 'is_read')) {
                $table->boolean('is_read')->default(0);
            }
            
            // 6. Pastikan timestamp kapan dibaca (read_at) ada
            if (!Schema::hasColumn('notifikasis', 'read_at')) {
                $table->timestamp('read_at')->nullable();
            }
        });
    }

    public function down()
    {
        // Aman dikosongkan
    }
};