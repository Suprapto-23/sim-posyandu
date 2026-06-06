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

class UserController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 12);
        $perPage = max(5, min($perPage, 25));

        $search = trim((string) $request->input('search', ''));

        $query = User::query()
            ->select($this->userColumns())
            ->with([
                'profile' => function ($profile) {
                    $profile->select($this->profileColumns());
                },
            ])
            ->where('role', 'user');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                if (preg_match('/^[0-9]+$/', $search)) {
                    if (Schema::hasColumn('users', 'nik')) {
                        $q->where('nik', 'like', "{$search}%");
                    }

                    $q->orWhereHas('profile', function ($profile) use ($search) {
                        if (Schema::hasColumn('profiles', 'nik')) {
                            $profile->where('nik', 'like', "{$search}%");
                        }

                        if (Schema::hasColumn('profiles', 'telepon')) {
                            $profile->orWhere('telepon', 'like', "{$search}%");
                        }
                    });

                    return;
                }

                $q->where('name', 'like', "{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('profile', function ($profile) use ($search) {
                        if (Schema::hasColumn('profiles', 'full_name')) {
                            $profile->where('full_name', 'like', "{$search}%")
                                ->orWhere('full_name', 'like', "%{$search}%");
                        }
                    });
            });
        }

        if ($request->filled('status') && in_array($request->status, ['active', 'inactive'], true)) {
            $query->where('status', $request->status);
        }

        $users = $query
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();

        $stats = [
            'total' => User::where('role', 'user')->count(),
            'aktif' => User::where('role', 'user')->where('status', 'active')->count(),
            'nonaktif' => User::where('role', 'user')->where('status', 'inactive')->count(),
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
            $userData = $this->filterColumns('users', [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'nik' => $validated['nik'],
                'password' => Hash::make($password),
                'role' => 'user',
                'status' => $validated['status'] ?? 'active',
                'must_change_password' => true,
            ]);

            $user = User::create($userData);

            $this->saveProfile($user->id, $validated, true);

            DB::commit();

            return redirect()
                ->route('admin.users.index')
                ->with('success', 'Akun warga berhasil dibuat.')
                ->with('generated_password', $password)
                ->with('reset_password', $password)
                ->with('user_name', $validated['name'])
                ->with('reset_name', $validated['name'])
                ->with('user_email', $validated['email'])
                ->with('reset_email', $validated['email']);
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Admin UserController store gagal', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return back()
                ->withInput()
                ->withErrors([
                    'system' => 'Gagal membuat akun warga. Cek struktur database dan log Laravel.',
                ]);
        }
    }

    public function show($id)
    {
        $user = User::with([
                'profile' => function ($profile) {
                    $profile->select($this->profileColumns());
                },
            ])
            ->where('role', 'user')
            ->findOrFail($id);

        $summary = $this->sasaranSummary($user->id);

        return view('admin.users.show', compact('user', 'summary'));
    }

    public function edit($id)
    {
        $user = User::with([
                'profile' => function ($profile) {
                    $profile->select($this->profileColumns());
                },
            ])
            ->where('role', 'user')
            ->findOrFail($id);

        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $user = User::with('profile')
            ->where('role', 'user')
            ->findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:191'],
            'jenis_kelamin' => ['required', Rule::in(['L', 'P'])],
            'telepon' => ['nullable', 'string', 'max:20'],
            'tempat_lahir' => ['nullable', 'string', 'max:100'],
            'tanggal_lahir' => ['nullable', 'date'],
            'alamat' => ['nullable', 'string'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
        ]);

        DB::beginTransaction();

        try {
            $user->update($this->filterColumns('users', [
                'name' => $validated['name'],
                'status' => $validated['status'] ?? 'active',
            ]));

            $validated['nik'] = $user->nik ?? $user->profile?->nik;
            $validated['email'] = $user->email;

            $this->saveProfile($user->id, $validated, false);

            DB::commit();

            return redirect()
                ->route('admin.users.show', $id)
                ->with('success', 'Data warga berhasil diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Admin UserController update gagal', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return back()
                ->withInput()
                ->withErrors([
                    'system' => 'Gagal memperbarui data warga.',
                ]);
        }
    }

    public function destroy($id)
    {
        $user = User::with('profile')
            ->where('role', 'user')
            ->findOrFail($id);

        $name = $user->profile?->full_name ?? $user->name;

        if ($this->hasOperationalHistory($user->id)) {
            return back()
                ->with('warning', "Akun warga {$name} tidak dihapus karena sudah memiliki data sasaran, pemeriksaan, atau riwayat layanan. Nonaktifkan akun jika warga tidak lagi menggunakan sistem.");
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
                ->with('success', "Akun warga {$name} berhasil dihapus.");
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Admin UserController destroy gagal', [
                'message' => $e->getMessage(),
                'user_id' => $user->id,
            ]);

            return back()
                ->withErrors([
                    'system' => 'Gagal menghapus akun warga.',
                ]);
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

        $user->update($this->filterColumns('users', [
            'password' => Hash::make($password),
            'must_change_password' => true,
        ]));

        $name = $user->profile?->full_name ?? $user->name;

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Password baru warga berhasil dibuat.')
            ->with('generated_password', $password)
            ->with('reset_password', $password)
            ->with('user_name', $name)
            ->with('reset_name', $name)
            ->with('user_email', $user->email)
            ->with('reset_email', $user->email);
    }

    private function validateUserPayload(Request $request): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:191'],
            'email' => ['required', 'email', 'max:191', 'unique:users,email'],
            'nik' => ['required', 'digits:16'],
            'jenis_kelamin' => ['required', Rule::in(['L', 'P'])],
            'telepon' => ['nullable', 'string', 'max:20'],
            'tempat_lahir' => ['nullable', 'string', 'max:100'],
            'tanggal_lahir' => ['nullable', 'date'],
            'alamat' => ['nullable', 'string'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ];

        if (Schema::hasColumn('users', 'nik')) {
            $rules['nik'][] = 'unique:users,nik';
        }

        if (Schema::hasTable('profiles') && Schema::hasColumn('profiles', 'nik')) {
            $rules['nik'][] = 'unique:profiles,nik';
        }

        return $request->validate($rules, [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah digunakan.',
            'nik.required' => 'NIK wajib diisi.',
            'nik.digits' => 'NIK harus 16 digit angka.',
            'nik.unique' => 'NIK ini sudah terdaftar.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
        ]);
    }

    private function userColumns(): array
    {
        $columns = ['id'];

        foreach (['name', 'email', 'nik', 'role', 'status', 'created_at', 'updated_at'] as $column) {
            if (Schema::hasColumn('users', $column)) {
                $columns[] = $column;
            }
        }

        return $columns;
    }

    private function profileColumns(): array
    {
        $columns = ['id', 'user_id'];

        foreach (['full_name', 'nik', 'jenis_kelamin', 'tempat_lahir', 'tanggal_lahir', 'alamat', 'telepon', 'created_at', 'updated_at'] as $column) {
            if (Schema::hasColumn('profiles', $column)) {
                $columns[] = $column;
            }
        }

        return $columns;
    }

    private function filterColumns(string $table, array $payload): array
    {
        $filtered = [];

        foreach ($payload as $column => $value) {
            if (Schema::hasColumn($table, $column)) {
                $filtered[$column] = $value;
            }
        }

        return $filtered;
    }

    private function saveProfile(int $userId, array $data, bool $isCreate): void
    {
        if (! Schema::hasTable('profiles')) {
            return;
        }

        $payload = $this->filterColumns('profiles', [
            'user_id' => $userId,
            'full_name' => $data['name'] ?? null,
            'nik' => $data['nik'] ?? null,
            'jenis_kelamin' => $data['jenis_kelamin'] ?? null,
            'telepon' => $data['telepon'] ?? null,
            'tempat_lahir' => $data['tempat_lahir'] ?? null,
            'tanggal_lahir' => $data['tanggal_lahir'] ?? null,
            'alamat' => $data['alamat'] ?? null,
            'updated_at' => now(),
        ]);

        if ($isCreate && Schema::hasColumn('profiles', 'created_at')) {
            $payload['created_at'] = now();
        }

        DB::table('profiles')->updateOrInsert(
            ['user_id' => $userId],
            $payload
        );
    }

    private function sasaranSummary(int $userId): array
    {
        return [
            'balita' => $this->countRelated('balitas', $userId),
            'remaja' => $this->countRelated('remajas', $userId),
            'lansia' => $this->countRelated('lansias', $userId),
        ];
    }

    private function countRelated(string $table, int $userId): int
    {
        if (! Schema::hasTable($table)) {
            return 0;
        }

        foreach (['user_id', 'warga_id', 'akun_id'] as $column) {
            if (! Schema::hasColumn($table, $column)) {
                continue;
            }

            return DB::table($table)
                ->where($column, $userId)
                ->count();
        }

        return 0;
    }

    private function hasOperationalHistory(int $userId): bool
    {
        $checks = [
            ['table' => 'balitas', 'columns' => ['user_id', 'warga_id', 'akun_id']],
            ['table' => 'remajas', 'columns' => ['user_id', 'warga_id', 'akun_id']],
            ['table' => 'lansias', 'columns' => ['user_id', 'warga_id', 'akun_id']],
            ['table' => 'pemeriksaans', 'columns' => ['user_id', 'warga_id', 'pasien_id']],
            ['table' => 'rekam_medis', 'columns' => ['user_id', 'warga_id', 'pasien_id']],
            ['table' => 'imunisasis', 'columns' => ['user_id', 'warga_id', 'pasien_id']],
            ['table' => 'notifikasis', 'columns' => ['user_id']],
        ];

        foreach ($checks as $check) {
            if (! Schema::hasTable($check['table'])) {
                continue;
            }

            foreach ($check['columns'] as $column) {
                if (! Schema::hasColumn($check['table'], $column)) {
                    continue;
                }

                if (DB::table($check['table'])->where($column, $userId)->exists()) {
                    return true;
                }
            }
        }

        return false;
    }

    private function makePassword(): string
    {
        $lower = 'abcdefghjkmnpqrstuvwxyz';
        $upper = 'ABCDEFGHJKMNPQRSTUVWXYZ';
        $number = '23456789';
        $symbol = '!@#';
        $all = $lower . $upper . $number . $symbol;

        $password = [
            $lower[random_int(0, strlen($lower) - 1)],
            $upper[random_int(0, strlen($upper) - 1)],
            $number[random_int(0, strlen($number) - 1)],
            $symbol[random_int(0, strlen($symbol) - 1)],
        ];

        for ($i = 0; $i < 8; $i++) {
            $password[] = $all[random_int(0, strlen($all) - 1)];
        }

        shuffle($password);

        return implode('', $password);
    }
}