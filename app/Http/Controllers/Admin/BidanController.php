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
        $status = $request->input('status', 'semua');

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
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('profile', function ($p) use ($search) {
                        $p->where('full_name', 'like', "%{$search}%");
                    });
            });
        }

        if (in_array($status, ['active', 'inactive'], true)) {
            $query->where('status', $status);
        }

        $bidans = $query
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();

        // OPTIMASI: 1 Query untuk menghitung semua statistik (Mengikuti gaya UserController)
        $statsData = User::where('role', 'bidan')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN status = "active" THEN 1 ELSE 0 END) as aktif')
            ->selectRaw('SUM(CASE WHEN status = "inactive" THEN 1 ELSE 0 END) as nonaktif')
            ->first();

        $stats = [
            'total' => (int) ($statsData->total ?? 0),
            'aktif' => (int) ($statsData->aktif ?? 0),
            'nonaktif' => (int) ($statsData->nonaktif ?? 0),
        ];

        return view('admin.bidans.index', compact('bidans', 'stats'));
    }

    public function create()
    {
        return view('admin.bidans.create');
    }

    public function store(Request $request)
    {
        // Validasi sama seperti sebelumnya...
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
                ->with('reset_name', $validated['name']);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Admin BidanController store gagal', ['message' => $e->getMessage()]);
            return back()->withInput()->withErrors(['system' => 'Gagal membuat akun bidan.']);
        }
    }

    public function show($id)
    {
        $bidan = User::with(['profile', 'bidan'])->where('role', 'bidan')->findOrFail($id);
        return view('admin.bidans.show', compact('bidan'));
    }

    public function edit($id)
    {
        $bidan = User::with(['profile', 'bidan'])->where('role', 'bidan')->findOrFail($id);
        return view('admin.bidans.edit', compact('bidan'));
    }

    public function update(Request $request, $id)
    {
        // Logika update dipertahankan sama persis...
        $bidan = User::with(['profile', 'bidan'])->where('role', 'bidan')->findOrFail($id);

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
                $bidan->profile()->create(array_merge($profileData, ['user_id' => $bidan->id, 'nik' => $bidan->nik]));
            }

            $this->saveBidanDetail($bidan->id, $validated);
            DB::commit();

            return redirect()->route('admin.bidans.show', $id)->with('success', 'Data bidan berhasil diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Admin BidanController update gagal', ['message' => $e->getMessage()]);
            return back()->withInput()->withErrors(['system' => 'Gagal memperbarui data bidan.']);
        }
    }

    // FITUR BARU: Menambahkan fungsi Toggle Status untuk mengatasi error route
    public function toggleStatus($id)
    {
        $bidan = User::where('role', 'bidan')->findOrFail($id);
        $newStatus = $bidan->status === 'active' ? 'inactive' : 'active';
        $bidan->update(['status' => $newStatus]);
        $statusText = $newStatus === 'active' ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Status akun {$bidan->name} berhasil {$statusText}.");
    }

    public function destroy($id)
    {
        $bidan = User::with(['profile', 'bidan'])->where('role', 'bidan')->findOrFail($id);
        $name = $bidan->profile?->full_name ?? $bidan->name;

        if ($this->hasOperationalHistory($bidan->id)) {
            return back()->with('warning', "Akun bidan {$name} tidak dihapus karena memiliki riwayat operasional (Pemeriksaan/Imunisasi). Nonaktifkan akun saja.");
        }

        DB::beginTransaction();
        try {
            if (Schema::hasTable('bidans')) DB::table('bidans')->where('user_id', $bidan->id)->delete();
            if ($bidan->profile) $bidan->profile->delete();
            
            $bidan->delete();
            DB::commit();

            return redirect()->route('admin.bidans.index')->with('success', "Akun bidan {$name} berhasil dihapus.");
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Admin BidanController destroy gagal', ['message' => $e->getMessage()]);
            return back()->withErrors(['system' => 'Gagal menghapus data bidan.']);
        }
    }

    public function resetPassword($id)
    {
        $bidan = User::with('profile')->where('role', 'bidan')->findOrFail($id);
        $password = $this->makePassword();

        $updateData = ['password' => Hash::make($password)];
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
            ->with('reset_name', $name);
    }

    private function saveBidanDetail(int $userId, array $data): void
    {
        // Dipertahankan karena ini logikanya sudah benar
        if (!Schema::hasTable('bidans')) return;

        $payload = [];
        if (Schema::hasColumn('bidans', 'jabatan')) $payload['jabatan'] = $data['jabatan'] ?? 'Bidan Desa';
        if (Schema::hasColumn('bidans', 'no_str')) $payload['no_str'] = $data['no_str'] ?? null;
        if (Schema::hasColumn('bidans', 'no_sip')) $payload['no_sip'] = $data['no_sip'] ?? null;
        if (Schema::hasColumn('bidans', 'lokasi_praktik')) $payload['lokasi_praktik'] = $data['lokasi_praktik'] ?? null;
        if (Schema::hasColumn('bidans', 'updated_at')) $payload['updated_at'] = now();

        $exists = DB::table('bidans')->where('user_id', $userId)->exists();
        if (!$exists && Schema::hasColumn('bidans', 'created_at')) $payload['created_at'] = now();

        DB::table('bidans')->updateOrInsert(['user_id' => $userId], $payload);
    }

    private function hasOperationalHistory(int $userId): bool
    {
        // Dipertahankan karena untuk rekam medis Bidan tidak boleh dihapus jika ada history
        $checks = [
            ['table' => 'pemeriksaans', 'columns' => ['bidan_id', 'created_by', 'updated_by']],
            ['table' => 'imunisasis', 'columns' => ['bidan_id', 'petugas_id', 'created_by', 'updated_by']],
            ['table' => 'jadwals', 'columns' => ['bidan_id', 'created_by', 'updated_by']],
            ['table' => 'rekam_medis', 'columns' => ['bidan_id', 'created_by', 'updated_by']],
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
        // Menggunakan library random yang lebih ringkas dan kuat seperti di UserController
        return \Illuminate\Support\Str::random(10);
    }
}