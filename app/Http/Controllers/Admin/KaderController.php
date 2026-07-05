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

class KaderController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 12);
        $perPage = max(5, min($perPage, 25));

        $search = trim((string) $request->input('search', ''));
        $status = $request->input('status', 'semua');

        $query = User::query()
            ->select('id', 'name', 'email', 'nik', 'role', 'status', 'created_at')
            ->with([
                'profile' => function ($profile) {
                    $profile->select($this->profileColumns());
                },
                'kader' => function ($kader) {
                    $kader->select($this->kaderColumns());
                },
            ])
            ->where('role', 'kader');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                if (preg_match('/^[0-9]+$/', $search)) {
                    $q->where('nik', 'like', "{$search}%")
                        ->orWhereHas('profile', function ($profile) use ($search) {
                            $profile->where('nik', 'like', "{$search}%");
                            if (Schema::hasColumn('profiles', 'telepon')) {
                                $profile->orWhere('telepon', 'like', "{$search}%");
                            }
                        });
                    return;
                }
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('profile', function ($profile) use ($search) {
                        $profile->where('full_name', 'like', "%{$search}%");
                    });
            });
        }

        if (in_array($status, ['active', 'inactive'], true)) {
            $query->where('status', $status);
        }

        $kaders = $query
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();

        // OPTIMASI: 1 Query untuk menghitung semua statistik (Mengikuti gaya UserController)
        $statsData = User::where('role', 'kader')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN status = "active" THEN 1 ELSE 0 END) as aktif')
            ->selectRaw('SUM(CASE WHEN status = "inactive" THEN 1 ELSE 0 END) as nonaktif')
            ->first();

        $stats = [
            'total' => (int) ($statsData->total ?? 0),
            'aktif' => (int) ($statsData->aktif ?? 0),
            'nonaktif' => (int) ($statsData->nonaktif ?? 0),
        ];

        return view('admin.kaders.index', compact('kaders', 'stats'));
    }

    public function create()
    {
        return view('admin.kaders.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:191'],
            'email' => ['required', 'email', 'max:191', 'unique:users,email'],
            'nik' => ['required', 'digits:16', 'unique:users,nik', 'unique:profiles,nik'],
            'jenis_kelamin' => ['required', Rule::in(['L', 'P'])],
            'telepon' => ['nullable', 'string', 'max:20'],
            'tempat_lahir' => ['nullable', 'string', 'max:100'],
            'tanggal_lahir' => ['required', 'date'],
            'alamat' => ['nullable', 'string'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
            'jabatan' => ['nullable', 'string', 'max:191'],
            'wilayah_tugas' => ['nullable', 'string', 'max:191'],
        ]);

        $password = $this->makePassword();

        DB::beginTransaction();
        try {
            $userData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'nik' => $validated['nik'],
                'password' => Hash::make($password),
                'role' => 'kader',
                'status' => $validated['status'] ?? 'active',
            ];

            if (Schema::hasColumn('users', 'must_change_password')) {
                $userData['must_change_password'] = true;
            }

            $user = User::create($userData);

            $user->profile()->create([
                'user_id' => $user->id,
                'full_name' => $validated['name'],
                'nik' => $validated['nik'],
                'jenis_kelamin' => $validated['jenis_kelamin'],
                'telepon' => $validated['telepon'] ?? null,
                'tempat_lahir' => $validated['tempat_lahir'] ?? null,
                'tanggal_lahir' => $validated['tanggal_lahir'],
                'alamat' => $validated['alamat'] ?? null,
            ]);

            $this->saveKaderDetail($user->id, $validated);
            DB::commit();

            return redirect()
                ->route('admin.kaders.index')
                ->with('success', 'Akun kader berhasil dibuat.')
                ->with('generated_password', $password)
                ->with('reset_password', $password)
                ->with('user_name', $validated['name'])
                ->with('reset_name', $validated['name']);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Admin KaderController store gagal', ['message' => $e->getMessage()]);
            return back()->withInput()->withErrors(['system' => 'Gagal membuat akun kader.']);
        }
    }

    public function show($id)
    {
        $kader = User::with([
                'profile' => fn($p) => $p->select($this->profileColumns()),
                'kader' => fn($k) => $k->select($this->kaderColumns()),
            ])->where('role', 'kader')->findOrFail($id);

        return view('admin.kaders.show', compact('kader'));
    }

    public function edit($id)
    {
        $kader = User::with([
                'profile' => fn($p) => $p->select($this->profileColumns()),
                'kader' => fn($k) => $k->select($this->kaderColumns()),
            ])->where('role', 'kader')->findOrFail($id);

        return view('admin.kaders.edit', compact('kader'));
    }

    public function update(Request $request, $id)
    {
        $kader = User::with(['profile', 'kader'])->where('role', 'kader')->findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:191'],
            'jenis_kelamin' => ['required', Rule::in(['L', 'P'])],
            'telepon' => ['nullable', 'string', 'max:20'],
            'tempat_lahir' => ['nullable', 'string', 'max:100'],
            'tanggal_lahir' => ['required', 'date'],
            'alamat' => ['nullable', 'string'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
            'jabatan' => ['nullable', 'string', 'max:191'],
            'wilayah_tugas' => ['nullable', 'string', 'max:191'],
        ]);

        DB::beginTransaction();
        try {
            $kader->update([
                'name' => $validated['name'],
                'status' => $validated['status'] ?? 'active',
            ]);

            $profileData = [
                'full_name' => $validated['name'],
                'jenis_kelamin' => $validated['jenis_kelamin'],
                'telepon' => $validated['telepon'] ?? null,
                'tempat_lahir' => $validated['tempat_lahir'] ?? null,
                'tanggal_lahir' => $validated['tanggal_lahir'],
                'alamat' => $validated['alamat'] ?? null,
            ];

            if ($kader->profile) {
                $kader->profile->update($profileData);
            } else {
                $kader->profile()->create(array_merge($profileData, ['user_id' => $kader->id, 'nik' => $kader->nik]));
            }

            $this->saveKaderDetail($kader->id, $validated);
            DB::commit();

            return redirect()->route('admin.kaders.show', $id)->with('success', 'Data kader berhasil diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Admin KaderController update gagal', ['message' => $e->getMessage()]);
            return back()->withInput()->withErrors(['system' => 'Gagal memperbarui data kader.']);
        }
    }

    // FITUR BARU: Menambahkan fungsi Toggle Status untuk mengatasi error route
    public function toggleStatus($id)
    {
        $kader = User::where('role', 'kader')->findOrFail($id);
        $newStatus = $kader->status === 'active' ? 'inactive' : 'active';
        $kader->update(['status' => $newStatus]);
        $statusText = $newStatus === 'active' ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Status akun {$kader->name} berhasil {$statusText}.");
    }

    public function destroy($id)
    {
        $kader = User::with(['profile', 'kader'])->where('role', 'kader')->findOrFail($id);
        $name = $kader->profile?->full_name ?? $kader->name;

        if ($this->hasOperationalHistory($kader->id)) {
            return back()->with('warning', "Akun kader {$name} tidak dihapus karena memiliki riwayat operasional pendataan. Nonaktifkan akun saja.");
        }

        DB::beginTransaction();
        try {
            if (Schema::hasTable('kaders')) DB::table('kaders')->where('user_id', $kader->id)->delete();
            if ($kader->profile) $kader->profile->delete();
            
            $kader->delete();
            DB::commit();

            return redirect()->route('admin.kaders.index')->with('success', "Akun kader {$name} berhasil dihapus.");
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Admin KaderController destroy gagal', ['message' => $e->getMessage()]);
            return back()->withErrors(['system' => 'Gagal menghapus data kader.']);
        }
    }

    public function resetPassword($id)
    {
        $kader = User::with('profile')->where('role', 'kader')->findOrFail($id);
        $password = $this->makePassword();

        $updateData = ['password' => Hash::make($password)];
        if (Schema::hasColumn('users', 'must_change_password')) {
            $updateData['must_change_password'] = true;
        }

        $kader->update($updateData);
        $name = $kader->profile?->full_name ?? $kader->name;

        return redirect()
            ->route('admin.kaders.index')
            ->with('success', 'Password baru kader berhasil dibuat.')
            ->with('generated_password', $password)
            ->with('reset_password', $password)
            ->with('user_name', $name)
            ->with('reset_name', $name);
    }

    private function profileColumns(): array
    {
        $columns = ['id', 'user_id'];
        foreach (['full_name', 'nik', 'jenis_kelamin', 'tempat_lahir', 'tanggal_lahir', 'alamat', 'telepon'] as $column) {
            if (Schema::hasColumn('profiles', $column)) $columns[] = $column;
        }
        return $columns;
    }

    private function kaderColumns(): array
    {
        $columns = ['id', 'user_id'];
        foreach (['jabatan', 'wilayah_tugas'] as $column) {
            if (Schema::hasColumn('kaders', $column)) $columns[] = $column;
        }
        return $columns;
    }

    private function saveKaderDetail(int $userId, array $data): void
    {
        if (!Schema::hasTable('kaders')) return;

        $payload = [];
        if (Schema::hasColumn('kaders', 'jabatan')) $payload['jabatan'] = $data['jabatan'] ?? 'Kader Posyandu';
        if (Schema::hasColumn('kaders', 'wilayah_tugas')) $payload['wilayah_tugas'] = $data['wilayah_tugas'] ?? null;
        if (Schema::hasColumn('kaders', 'updated_at')) $payload['updated_at'] = now();

        $exists = DB::table('kaders')->where('user_id', $userId)->exists();
        if (!$exists && Schema::hasColumn('kaders', 'created_at')) $payload['created_at'] = now();

        DB::table('kaders')->updateOrInsert(['user_id' => $userId], $payload);
    }

    private function hasOperationalHistory(int $userId): bool
    {
        $checks = [
            ['table' => 'balitas', 'columns' => ['created_by', 'updated_by', 'kader_id']],
            ['table' => 'remajas', 'columns' => ['created_by', 'updated_by', 'kader_id']],
            ['table' => 'lansias', 'columns' => ['created_by', 'updated_by', 'kader_id']],
            ['table' => 'absensi_posyandu', 'columns' => ['dicatat_oleh']],
            ['table' => 'pengukurans', 'columns' => ['created_by', 'updated_by', 'kader_id']],
            ['table' => 'pemeriksaans', 'columns' => ['created_by', 'updated_by', 'kader_id']],
        ];

        foreach ($checks as $check) {
            if (!Schema::hasTable($check['table'])) continue;
            foreach ($check['columns'] as $column) {
                if (!Schema::hasColumn($check['table'], $column)) continue;
                if (DB::table($check['table'])->where($column, $userId)->exists()) return true;
            }
        }
        return false;
    }

    private function makePassword(): string
    {
        return \Illuminate\Support\Str::random(10);
    }
}