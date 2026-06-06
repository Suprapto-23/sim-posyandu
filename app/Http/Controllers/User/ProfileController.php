<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Controllers\User\Concerns\ResolvesUserHealthContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    use ResolvesUserHealthContext;

    public function edit(): View
    {
        $user = Auth::user();
        $user->loadMissing('profile');

        $context = $this->getUserContext($user);

        return view('user.profile.edit', [
            'user' => $user,
            'profile' => $user->profile,
            'context' => $context,
            'profileSummary' => $this->buildProfileSummary($user, $context),
            'connectionCards' => $this->buildConnectionCards($context),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nik' => ['nullable', 'digits:16'],
            'tempat_lahir' => ['nullable', 'string', 'max:100'],
            'tanggal_lahir' => ['nullable', 'date', 'before:today'],
            'jenis_kelamin' => ['nullable', 'in:L,P'],
            'alamat' => ['nullable', 'string', 'max:500'],
            'telepon' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s]+$/'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'nik.digits' => 'NIK harus terdiri dari 16 digit angka.',
            'tanggal_lahir.before' => 'Tanggal lahir harus sebelum hari ini.',
            'jenis_kelamin.in' => 'Jenis kelamin tidak valid.',
            'telepon.regex' => 'Nomor WhatsApp hanya boleh berisi angka, spasi, tanda plus, atau strip.',
        ]);

        try {
            DB::transaction(function () use ($user, $validated) {
                $nik = $this->normalizeNik($validated['nik'] ?? null);

                $userPayload = [
                    'name' => $validated['name'],
                    'updated_at' => now(),
                ];

                if (Schema::hasColumn('users', 'nik')) {
                    $userPayload['nik'] = $nik;
                }

                DB::table('users')
                    ->where('id', $user->id)
                    ->update($userPayload);

                if (Schema::hasTable('profiles')) {
                    DB::table('profiles')->updateOrInsert(
                        ['user_id' => $user->id],
                        $this->profilePayload($validated, $nik, $user->id)
                    );
                }
            });

            return back()->with('success', 'Profil berhasil diperbarui.');
        } catch (\Throwable $e) {
            Log::error('User ProfileController@update error', [
                'message' => $e->getMessage(),
                'user_id' => $user->id,
            ]);

            return back()
                ->withInput()
                ->with('error', 'Profil gagal diperbarui. Periksa kembali data yang diisi.');
        }
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8)->letters()->numbers(),
            ],
        ], [
            'current_password.required' => 'Kata sandi saat ini wajib diisi.',
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
            'password.min' => 'Kata sandi baru minimal 8 karakter.',
            'password.letters' => 'Kata sandi baru harus mengandung huruf.',
            'password.numbers' => 'Kata sandi baru harus mengandung angka.',
        ]);

        $user = Auth::user();

        if (! Hash::check($validated['current_password'], $user->password)) {
            return back()
                ->withErrors(['current_password' => 'Kata sandi saat ini tidak sesuai.'])
                ->with('error', 'Kata sandi saat ini tidak sesuai.');
        }

        if (Hash::check($validated['password'], $user->password)) {
            return back()
                ->withErrors(['password' => 'Kata sandi baru tidak boleh sama dengan kata sandi lama.'])
                ->with('error', 'Gunakan kata sandi baru yang berbeda.');
        }

        try {
            $payload = [
                'password' => Hash::make($validated['password']),
                'updated_at' => now(),
            ];

            if (Schema::hasColumn('users', 'must_change_password')) {
                $payload['must_change_password'] = false;
            }

            DB::table('users')
                ->where('id', $user->id)
                ->update($payload);

            return back()->with('success', 'Kata sandi berhasil diperbarui.');
        } catch (\Throwable $e) {
            Log::error('User ProfileController@updatePassword error', [
                'message' => $e->getMessage(),
                'user_id' => $user->id,
            ]);

            return back()->with('error', 'Kata sandi gagal diperbarui.');
        }
    }

    private function profilePayload(array $validated, ?string $nik, int $userId): array
    {
        $payload = [
            'updated_at' => now(),
        ];

        if (! $this->profileExists($userId)) {
            $payload['created_at'] = now();
        }

        $map = [
            'user_id' => $userId,
            'full_name' => $validated['name'],
            'nik' => $nik,
            'jenis_kelamin' => $validated['jenis_kelamin'] ?? null,
            'tempat_lahir' => $validated['tempat_lahir'] ?? null,
            'tanggal_lahir' => $validated['tanggal_lahir'] ?? null,
            'alamat' => $validated['alamat'] ?? null,
            'telepon' => $validated['telepon'] ?? null,
        ];

        foreach ($map as $column => $value) {
            if (Schema::hasColumn('profiles', $column)) {
                $payload[$column] = $value;
            }
        }

        return $payload;
    }

    private function profileExists(int $userId): bool
    {
        if (! Schema::hasTable('profiles')) {
            return false;
        }

        return DB::table('profiles')
            ->where('user_id', $userId)
            ->exists();
    }

    private function normalizeNik(?string $nik): ?string
    {
        if (blank($nik)) {
            return null;
        }

        $nik = preg_replace('/\D/', '', (string) $nik);

        return $nik ?: null;
    }

    private function buildProfileSummary($user, array $context): array
    {
        $nik = $context['nik'] ?? ($user->nik ?? data_get($user, 'profile.nik'));

        $totalBalita = collect($context['balitas'] ?? [])->count();
        $totalRemaja = collect($context['remajas'] ?? [])->count();
        $totalLansia = collect($context['lansias'] ?? [])->count();

        $totalSasaran = $totalBalita + $totalRemaja + $totalLansia;

        return [
            'nik' => $nik,
            'status_label' => $totalSasaran > 0 ? 'Data Terhubung' : 'Belum Terhubung',
            'status_tone' => $totalSasaran > 0 ? 'emerald' : 'amber',
            'total_sasaran' => $totalSasaran,
            'total_balita' => $totalBalita,
            'total_remaja' => $totalRemaja,
            'total_lansia' => $totalLansia,
            'peran' => collect($context['peran'] ?? ['umum'])
                ->map(fn ($item) => ucwords(str_replace('_', ' ', $item)))
                ->join(', '),
        ];
    }

    private function buildConnectionCards(array $context): array
    {
        return [
            [
                'label' => 'Balita',
                'value' => collect($context['balitas'] ?? [])->count(),
                'caption' => 'Data anak terhubung',
                'tone' => 'rose',
                'icon' => 'fa-child',
            ],
            [
                'label' => 'Remaja',
                'value' => collect($context['remajas'] ?? [])->count(),
                'caption' => 'Data remaja terhubung',
                'tone' => 'sky',
                'icon' => 'fa-user-graduate',
            ],
            [
                'label' => 'Lansia',
                'value' => collect($context['lansias'] ?? [])->count(),
                'caption' => 'Data lansia terhubung',
                'tone' => 'amber',
                'icon' => 'fa-heart-pulse',
            ],
        ];
    }
}