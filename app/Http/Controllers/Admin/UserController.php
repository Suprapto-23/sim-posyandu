<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 12);
        $perPage = max(5, min($perPage, 25));

        $search = trim((string) $request->input('search', ''));
        $status = $request->input('status', 'semua');
        $kategori = $request->input('kategori', 'semua');

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

        // Filter berdasarkan kepemilikan sasaran
        if ($kategori === 'balita') {
            if (Schema::hasTable('balitas')) {
                $query->whereExists(function ($q) {
                    $q->select(DB::raw(1))->from('balitas')->whereColumn('balitas.user_id', 'users.id');
                });
            }
        } elseif ($kategori === 'remaja') {
            if (Schema::hasTable('remajas')) {
                $query->whereExists(function ($q) {
                    $q->select(DB::raw(1))->from('remajas')->whereColumn('remajas.user_id', 'users.id');
                });
            }
        } elseif ($kategori === 'lansia') {
            if (Schema::hasTable('lansias')) {
                $query->whereExists(function ($q) {
                    $q->select(DB::raw(1))->from('lansias')->whereColumn('lansias.user_id', 'users.id');
                });
            }
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
        $password = $this->makePassword();

        DB::beginTransaction();

        try {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'nik' => $validated['nik'],
                'password' => Hash::make($password),
                'role' => 'user',
                'status' => $validated['status'] ?? 'active',
            ]);

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
                ->with('reset_password', $password)
                ->with('user_name', $validated['name'])
                ->with('reset_name', $validated['name'])
                ->with('user_email', $validated['email'])
                ->with('reset_email', $validated['email'])
                ->with('user_nik', $validated['nik']);

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['system' => 'Gagal membuat akun warga.']);
        }
    }

    public function show($id)
    {
        $user = User::with('profile')->where('role', 'user')->findOrFail($id);
        $summary = $this->sasaranSummary($user->id);
        return view('admin.users.show', compact('user', 'summary'));
    }

    public function edit($id)
    {
        $user = User::with('profile')->where('role', 'user')->findOrFail($id);
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $user = User::with('profile')->where('role', 'user')->findOrFail($id);

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
            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'nik' => $validated['nik'],
                'status' => $validated['status'] ?? 'active',
            ]);

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
            return redirect()->route('admin.users.show', $id)->with('success', 'Data warga berhasil diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['system' => 'Gagal memperbarui data warga.']);
        }
    }

    public function toggleStatus($id)
    {
        $user = User::where('role', 'user')->findOrFail($id);
        $newStatus = $user->status === 'active' ? 'inactive' : 'active';
        $user->update(['status' => $newStatus]);
        $statusText = $newStatus === 'active' ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Status akun {$user->name} berhasil {$statusText}.");
    }

    public function destroy($id)
    {
        $user = User::with('profile')->where('role', 'user')->findOrFail($id);
        $name = $user->profile?->full_name ?? $user->name;

        DB::beginTransaction();

        try {
            // 1. LEPASKAN TAUTAN (UNLINK) DATA REKAM MEDIS
            // Set user_id menjadi null pada data medis. Ini memastikan akun login warga 
            // dapat dihapus tanpa memicu error "Foreign Key", namun data balita/lansia tetap aman di posyandu.
            $tables = ['balitas', 'remajas', 'lansias'];
            foreach ($tables as $table) {
                if (Schema::hasTable($table) && Schema::hasColumn($table, 'user_id')) {
                    DB::table($table)->where('user_id', $user->id)->update(['user_id' => null]);
                }
            }

            // 2. HAPUS DATA RELASI PRIBADI YANG MENGIKAT
            if (Schema::hasTable('profiles')) {
                DB::table('profiles')->where('user_id', $user->id)->delete();
            }
            if (Schema::hasTable('login_logs')) {
                DB::table('login_logs')->where('user_id', $user->id)->delete();
            }
            if (Schema::hasTable('notifikasis')) {
                DB::table('notifikasis')->where('user_id', $user->id)->delete();
            }

            // 3. HAPUS AKUN UTAMA
            $user->delete();

            DB::commit();
            return redirect()->route('admin.users.index')->with('success', "Akun warga {$name} berhasil dihapus permanen. Data posyandu pasien telah dilepaskan.");
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Admin UserController destroy gagal', ['message' => $e->getMessage()]);
            // Tampilkan error aslinya ke layar jika ada constraint lain
            return back()->withErrors(['system' => "Sistem menolak penghapusan: " . $e->getMessage()]);
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
        $user = User::with('profile')->where('role', 'user')->findOrFail($id);
        $password = $this->makePassword();

        $user->update(['password' => Hash::make($password)]);
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
        ]);
    }

    private function sasaranSummary(int $userId): array
    {
        $summary = ['balita' => 0, 'remaja' => 0, 'lansia' => 0];
        if (Schema::hasTable('balitas') && Schema::hasColumn('balitas', 'user_id')) {
            $summary['balita'] = DB::table('balitas')->where('user_id', $userId)->count();
        }
        if (Schema::hasTable('remajas') && Schema::hasColumn('remajas', 'user_id')) {
            $summary['remaja'] = DB::table('remajas')->where('user_id', $userId)->count();
        }
        if (Schema::hasTable('lansias') && Schema::hasColumn('lansias', 'user_id')) {
            $summary['lansia'] = DB::table('lansias')->where('user_id', $userId)->count();
        }
        return $summary;
    }

    private function makePassword(): string
    {
        return Str::random(10);
    }
}