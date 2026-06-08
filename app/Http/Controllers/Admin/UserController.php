<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
{
    $perPage = (int) $request->input('per_page', 12);
    $perPage = max(5, min($perPage, 25));

    $search = trim((string) $request->input('search', ''));
    $status = $request->input('status');

    $query = User::query()
        ->select('users.*')
        ->leftJoin('profiles', 'profiles.user_id', '=', 'users.id')
        ->with('profile')
        ->where('users.role', 'user');

    if ($search !== '') {
        $query->where(function ($q) use ($search) {
            if (preg_match('/^[0-9]+$/', $search)) {
                $q->where('users.nik', 'like', "{$search}%")
                  ->orWhere('profiles.telepon', 'like', "{$search}%");
            } else {
                $q->where('users.name', 'like', "{$search}%")
                  ->orWhere('users.email', 'like', "{$search}%")
                  ->orWhere('profiles.full_name', 'like', "{$search}%");
            }
        });
    }

    if (in_array($status, ['active', 'inactive'], true)) {
        $query->where('users.status', $status);
    }

    $users = $query
        ->latest('users.id')
        ->paginate($perPage)
        ->withQueryString();

    $statsData = User::query()
        ->where('role', 'user')
        ->selectRaw('COUNT(*) as total')
        ->selectRaw('SUM(CASE WHEN status = "active" THEN 1 ELSE 0 END) as aktif')
        ->selectRaw('SUM(CASE WHEN status = "inactive" THEN 1 ELSE 0 END) as nonaktif')
        ->first();

    $stats = [
        'total' => (int) ($statsData->total ?? 0),
        'aktif' => (int) ($statsData->aktif ?? 0),
        'nonaktif' => (int) ($statsData->nonaktif ?? 0),
    ];

    return view('admin.users.index', compact('users', 'stats'));
}

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateUserPayload($request);

        // OPTIMASI UX: Generate password yang manusiawi untuk Warga
        $password = $this->makePassword();

        DB::beginTransaction();

        try {
            // 1. Simpan tabel Utama (users)
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'nik' => $validated['nik'],
                'password' => Hash::make($password),
                'role' => 'user',
                'status' => $validated['status'] ?? 'active',
            ]);

            // 2. Simpan Data Relasi (profiles)
            $user->profile()->create([
                'full_name' => $validated['name'],
                'nik' => $validated['nik'],
                'jenis_kelamin' => $validated['jenis_kelamin'],
                'telepon' => $validated['telepon'] ?? null,
                'tempat_lahir' => $validated['tempat_lahir'] ?? null,
                'tanggal_lahir' => $validated['tanggal_lahir'] ?? null,
                'alamat' => $validated['alamat'] ?? null,
            ]);

            DB::commit();

            return redirect()
                ->route('admin.users.index')
                ->with('success', 'Akun warga berhasil dibuat.')
                ->with('generated_password', $password)
                ->with('reset_password', $password) // Untuk compatibility script Blade lama
                ->with('user_name', $validated['name'])
                ->with('reset_name', $validated['name'])
                ->with('user_email', $validated['email'])
                ->with('reset_email', $validated['email'])
                ->with('user_nik', $validated['nik']); // Tambahan NIK untuk mempermudah copy

        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Admin UserController store gagal', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return back()
                ->withInput()
                ->withErrors(['system' => 'Gagal membuat akun warga. Cek struktur database dan log Laravel.']);
        }
    }

    public function show($id)
    {
        $user = User::with('profile')
            ->where('role', 'user')
            ->findOrFail($id);

        $summary = $this->sasaranSummary($user->id);

        return view('admin.users.show', compact('user', 'summary'));
    }

    public function edit($id)
    {
        $user = User::with('profile')
            ->where('role', 'user')
            ->findOrFail($id);

        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $user = User::with('profile')
            ->where('role', 'user')
            ->findOrFail($id);

        // Validasi dengan pengecualian ID saat ini (Ignore)
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:191'],
            'email' => ['required', 'email', 'max:191', Rule::unique('users')->ignore($user->id)],
            'nik' => ['required', 'digits:16', Rule::unique('users')->ignore($user->id)],
            'jenis_kelamin' => ['required', Rule::in(['L', 'P'])],
            'telepon' => ['nullable', 'string', 'max:20'],
            'tempat_lahir' => ['nullable', 'string', 'max:100'],
            'tanggal_lahir' => ['nullable', 'date'],
            'alamat' => ['nullable', 'string'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email sudah digunakan.',
            'nik.required' => 'NIK wajib diisi.',
            'nik.digits' => 'NIK harus 16 digit angka.',
            'nik.unique' => 'NIK sudah digunakan.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
        ]);

        DB::beginTransaction();

        try {
            // 1. Update tabel Utama (users)
            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'nik' => $validated['nik'],
                'status' => $validated['status'] ?? 'active',
            ]);

            // 2. Update atau Buat Relasi (profiles)
            $user->profile()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'full_name' => $validated['name'],
                    'nik' => $validated['nik'],
                    'jenis_kelamin' => $validated['jenis_kelamin'],
                    'telepon' => $validated['telepon'] ?? null,
                    'tempat_lahir' => $validated['tempat_lahir'] ?? null,
                    'tanggal_lahir' => $validated['tanggal_lahir'] ?? null,
                    'alamat' => $validated['alamat'] ?? null,
                ]
            );

            DB::commit();

            return redirect()
                ->route('admin.users.show', $id)
                ->with('success', 'Data warga berhasil diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Admin UserController update gagal', [
                'message' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->withErrors(['system' => 'Gagal memperbarui data warga.']);
        }
    }

    public function destroy($id)
    {
        $user = User::with('profile')
            ->where('role', 'user')
            ->findOrFail($id);

        $name = $user->profile?->full_name ?? $user->name;

        // OPTIMASI: Pengecekan relasional yang lebih instan
        if ($this->hasOperationalHistory($user->id)) {
            return back()
                ->with('warning', "Akun warga {$name} tidak dapat dihapus karena sudah terkait dengan data sasaran (Balita/Remaja/Lansia) atau riwayat medis. Silakan ubah statusnya menjadi Nonaktif.");
        }

        DB::beginTransaction();

        try {
            if ($user->profile) {
                $user->profile->delete();
            }

            $user->delete();

            DB::commit();

            return redirect()
                ->route('admin.users.index')
                ->with('success', "Akun warga {$name} berhasil dihapus permanen.");
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Admin UserController destroy gagal', [
                'message' => $e->getMessage(),
                'user_id' => $user->id,
            ]);

            return back()->withErrors(['system' => 'Terjadi kesalahan sistem. Gagal menghapus akun warga.']);
        }
    }

    public function generatePassword($id)
    {
        return $this->makeNewPasswordResponse($id);
    }

    public function resetPassword($id)
    {
        return $this->makeNewPasswordResponse($id);
    }

    private function makeNewPasswordResponse($id)
    {
        $user = User::with('profile')
            ->where('role', 'user')
            ->findOrFail($id);

        $password = $this->makePassword();

        // Update password baru saja
        $user->update([
            'password' => Hash::make($password),
        ]);

        $name = $user->profile?->full_name ?? $user->name;

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Password baru warga berhasil dibuat/di-reset.')
            ->with('generated_password', $password)
            ->with('reset_password', $password)
            ->with('user_name', $name)
            ->with('reset_name', $name)
            ->with('user_email', $user->email)
            ->with('reset_email', $user->email)
            ->with('user_nik', $user->nik);
    }

    private function validateUserPayload(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:191'],
            'email' => ['required', 'email', 'max:191', 'unique:users,email'],
            'nik' => ['required', 'digits:16', 'unique:users,nik'],
            'jenis_kelamin' => ['required', Rule::in(['L', 'P'])],
            'telepon' => ['nullable', 'string', 'max:20'],
            'tempat_lahir' => ['nullable', 'string', 'max:100'],
            'tanggal_lahir' => ['nullable', 'date'],
            'alamat' => ['nullable', 'string'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah digunakan.',
            'nik.required' => 'NIK wajib diisi.',
            'nik.digits' => 'NIK harus 16 digit angka.',
            'nik.unique' => 'NIK ini sudah terdaftar di sistem.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
        ]);
    }

    /**
     * OPTIMASI: Hitung ringkasan lansia, balita, remaja
     * Menggunakan raw query builder sangat ringan dibanding Eloquent Model
     */
    private function sasaranSummary(int $userId): array
    {
        return [
            'balita' => DB::table('balitas')->where('user_id', $userId)->count(),
            'remaja' => DB::table('remajas')->where('user_id', $userId)->count(),
            'lansia' => DB::table('lansias')->where('user_id', $userId)->count(),
        ];
    }

    /**
     * OPTIMASI ZERO-OVERHEAD: Validasi history data saat akan menghapus akun.
     * Tidak ada lagi looping schema. Langsung cek kondisi (exists).
     */
    private function hasOperationalHistory(int $userId): bool
{
    return DB::table('balitas')->where('user_id', $userId)->exists()
        || DB::table('remajas')->where('user_id', $userId)->exists()
        || DB::table('lansias')->where('user_id', $userId)->exists()
        || DB::table('pemeriksaans')->where('user_id', $userId)->exists()
        || DB::table('notifikasis')->where('user_id', $userId)->exists();
}
    /**
     * OPTIMASI UX: Generate Password yang lebih "Human-Readable"
     * Format: Warga + [4 Angka Acak] + ! (Contoh: Warga9102!)
     */
    private function makePassword(): string
    {
        try {
            $randomNumber = random_int(1000, 9999);
        } catch (\Exception $e) {
            $randomNumber = mt_rand(1000, 9999);
        }
        
        return 'Warga' . $randomNumber . '!';
    }
}