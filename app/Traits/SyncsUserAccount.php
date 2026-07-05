<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

trait SyncsUserAccount
{
    /**
     * Mengeksekusi sinkronisasi akun tanpa memicu duplikasi NIK.
     */
    public function executeSync(array $data, string $kategoriDemografi)
    {
        // Cegah eksekusi jika NIK kosong (mencegah null constraint)
        if (empty($data['nik'])) {
            abort(400, 'NIK tidak valid atau kosong saat proses sinkronisasi.');
        }

        // firstOrCreate: Solusi mutlak untuk masalah sinkronisasi dua pintu (Admin & Kader)
        $user = User::firstOrCreate(
            // Parameter 1: Kondisi pencarian (Kolom Unique)
            ['nik' => $data['nik']],
            
            // Parameter 2: Data yang akan dieksekusi HANYA jika NIK belum terdaftar
            [
                'name' => $data['nama_lengkap'],
                'password' => Hash::make($data['nik']), // Default password menggunakan NIK
                'role' => $kategoriDemografi // Kategorisasi demografi otomatis
            ]
        );

        // Kembalikan primary key untuk ditautkan sebagai foreign key
        return $user->id;
    }
}