<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Relation;

class AbsensiDetail extends Model
{
    use HasFactory;

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

    protected static function booted(): void
    {
        Relation::morphMap(self::PASIEN_TYPES);

        static::saving(function (self $detail) {
            $detail->pasien_type = self::normalizePasienType($detail->pasien_type);
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

    public static function normalizePasienType(?string $type): string
    {
        $value = trim((string) $type);

        if ($value === '') {
            return 'balita';
        }

        $base = strtolower(str_replace(['_', '-', ' '], '', class_basename($value)));

        return match ($base) {
            'balita', 'balitas', 'anak' => 'balita',
            'remaja', 'remajas' => 'remaja',
            'lansia', 'lansias' => 'lansia',
            default => $value,
        };
    }

    public function getPasienModelClassAttribute(): ?string
    {
        $type = self::normalizePasienType($this->pasien_type);

        if (isset(self::PASIEN_TYPES[$type])) {
            return self::PASIEN_TYPES[$type];
        }

        return class_exists($this->pasien_type) ? $this->pasien_type : null;
    }

    public function getPasienDataAttribute(): ?Model
    {
        if ($this->relationLoaded('pasien')) {
            return $this->getRelation('pasien');
        }

        $modelClass = $this->pasien_model_class;

        if (!$modelClass || !$this->pasien_id) {
            return null;
        }

        return $modelClass::find($this->pasien_id);
    }

    public function getNamaPasienAttribute(): string
    {
        $pasien = $this->pasien_data;

        return $pasien?->nama_lengkap
            ?? $pasien?->nama
            ?? $pasien?->name
            ?? 'Data tidak ditemukan';
    }

    public function getNikPasienAttribute(): string
    {
        $pasien = $this->pasien_data;

        return $pasien?->nik ?? '-';
    }

    public function getKategoriPasienAttribute(): string
    {
        return match (self::normalizePasienType($this->pasien_type)) {
            'balita' => 'Balita / Anak',
            'remaja' => 'Remaja',
            'lansia' => 'Lansia',
            default => 'Tidak Dikenal',
        };
    }

    public function getStatusTextAttribute(): string
    {
        return $this->hadir ? 'Hadir' : 'Tidak Hadir';
    }

    public function getStatusBadgeAttribute(): string
    {
        return $this->hadir ? 'emerald' : 'rose';
    }

    public function getStatusClassAttribute(): string
    {
        return $this->hadir
            ? 'bg-emerald-50 text-emerald-700 border-emerald-100'
            : 'bg-rose-50 text-rose-700 border-rose-100';
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

    public function scopeTanggal(Builder $query, string $tanggal): Builder
    {
        return $query->whereHas('absensi', function ($q) use ($tanggal) {
            $q->whereDate('tanggal_posyandu', $tanggal);
        });
    }

    public function scopeBulan(Builder $query, int $bulan, ?int $tahun = null): Builder
    {
        return $query->whereHas('absensi', function ($q) use ($bulan, $tahun) {
            $q->where('bulan', $bulan);

            if ($tahun) {
                $q->where('tahun', $tahun);
            }
        });
    }

    public function scopePeriode(Builder $query, int $bulan, int $tahun): Builder
    {
        return $query->whereHas('absensi', function ($q) use ($bulan, $tahun) {
            $q->where('bulan', $bulan)
                ->where('tahun', $tahun);
        });
    }

    public function scopeUntukPasien(Builder $query, int $pasienId, string $pasienType): Builder
    {
        return $query->where('pasien_id', $pasienId)
            ->where('pasien_type', self::normalizePasienType($pasienType));
    }

    public function tandaiHadir(?string $keterangan = null): bool
    {
        $this->hadir = true;
        $this->keterangan = $keterangan;

        return $this->save();
    }

    public function tandaiTidakHadir(?string $keterangan = null): bool
    {
        $this->hadir = false;
        $this->keterangan = $keterangan;

        return $this->save();
    }
}