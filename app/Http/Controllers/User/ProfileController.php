<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash; // Wajib di-import untuk enkripsi password

/**
 * ProfileController (User/Warga)
 */
class ProfileController extends Controller
{
    /**
     * Menampilkan halaman edit profil
     */
    public function edit()
    {
        $user = Auth::user()->load('profile');
        return view('user.profile.edit', compact('user'));
    }

    /**
     * Memperbarui data identitas & profil
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name'          => 'required|string|max:255',
            'nik'           => 'nullable|digits:16',
            'tempat_lahir'  => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date|before:today',
            'jenis_kelamin' => 'nullable|in:L,P',
            'alamat'        => 'nullable|string|max:500',
            'telepon'       => 'nullable|string|max:15|regex:/^[0-9]+$/',
        ]);

        $user->update([
            'name'  => $request->name,
            'nik'   => $request->nik ?: null,
        ]);

        // full_name ADA di tabel profiles (NOT NULL) — wajib diisi
        Profile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'full_name'     => $request->name,
                'nik'           => $request->nik ?: null,
                'jenis_kelamin' => $request->jenis_kelamin ?? 'L',
                'tempat_lahir'  => $request->tempat_lahir,
                'tanggal_lahir' => $request->tanggal_lahir,
                'alamat'        => $request->alamat,
                'telepon'       => $request->telepon,
            ]
        );

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

   /**
     * Memperbarui kata sandi keamanan akun (User Warga)
     */
    public function updatePassword(Request $request)
    {
        // 1. Validasi input dengan pesan Bahasa Indonesia yang rapi
        $request->validate([
            'current_password' => ['required'],
            'password'         => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.required' => 'Kata sandi saat ini wajib diisi.',
            'password.required'         => 'Kata sandi baru wajib diisi.',
            'password.min'              => 'Kata sandi baru minimal harus 8 karakter.',
            'password.confirmed'        => 'Konfirmasi kata sandi baru tidak cocok.',
        ]);

        $user = Auth::user();

        // 2. Cek kecocokan sandi lama secara manual (Lebih akurat daripada rule 'current_password')
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Kata sandi saat ini yang Anda masukkan salah.']);
        }

        // 3. Eksekusi Update Paksa ke Database (Mencegah Silent Fail)
        \Illuminate\Support\Facades\DB::table('users')->where('id', $user->id)->update([
            'password'   => Hash::make($request->password),
            'updated_at' => now()
        ]);

        // 4. (Opsional tapi Direkomendasikan) 
        // Jangan logout user, tapi perbarui sesi agar tidak tertendang
        // Atau jika ingin keamanan ketat, Anda bisa membiarkan Auth::logoutOtherDevices()
        
        return back()->with('success', 'Keamanan akun: Kata sandi berhasil diperbarui. Silakan gunakan kata sandi baru pada login berikutnya.');
    }
}