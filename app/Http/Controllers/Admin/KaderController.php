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

                $q->where('name', 'like', "{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('profile', function ($profile) use ($search) {
                        $profile->where('full_name', 'like', "{$search}%")
                            ->orWhere('full_name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status') && in_array($request->status, ['active', 'inactive'], true)) {
            $query->where('status', $request->status);
        }

        $kaders = $query
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();

        $stats = [
            'total' => User::where('role', 'kader')->count(),
            'aktif' => User::where('role', 'kader')->where('status', 'active')->count(),
            'nonaktif' => User::where('role', 'kader')->where('status', 'inactive')->count(),
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
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah digunakan.',
            'nik.required' => 'NIK wajib diisi.',
            'nik.digits' => 'NIK harus 16 digit angka.',
            'nik.unique' => 'NIK ini sudah terdaftar.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
            'tanggal_lahir.required' => 'Tanggal lahir wajib diisi.',
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
                ->with('reset_name', $validated['name'])
                ->with('user_email', $validated['email'])
                ->with('reset_email', $validated['email']);
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Admin KaderController store gagal', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return back()
                ->withInput()
                ->withErrors([
                    'system' => 'Gagal membuat akun kader. Cek struktur database dan log Laravel.',
                ]);
        }
    }

    public function show($id)
    {
        $kader = User::with([
                'profile' => function ($profile) {
                    $profile->select($this->profileColumns());
                },
                'kader' => function ($kader) {
                    $kader->select($this->kaderColumns());
                },
            ])
            ->where('role', 'kader')
            ->findOrFail($id);

        return view('admin.kaders.show', compact('kader'));
    }

    public function edit($id)
    {
        $kader = User::with([
                'profile' => function ($profile) {
                    $profile->select($this->profileColumns());
                },
                'kader' => function ($kader) {
                    $kader->select($this->kaderColumns());
                },
            ])
            ->where('role', 'kader')
            ->findOrFail($id);

        return view('admin.kaders.edit', compact('kader'));
    }

    public function update(Request $request, $id)
    {
        $kader = User::with(['profile', 'kader'])
            ->where('role', 'kader')
            ->findOrFail($id);

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
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
            'tanggal_lahir.required' => 'Tanggal lahir wajib diisi.',
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
                $kader->profile()->create(array_merge($profileData, [
                    'user_id' => $kader->id,
                    'nik' => $kader->nik,
                ]));
            }

            $this->saveKaderDetail($kader->id, $validated);

            DB::commit();

            return redirect()
                ->route('admin.kaders.show', $id)
                ->with('success', 'Data kader berhasil diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Admin KaderController update gagal', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return back()
                ->withInput()
                ->withErrors([
                    'system' => 'Gagal memperbarui data kader.',
                ]);
        }
    }

    public function destroy($id)
    {
        $kader = User::with(['profile', 'kader'])
            ->where('role', 'kader')
            ->findOrFail($id);

        $name = $kader->profile?->full_name ?? $kader->name;

        if ($this->hasOperationalHistory($kader->id)) {
            return back()
                ->with('warning', "Akun kader {$name} tidak dihapus karena sudah memiliki riwayat operasional. Nonaktifkan akun jika kader tidak lagi bertugas.");
        }

        DB::beginTransaction();

        try {
            if (Schema::hasTable('kaders')) {
                DB::table('kaders')->where('user_id', $kader->id)->delete();
            }

            if ($kader->profile) {
                $kader->profile->delete();
            }

            $kader->delete();

            DB::commit();

            return redirect()
                ->route('admin.kaders.index')
                ->with('success', "Akun kader {$name} berhasil dihapus.");
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Admin KaderController destroy gagal', [
                'message' => $e->getMessage(),
                'user_id' => $kader->id,
            ]);

            return back()
                ->withErrors([
                    'system' => 'Gagal menghapus data kader.',
                ]);
        }
    }

    public function resetPassword($id)
    {
        $kader = User::with('profile')
            ->where('role', 'kader')
            ->findOrFail($id);

        $password = $this->makePassword();

        $updateData = [
            'password' => Hash::make($password),
        ];

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
            ->with('reset_name', $name)
            ->with('user_email', $kader->email)
            ->with('reset_email', $kader->email);
    }

    private function profileColumns(): array
    {
        $columns = ['id', 'user_id'];

        foreach (['full_name', 'nik', 'jenis_kelamin', 'tempat_lahir', 'tanggal_lahir', 'alamat', 'telepon'] as $column) {
            if (Schema::hasColumn('profiles', $column)) {
                $columns[] = $column;
            }
        }

        return $columns;
    }

    private function kaderColumns(): array
    {
        $columns = ['id', 'user_id'];

        foreach (['jabatan', 'wilayah_tugas'] as $column) {
            if (Schema::hasColumn('kaders', $column)) {
                $columns[] = $column;
            }
        }

        return $columns;
    }

    private function saveKaderDetail(int $userId, array $data): void
    {
        if (! Schema::hasTable('kaders')) {
            return;
        }

        $payload = [];

        if (Schema::hasColumn('kaders', 'jabatan')) {
            $payload['jabatan'] = $data['jabatan'] ?? 'Kader Posyandu';
        }

        if (Schema::hasColumn('kaders', 'wilayah_tugas')) {
            $payload['wilayah_tugas'] = $data['wilayah_tugas'] ?? null;
        }

        if (Schema::hasColumn('kaders', 'updated_at')) {
            $payload['updated_at'] = now();
        }

        $exists = DB::table('kaders')
            ->where('user_id', $userId)
            ->exists();

        if (! $exists && Schema::hasColumn('kaders', 'created_at')) {
            $payload['created_at'] = now();
        }

        DB::table('kaders')->updateOrInsert(
            ['user_id' => $userId],
            $payload
        );
    }

    private function hasOperationalHistory(int $userId): bool
    {
        $checks = [
            ['table' => 'balitas', 'columns' => ['created_by', 'updated_by', 'kader_id']],
            ['table' => 'remajas', 'columns' => ['created_by', 'updated_by', 'kader_id']],
            ['table' => 'lansias', 'columns' => ['created_by', 'updated_by', 'kader_id']],
            ['table' => 'absensi_posyandu', 'columns' => ['dicatat_oleh']],
            ['table' => 'absensi_details', 'columns' => ['created_by', 'updated_by', 'kader_id']],
            ['table' => 'pengukurans', 'columns' => ['created_by', 'updated_by', 'kader_id']],
            ['table' => 'pengukuran_fisiks', 'columns' => ['created_by', 'updated_by', 'kader_id']],
            ['table' => 'pemeriksaans', 'columns' => ['created_by', 'updated_by', 'kader_id']],
            ['table' => 'laporans', 'columns' => ['created_by', 'updated_by', 'kader_id']],
            ['table' => 'laporan_bulanans', 'columns' => ['created_by', 'updated_by', 'kader_id']],
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