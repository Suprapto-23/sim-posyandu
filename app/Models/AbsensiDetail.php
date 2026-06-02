<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Relation;

class AbsensiDetail extends Model
{
    protected $table = 'absensi_detail';

    protected $fillable = [
        'absensi_id',
        'pasien_id',
        'pasien_type',
        'hadir',
        'keterangan',
    ];

    protected $casts = [
        'absensi_id' => 'integer',
        'pasien_id' => 'integer',
        'hadir' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public const PASIEN_TYPES = [
        'balita' => Balita::class,
        'remaja' => Remaja::class,
        'lansia' => Lansia::class,
    ];

    public const KATEGORI_LABEL = [
        'balita' => 'Balita',
        'remaja' => 'Remaja',
        'lansia' => 'Lansia',
    ];

    protected static function booted(): void
    {
        Relation::morphMap(self::PASIEN_TYPES, false);

        static::saving(function (self $detail) {
            $detail->pasien_type = self::normalizePasienType($detail->pasien_type);
            $detail->hadir = (bool) $detail->hadir;
        });
    }

    public function absensi(): BelongsTo
    {
        return $this->belongsTo(AbsensiPosyandu::class, 'absensi_id');
    }

    public function pasien(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'pasien_type', 'pasien_id');
    }

    public function setPasienTypeAttribute($value): void
    {
        $this->attributes['pasien_type'] = self::normalizePasienType($value);
    }

    public function setHadirAttribute($value): void
    {
        $this->attributes['hadir'] = filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    public static function normalizePasienType(?string $type): string
    {
        $raw = trim((string) $type);

        if ($raw === Balita::class) {
            return 'balita';
        }

        if ($raw === Remaja::class) {
            return 'remaja';
        }

        if ($raw === Lansia::class) {
            return 'lansia';
        }

        $value = strtolower($raw);
        $value = str_replace(['_', '-', ' ', '\\'], '', $value);

        return match ($value) {
            'balita', 'balitas', 'appmodelsbalita', 'anak' => 'balita',
            'remaja', 'remajas', 'appmodelsremaja' => 'remaja',
            'lansia', 'lansias', 'appmodelslansia' => 'lansia',
            default => 'balita',
        };
    }

    public static function getModelClass(?string $type): ?string
    {
        $type = self::normalizePasienType($type);

        return self::PASIEN_TYPES[$type] ?? null;
    }

    public static function getKategoriLabel(?string $type): string
    {
        $type = self::normalizePasienType($type);

        return self::KATEGORI_LABEL[$type] ?? 'Tidak Dikenal';
    }

    public function getKategoriLabelAttribute(): string
    {
        return self::getKategoriLabel($this->pasien_type);
    }

    public function getStatusTextAttribute(): string
    {
        return $this->hadir ? 'Hadir' : 'Tidak Hadir';
    }

    public function getStatusBadgeAttribute(): string
    {
        return $this->hadir ? 'emerald' : 'orange';
    }

    public function getStatusIconAttribute(): string
    {
        return $this->hadir ? 'fa-circle-check' : 'fa-circle-xmark';
    }

    public function getNamaPasienAttribute(): string
    {
        $pasien = $this->getPasienModel();

        return $pasien?->nama_lengkap
            ?? $pasien?->nama
            ?? 'Data sasaran';
    }

    public function getNikPasienAttribute(): string
    {
        $pasien = $this->getPasienModel();

        return $pasien?->nik
            ?? '-';
    }

    public function getJenisKelaminPasienAttribute(): string
    {
        $pasien = $this->getPasienModel();

        return $pasien?->jenis_kelamin
            ?? '-';
    }

    public function getTanggalLahirPasienAttribute(): ?string
    {
        $pasien = $this->getPasienModel();

        if (!$pasien || empty($pasien->tanggal_lahir)) {
            return null;
        }

        try {
            return Carbon::parse($pasien->tanggal_lahir)->translatedFormat('d F Y');
        } catch (\Throwable) {
            return null;
        }
    }

    public function getUsiaPasienAttribute(): string
    {
        $pasien = $this->getPasienModel();

        if (!$pasien || empty($pasien->tanggal_lahir)) {
            return '-';
        }

        try {
            return Carbon::parse($pasien->tanggal_lahir)->age . ' tahun';
        } catch (\Throwable) {
            return '-';
        }
    }

    public function getAlamatPasienAttribute(): string
    {
        $pasien = $this->getPasienModel();

        return $pasien?->alamat
            ?? '-';
    }

    public function getInfoTambahanPasienAttribute(): string
    {
        $pasien = $this->getPasienModel();
        $type = self::normalizePasienType($this->pasien_type);

        if (!$pasien) {
            return '-';
        }

        return match ($type) {
            'balita' => $pasien->nama_ibu
                ?? $pasien->alamat
                ?? '-',

            'remaja' => $pasien->sekolah
                ?? $pasien->kelas
                ?? $pasien->alamat
                ?? '-',

            'lansia' => $pasien->tingkat_kemandirian
                ?? $pasien->tekanan_darah
                ?? $pasien->alamat
                ?? '-',

            default => '-',
        };
    }

    public function getKeteranganTextAttribute(): string
    {
        return filled($this->keterangan)
            ? $this->keterangan
            : '-';
    }

    public function scopeHadir(Builder $query): Builder
    {
        return $query->where('hadir', true);
    }

    public function scopeTidakHadir(Builder $query): Builder
    {
        return $query->where('hadir', false);
    }

    public function scopeKategori(Builder $query, string $kategori): Builder
    {
        return $query->where('pasien_type', self::normalizePasienType($kategori));
    }

    public function scopeUntukAbsensi(Builder $query, int $absensiId): Builder
    {
        return $query->where('absensi_id', $absensiId);
    }

    public function scopeUntukPasien(Builder $query, string $kategori, int $pasienId): Builder
    {
        return $query
            ->where('pasien_type', self::normalizePasienType($kategori))
            ->where('pasien_id', $pasienId);
    }

    private function getPasienModel(): ?Model
    {
        if ($this->relationLoaded('pasien')) {
            $pasien = $this->getRelation('pasien');

            return $pasien instanceof Model ? $pasien : null;
        }

        if (!$this->pasien_id || !$this->pasien_type) {
            return null;
        }

        try {
            $modelClass = self::getModelClass($this->pasien_type);

            if (!$modelClass || !class_exists($modelClass)) {
                return null;
            }

            return $modelClass::query()->find($this->pasien_id);
        } catch (\Throwable) {
            return null;
        }
    }
}