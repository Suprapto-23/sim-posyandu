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
        // 1. Buat Akun Administrator (Sistem Inti)
        // firstOrCreate memastikan jika di-run berulang kali, tidak akan duplikat
        $admin = User::firstOrCreate(
            ['email' => 'admin@posyandu.com'], // Patokan pencarian
            [
                'name'              => 'Administrator',
                'nik'               => '0000000000000000',
                'password'          => Hash::make('password'), // Sandi: password
                'role'              => 'admin',
                'status'            => 'active',
                'email_verified_at' => now(),
            ]
        );

        // 2. Buat Profile Admin untuk relasi dashboard
        Profile::firstOrCreate(
            ['user_id' => $admin->id],
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