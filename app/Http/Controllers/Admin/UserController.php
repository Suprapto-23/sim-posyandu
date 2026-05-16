<?php
/**
 * PATH   : app/Http/Controllers/Admin/UserController.php
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Balita;
use App\Models\Remaja;
use App\Models\Lansia;
use App\Models\IbuHamil;

class UserController extends Controller
{
    // ── INDEX ───────────────────────────────────
    public function index(Request $request)
    {
        $query = User::with('profile')->where('role', 'user');

        // 1. Fitur Pencarian
        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%")
                  ->orWhereHas('profile', fn($p) =>
                      $p->where('full_name', 'like', "%{$search}%")
                        ->orWhere('nik', 'like', "%{$search}%")
                  );
            });
        }
        
        if ($request->status) $query->where('status', $request->status);

        // 2. FITUR BARU: Filter Kategori Cerdas (Berdasarkan relasi NIK & ID)
        if ($kat = $request->kategori) {
            $query->where(function($q) use ($kat) {
                if ($kat == 'remaja') {
                    $q->whereIn('nik', Remaja::select('nik')->whereNotNull('nik'))
                      ->orWhereIn('id', Remaja::select('user_id')->whereNotNull('user_id'));
                } elseif ($kat == 'lansia') {
                    $q->whereIn('nik', Lansia::select('nik')->whereNotNull('nik'))
                      ->orWhereIn('id', Lansia::select('user_id')->whereNotNull('user_id'));
                } elseif ($kat == 'balita') {
                    $q->whereIn('nik', Balita::select('nik')->whereNotNull('nik'))
                      ->orWhereIn('nik', Balita::select('nik_ibu')->whereNotNull('nik_ibu'))
                      ->orWhereIn('nik', Balita::select('nik_ayah')->whereNotNull('nik_ayah'))
                      ->orWhereIn('id', Balita::select('user_id')->whereNotNull('user_id'));
                } elseif ($kat == 'bumil') {
                    $q->whereIn('nik', IbuHamil::select('nik')->whereNotNull('nik'))
                      ->orWhereIn('id', IbuHamil::select('user_id')->whereNotNull('user_id'));
                }
            });
        }

        $users = $query->latest()->paginate($request->per_page ?? 15)->withQueryString();
        $stats = $this->getStats();

        return view('admin.users.index', compact('users', 'stats'));
    }

    // ── CREATE ──────────────────────────────────
    public function create()
    {
        return view('admin.users.create');
    }

    // PATH: app/Http/Controllers/Admin/UserController.php

public function store(Request $request)
{
    $request->validate([
        'full_name'     => 'required|string|max:191',
        'nik'           => 'required|digits:16|unique:users,nik|unique:profiles,nik',
        'jenis_kelamin' => 'required|in:L,P',
        'telepon'       => 'required|string|max:20',
        'alamat'        => 'required|string',
        'tempat_lahir'  => 'required|string|max:100',
        'tanggal_lahir' => 'required|date',
        'status'        => 'required|in:active,inactive',
    ], [
        'nik.digits' => 'NIK harus tepat 16 digit angka.',
        'nik.unique' => 'NIK ini sudah terdaftar di sistem.',
    ]);

    $password = $this->makePassword();

    DB::beginTransaction();
    try {
        // [LOGIKA BARU]: Buat user TANPA email. NIK menjadi identitas utama.
        $user = User::create([
            'name'     => $request->full_name,
            'email'    => null, // Kosongkan karena warga tidak pakai email
            'nik'      => $request->nik,
            'password' => Hash::make($password),
            'role'     => 'user',
            'status'   => $request->status,
        ]);

        $user->profile()->create([
            'user_id'       => $user->id,
            'full_name'     => $request->full_name,
            'nik'           => $request->nik,
            'jenis_kelamin' => $request->jenis_kelamin,
            'telepon'       => $request->telepon,
            'alamat'        => $request->alamat,
            'tempat_lahir'  => $request->tempat_lahir,
            'tanggal_lahir' => $request->tanggal_lahir,
        ]);

        DB::commit();
    } catch (\Throwable $e) {
        DB::rollBack();
        Log::error('UserController::store — ' . $e->getMessage());
        return back()->withInput()->with('error', 'Gagal memproses data ke database. Pastikan database mengizinkan email kosong.');
    }

    return redirect()->route('admin.users.index')
        ->with('success', 'Akun warga berhasil dibuat.')
        ->with('generated_password', $password)
        ->with('user_name', $request->full_name)
        ->with('user_nik', $request->nik);
}

    // ── SHOW ────────────────────────────────────
    public function show($id)
    {
        $user       = User::with('profile')->where('role', 'user')->findOrFail($id);
        $linkedData = $this->detectLinkedPatients($user);

        return view('admin.users.show', compact('user', 'linkedData'));
    }

    // ── EDIT ────────────────────────────────────
    public function edit($id)
    {
        $user = User::with('profile')->where('role', 'user')->findOrFail($id);
        return view('admin.users.edit', compact('user'));
    }

    // ── UPDATE ──────────────────────────────────
    public function update(Request $request, $id)
    {
        $user = User::with('profile')->where('role', 'user')->findOrFail($id);
        $profileId = $user->profile?->id;

        $request->validate([
            'full_name'     => 'required|string|max:191',
            'nik'           => "required|digits:16|unique:users,nik,{$id}|unique:profiles,nik,{$profileId}",
            'jenis_kelamin' => 'required|in:L,P',
            'telepon'       => 'required|string|max:20',
            'alamat'        => 'required|string',
            'tempat_lahir'  => 'required|string|max:100',
            'tanggal_lahir' => 'required|date',
            'status'        => 'required|in:active,inactive',
        ]);

        DB::beginTransaction();
        try {
            $user->update([
                'name'   => $request->full_name,
                'nik'    => $request->nik,
                'status' => $request->status,
            ]);

            $profileData = [
                'full_name'     => $request->full_name,
                'nik'           => $request->nik,
                'jenis_kelamin' => $request->jenis_kelamin,
                'telepon'       => $request->telepon,
                'alamat'        => $request->alamat,
                'tempat_lahir'  => $request->tempat_lahir,
                'tanggal_lahir' => $request->tanggal_lahir,
            ];

            if ($user->profile) {
                $user->profile->update($profileData);
            } else {
                $user->profile()->create(array_merge($profileData, ['user_id' => $user->id]));
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal memperbarui data.');
        }

        return redirect()->route('admin.users.show', $id)
            ->with('success', 'Data warga berhasil diperbarui.');
    }

    // ── DESTROY ─────────────────────────────────
    public function destroy($id)
    {
        $user = User::where('role', 'user')->findOrFail($id);
        $name = $user->profile?->full_name ?? $user->name;
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', "Akun warga {$name} berhasil dihapus.");
    }

    // ── GENERATE PASSWORD (acak baru) ────────────
    public function generatePassword($id)
    {
        $user     = User::where('role', 'user')->findOrFail($id);
        $password = $this->makePassword();
        $user->update(['password' => Hash::make($password)]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Password baru berhasil dibuat.')
            ->with('generated_password', $password)
            ->with('user_name', $user->profile?->full_name ?? $user->name)
            ->with('user_nik', $user->nik);
    }

    // ── RESET PASSWORD (default: 6 digit NIK + "Ps!") ──
    public function resetPassword($id)
    {
        $user     = User::where('role', 'user')->findOrFail($id);
        $nik      = $user->nik ?? $user->profile?->nik ?? '000000000000000';
        $password = substr($nik, -6) . 'Ps!';
        $user->update(['password' => Hash::make($password)]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Password direset ke default.')
            ->with('reset_password', $password)
            ->with('reset_name', $user->profile?->full_name ?? $user->name)
            ->with('reset_nik', $nik);
    }

    // ── HELPERS ─────────────────────────────────

    private function getStats(): array
    {
        try {
            return [
                'total'    => User::where('role', 'user')->count(),
                'aktif'    => User::where('role', 'user')->where('status', 'active')->count(),
                'nonaktif' => User::where('role', 'user')->where('status', 'inactive')->count(),
            ];
        } catch (\Throwable $e) {
            return ['total' => 0, 'aktif' => 0, 'nonaktif' => 0];
        }
    }

    /**
     * Deteksi data pasien yang terhubung dengan user berdasarkan:
     * - user_id langsung
     * - NIK matching (balita: nik_ibu/nik_ayah, remaja/lansia: nik)
     */
    private function detectLinkedPatients(User $user): array
    {
        $nik = $user->nik ?? $user->profile?->nik;

        try {
            if ($nik) {
                $balita = Balita::where('user_id', $user->id)
                    ->orWhere('nik', $nik)
                    ->orWhere('nik_ibu', $nik)
                    ->orWhere('nik_ayah', $nik)
                    ->get();
            } else {
                $balita = Balita::where('user_id', $user->id)->get();
            }
        } catch (\Throwable $e) { $balita = collect(); }

        try {
            $remaja = $nik
                ? Remaja::where('user_id', $user->id)->orWhere('nik', $nik)->first()
                : Remaja::where('user_id', $user->id)->first();
        } catch (\Throwable $e) { $remaja = null; }

        try {
            $lansia = $nik
                ? Lansia::where('user_id', $user->id)->orWhere('nik', $nik)->first()
                : Lansia::where('user_id', $user->id)->first();
        } catch (\Throwable $e) { $lansia = null; }

        try {
            $bumil = $nik
                ? IbuHamil::where('user_id', $user->id)->orWhere('nik', $nik)->first()
                : IbuHamil::where('user_id', $user->id)->first();
        } catch (\Throwable $e) { $bumil = null; }

        return compact('balita', 'remaja', 'lansia', 'bumil');
    }

    private function makePassword(): string
    {
        $chars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789!@#';
        $pass  = '';
        for ($i = 0; $i < 8; $i++) {
            $pass .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $pass;
    }
}