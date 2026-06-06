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

class BidanController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 12);
        $perPage = max(5, min($perPage, 25));

        $search = trim((string) $request->input('search', ''));

        $query = User::query()
            ->select('id', 'name', 'email', 'nik', 'role', 'status', 'created_at')
            ->with([
                'profile:id,user_id,full_name,nik,jenis_kelamin,tempat_lahir,tanggal_lahir,alamat,telepon',
                'bidan:id,user_id,jabatan,no_str,no_sip,lokasi_praktik',
            ])
            ->where('role', 'bidan');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                if (preg_match('/^[0-9]+$/', $search)) {
                    $q->where('nik', 'like', "{$search}%")
                        ->orWhereHas('profile', function ($p) use ($search) {
                            $p->where('nik', 'like', "{$search}%")
                                ->orWhere('telepon', 'like', "{$search}%");
                        });

                    return;
                }

                $q->where('name', 'like', "{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('profile', function ($p) use ($search) {
                        $p->where('full_name', 'like', "{$search}%")
                            ->orWhere('full_name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status') && in_array($request->status, ['active', 'inactive'], true)) {
            $query->where('status', $request->status);
        }

        $bidans = $query
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();

        $stats = [
            'total' => User::where('role', 'bidan')->count(),
            'aktif' => User::where('role', 'bidan')->where('status', 'active')->count(),
            'nonaktif' => User::where('role', 'bidan')->where('status', 'inactive')->count(),
        ];

        return view('admin.bidans.index', compact('bidans', 'stats'));
    }

    public function create()
    {
        return view('admin.bidans.create');
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
            'no_str' => ['nullable', 'string', 'max:191'],
            'no_sip' => ['nullable', 'string', 'max:191'],
            'lokasi_praktik' => ['nullable', 'string', 'max:191'],
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
                'role' => 'bidan',
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

            $this->saveBidanDetail($user->id, $validated);

            DB::commit();

            return redirect()
                ->route('admin.bidans.index')
                ->with('success', 'Akun bidan berhasil dibuat.')
                ->with('generated_password', $password)
                ->with('reset_password', $password)
                ->with('user_name', $validated['name'])
                ->with('reset_name', $validated['name'])
                ->with('user_email', $validated['email'])
                ->with('reset_email', $validated['email']);
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Admin BidanController store gagal', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return back()
                ->withInput()
                ->withErrors([
                    'system' => 'Gagal membuat akun bidan. Cek struktur database dan log Laravel.',
                ]);
        }
    }

    public function show($id)
    {
        $bidan = User::with(['profile', 'bidan'])
            ->where('role', 'bidan')
            ->findOrFail($id);

        return view('admin.bidans.show', compact('bidan'));
    }

    public function edit($id)
    {
        $bidan = User::with(['profile', 'bidan'])
            ->where('role', 'bidan')
            ->findOrFail($id);

        return view('admin.bidans.edit', compact('bidan'));
    }

    public function update(Request $request, $id)
    {
        $bidan = User::with(['profile', 'bidan'])
            ->where('role', 'bidan')
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
            'no_str' => ['nullable', 'string', 'max:191'],
            'no_sip' => ['nullable', 'string', 'max:191'],
            'lokasi_praktik' => ['nullable', 'string', 'max:191'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
            'tanggal_lahir.required' => 'Tanggal lahir wajib diisi.',
        ]);

        DB::beginTransaction();

        try {
            $bidan->update([
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

            if ($bidan->profile) {
                $bidan->profile->update($profileData);
            } else {
                $bidan->profile()->create(array_merge($profileData, [
                    'user_id' => $bidan->id,
                    'nik' => $bidan->nik,
                ]));
            }

            $this->saveBidanDetail($bidan->id, $validated);

            DB::commit();

            return redirect()
                ->route('admin.bidans.show', $id)
                ->with('success', 'Data bidan berhasil diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Admin BidanController update gagal', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return back()
                ->withInput()
                ->withErrors([
                    'system' => 'Gagal memperbarui data bidan.',
                ]);
        }
    }

    public function destroy($id)
    {
        $bidan = User::with(['profile', 'bidan'])
            ->where('role', 'bidan')
            ->findOrFail($id);

        $name = $bidan->profile?->full_name ?? $bidan->name;

        if ($this->hasOperationalHistory($bidan->id)) {
            return back()
                ->with('warning', "Akun bidan {$name} tidak dihapus karena sudah memiliki riwayat operasional. Nonaktifkan akun jika bidan tidak lagi bertugas.");
        }

        DB::beginTransaction();

        try {
            if (Schema::hasTable('bidans')) {
                DB::table('bidans')->where('user_id', $bidan->id)->delete();
            }

            if ($bidan->profile) {
                $bidan->profile->delete();
            }

            $bidan->delete();

            DB::commit();

            return redirect()
                ->route('admin.bidans.index')
                ->with('success', "Akun bidan {$name} berhasil dihapus.");
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Admin BidanController destroy gagal', [
                'message' => $e->getMessage(),
                'user_id' => $bidan->id,
            ]);

            return back()
                ->withErrors([
                    'system' => 'Gagal menghapus data bidan.',
                ]);
        }
    }

    public function resetPassword($id)
    {
        $bidan = User::with('profile')
            ->where('role', 'bidan')
            ->findOrFail($id);

        $password = $this->makePassword();

        $updateData = [
            'password' => Hash::make($password),
        ];

        if (Schema::hasColumn('users', 'must_change_password')) {
            $updateData['must_change_password'] = true;
        }

        $bidan->update($updateData);

        $name = $bidan->profile?->full_name ?? $bidan->name;

        return redirect()
            ->route('admin.bidans.index')
            ->with('success', 'Password baru bidan berhasil dibuat.')
            ->with('generated_password', $password)
            ->with('reset_password', $password)
            ->with('user_name', $name)
            ->with('reset_name', $name)
            ->with('user_email', $bidan->email)
            ->with('reset_email', $bidan->email);
    }

    private function saveBidanDetail(int $userId, array $data): void
    {
        if (! Schema::hasTable('bidans')) {
            return;
        }

        $payload = [];

        if (Schema::hasColumn('bidans', 'jabatan')) {
            $payload['jabatan'] = $data['jabatan'] ?? 'Bidan Desa';
        }

        if (Schema::hasColumn('bidans', 'no_str')) {
            $payload['no_str'] = $data['no_str'] ?? null;
        }

        if (Schema::hasColumn('bidans', 'no_sip')) {
            $payload['no_sip'] = $data['no_sip'] ?? null;
        }

        if (Schema::hasColumn('bidans', 'lokasi_praktik')) {
            $payload['lokasi_praktik'] = $data['lokasi_praktik'] ?? null;
        }

        if (Schema::hasColumn('bidans', 'updated_at')) {
            $payload['updated_at'] = now();
        }

        $exists = DB::table('bidans')
            ->where('user_id', $userId)
            ->exists();

        if (! $exists && Schema::hasColumn('bidans', 'created_at')) {
            $payload['created_at'] = now();
        }

        DB::table('bidans')->updateOrInsert(
            ['user_id' => $userId],
            $payload
        );
    }

    private function hasOperationalHistory(int $userId): bool
    {
        $checks = [
            ['table' => 'pemeriksaans', 'columns' => ['bidan_id', 'created_by', 'updated_by']],
            ['table' => 'imunisasis', 'columns' => ['bidan_id', 'petugas_id', 'created_by', 'updated_by']],
            ['table' => 'jadwals', 'columns' => ['bidan_id', 'created_by', 'updated_by']],
            ['table' => 'rekam_medis', 'columns' => ['bidan_id', 'created_by', 'updated_by']],
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

                $exists = DB::table($check['table'])
                    ->where($column, $userId)
                    ->exists();

                if ($exists) {
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