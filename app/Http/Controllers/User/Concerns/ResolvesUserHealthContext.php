<?php

namespace App\Http\Controllers\User\Concerns;

use App\Models\Balita;
use App\Models\JadwalPosyandu;
use App\Models\Lansia;
use App\Models\Pemeriksaan;
use App\Models\Remaja;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

trait ResolvesUserHealthContext
{
    protected function getUserContext(?User $user = null): array
    {
        $user = $user ?: auth()->user();

        if (! $user) {
            return $this->emptyUserContext();
        }

        $nik = $this->resolveUserNik($user);

        $balitas = $this->resolveBalitas($user, $nik);
        $remajas = $this->resolveRemajas($user, $nik);
        $lansias = $this->resolveLansias($user, $nik);

        $remaja = $remajas->first();
        $lansia = $lansias->first();

        $peran = [];

        if ($balitas->isNotEmpty()) {
            $peran[] = 'orang_tua';
        }

        if ($remajas->isNotEmpty()) {
            $peran[] = 'remaja';
        }

        if ($lansias->isNotEmpty()) {
            $peran[] = 'lansia';
        }

        if (empty($peran)) {
            $peran[] = 'umum';
        }

        $targets = $this->buildTargetsFromRoles($peran);

        return [
            'user' => $user,
            'nik' => $nik,
            'balitas' => $balitas,
            'remaja' => $remaja,
            'remajas' => $remajas,
            'lansia' => $lansia,
            'lansias' => $lansias,
            'peran' => $peran,
            'targets' => $targets,
            'hasData' => $balitas->isNotEmpty() || $remajas->isNotEmpty() || $lansias->isNotEmpty(),
            'total_sasaran' => $balitas->count() + $remajas->count() + $lansias->count(),
        ];
    }

    protected function emptyUserContext(): array
    {
        return [
            'user' => null,
            'nik' => null,
            'balitas' => collect(),
            'remaja' => null,
            'remajas' => collect(),
            'lansia' => null,
            'lansias' => collect(),
            'peran' => ['umum'],
            'targets' => ['semua'],
            'hasData' => false,
            'total_sasaran' => 0,
        ];
    }

    protected function resolveUserNik(User $user): ?string
    {
        $candidates = [
            $user->nik ?? null,
            $user->no_ktp ?? null,
            $user->nomor_induk ?? null,
            data_get($user, 'profile.nik'),
            data_get($user, 'warga.nik'),
        ];

        foreach ($candidates as $candidate) {
            if (blank($candidate)) {
                continue;
            }

            $nik = preg_replace('/\D/', '', (string) $candidate);

            if (strlen($nik) === 16) {
                return $nik;
            }
        }

        return null;
    }

    protected function resolveBalitas(User $user, ?string $nik): Collection
    {
        if (! class_exists(Balita::class) || ! Schema::hasTable('balitas')) {
            return collect();
        }

        $query = Balita::query();

        $query->when(
            method_exists(Balita::class, 'user'),
            fn ($q) => $q->with('user')
        );

        $query->where(function (Builder $q) use ($user, $nik) {
            if (Schema::hasColumn('balitas', 'user_id')) {
                $q->where('user_id', $user->id);
            }
            if ($nik && Schema::hasColumn('balitas', 'nik')) {
                $this->safeOrWhere($q, 'nik', $nik);
            }
            if ($nik && Schema::hasColumn('balitas', 'nik_ibu')) {
                $this->safeOrWhere($q, 'nik_ibu', $nik);
            }
            if ($nik && Schema::hasColumn('balitas', 'nik_orangtua')) {
                $this->safeOrWhere($q, 'nik_orangtua', $nik);
            }
        });

        $balitas = $query->orderBy('nama_lengkap')->limit(20)->get();

        // FIX: Tarik data pemeriksaan terakhir secara manual
        $balitas->each(function ($item) {
            $item->setAttribute('pemeriksaan_terakhir', $this->verifiedPemeriksaanQuery('balita', $item->id)->latest()->first());
        });

        return $balitas;
    }

    protected function resolveRemajas(User $user, ?string $nik): Collection
    {
        if (! class_exists(Remaja::class) || ! Schema::hasTable('remajas')) {
            return collect();
        }

        $query = Remaja::query();

        $query->when(
            method_exists(Remaja::class, 'user'),
            fn ($q) => $q->with('user')
        );

        $query->where(function (Builder $q) use ($user, $nik) {
            if (Schema::hasColumn('remajas', 'user_id')) {
                $q->where('user_id', $user->id);
            }
            if ($nik && Schema::hasColumn('remajas', 'nik')) {
                $this->safeOrWhere($q, 'nik', $nik);
            }
        });

        $remajas = $query->orderBy('nama_lengkap')->limit(5)->get();

        // FIX: Tarik data pemeriksaan terakhir secara manual
        $remajas->each(function ($item) {
            $item->setAttribute('pemeriksaan_terakhir', $this->verifiedPemeriksaanQuery('remaja', $item->id)->latest()->first());
        });

        return $remajas;
    }

    protected function resolveLansias(User $user, ?string $nik): Collection
    {
        if (! class_exists(Lansia::class) || ! Schema::hasTable('lansias')) {
            return collect();
        }

        $query = Lansia::query();

        $query->when(
            method_exists(Lansia::class, 'user'),
            fn ($q) => $q->with('user')
        );

        $query->where(function (Builder $q) use ($user, $nik) {
            if (Schema::hasColumn('lansias', 'user_id')) {
                $q->where('user_id', $user->id);
            }
            if ($nik && Schema::hasColumn('lansias', 'nik')) {
                $this->safeOrWhere($q, 'nik', $nik);
            }
        });

        $lansias = $query->orderBy('nama_lengkap')->limit(5)->get();

        // FIX: Tarik data pemeriksaan terakhir secara manual
        $lansias->each(function ($item) {
            $item->setAttribute('pemeriksaan_terakhir', $this->verifiedPemeriksaanQuery('lansia', $item->id)->latest()->first());
        });

        return $lansias;
    }

    protected function safeOrWhere(Builder $query, string $column, string $value): void
    {
        $hasWheres = ! empty($query->getQuery()->wheres);

        if ($hasWheres) {
            $query->orWhere($column, $value);
            return;
        }

        $query->where($column, $value);
    }

    protected function buildTargetsFromRoles(array $peran): array
    {
        $targets = ['semua'];

        if (in_array('orang_tua', $peran, true)) {
            $targets[] = 'balita';
        }

        if (in_array('remaja', $peran, true)) {
            $targets[] = 'remaja';
        }

        if (in_array('lansia', $peran, true)) {
            $targets[] = 'lansia';
        }

        return array_values(array_unique($targets));
    }

    protected function buildUserJadwalQuery(array $targets): Builder
    {
        return JadwalPosyandu::query()
            ->where('status', 'aktif')
            ->whereIn('target_peserta', $targets)
            ->orderBy('tanggal', 'asc')
            ->orderBy('waktu_mulai', 'asc');
    }

    protected function verifiedPemeriksaanQuery(string $kategori, int $pasienId): Builder
    {
        return Pemeriksaan::query()
            ->where('kategori_pasien', $kategori)
            ->where('pasien_id', $pasienId)
            ->whereIn('status_verifikasi', [
                'tervalidasi',
                'verified',
                'approved',
            ]);
    }

    protected function normalizeNumber($value, string $unit = ''): string
    {
        if (blank($value)) {
            return '-';
        }

        $value = rtrim(rtrim((string) $value, '0'), '.');

        return trim($value . ' ' . $unit);
    }

    protected function calculateImt($beratBadan, $tinggiBadan): ?float
    {
        if (blank($beratBadan) || blank($tinggiBadan)) {
            return null;
        }

        $tinggiMeter = ((float) $tinggiBadan) / 100;

        if ($tinggiMeter <= 0) {
            return null;
        }

        return round(((float) $beratBadan) / ($tinggiMeter * $tinggiMeter), 2);
    }

    protected function imtLabel(?float $imt): string
    {
        if (! $imt) {
            return '-';
        }

        return match (true) {
            $imt < 18.5 => 'Kurus',
            $imt < 25 => 'Normal',
            $imt < 30 => 'Berlebih',
            default => 'Obesitas',
        };
    }
}