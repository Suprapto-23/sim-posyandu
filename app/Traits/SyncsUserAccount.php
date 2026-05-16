<?php

namespace App\Traits;

use App\Models\User;
use App\Models\Profile;
use Illuminate\Support\Facades\Schema;

/**
 * Trait SyncsUserAccount
 * Digunakan oleh Kader untuk mencari dan menyambungkan data rekam medis 
 * (Balita, Lansia, dll) dengan Akun Login Warga (User) secara otomatis.
 */
trait SyncsUserAccount
{
    public function findLinkedUser($nik, $nama_lengkap)
    {
        $cleanNik  = preg_replace('/[^0-9]/', '', (string)$nik);
        $cleanName = trim((string)$nama_lengkap);

        if (empty($cleanNik) && empty($cleanName)) return null;

        // 1. TAHAP PERTAMA: Cari berdasarkan NIK (Sangat Akurat)
        if (!empty($cleanNik)) {
            // Cari di tabel users
            $user = User::where('nik', $cleanNik)
                        ->orWhere('email', $cleanNik)
                        ->first();
            if ($user) return $user;

            // Cari di tabel profiles
            if (Schema::hasTable('profiles')) {
                $profile = Profile::where('nik', $cleanNik)->first();
                if ($profile && $profile->user) return $profile->user;
            }
        }

        // 2. TAHAP KEDUA: Cari berdasarkan Nama (Fuzzy Search)
        if (!empty($cleanName)) {
            $userByName = User::where('name', 'LIKE', "%{$cleanName}%")->first();
            if ($userByName) return $userByName;
        }

        return null;
    }
}