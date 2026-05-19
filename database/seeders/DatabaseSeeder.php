<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Profile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat Akun Administrator (Tanpa menghapus data lain)
        // firstOrCreate akan mencari email admin@posyandu.com, 
        // jika belum ada, baru data di dalam array kedua akan di-insert.
        $admin = User::firstOrCreate(
            ['email' => 'admin@posyandu.com'], // Yang dicari
            [
                'name'              => 'Administrator',
                'nik'               => '0000000000000000',
                'password'          => Hash::make('password'), // Sandi: password
                'role'              => 'admin',
                'status'            => 'active',
                'email_verified_at' => now(),
            ]
        );

        // 2. Buat Profile Admin agar tidak error saat masuk dashboard
        Profile::firstOrCreate(
            ['user_id' => $admin->id], // Yang dicari berdasarkan user_id admin
            [
                'full_name'     => 'Administrator Sistem Terpadu',
                'nik'           => '0000000000000000',
                'jenis_kelamin' => 'L',
                'alamat'        => 'Pusat Manajemen Posyandu',
                'telepon'       => '081234567890',
            ]
        );
    }
}