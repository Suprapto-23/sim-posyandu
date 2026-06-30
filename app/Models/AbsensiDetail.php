<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

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
        'pasien_id'  => 'integer',
        'hadir'      => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public const KATEGORI_LABEL = [
        'balita' => 'Balita',
        'remaja' => 'Remaja',
        'lansia' => 'Lansia',
    ];

    // ── RELASI ───────────────────────────────────────────────────────────
    
    public function absensi(): BelongsTo
    {
        return $this->belongsTo(AbsensiPosyandu::class, 'absensi_id');
    }

    public function pasien(): MorphTo
    {
        // Secara otomatis menggunakan MorphMap global di AppServiceProvider
        return $this->morphTo(); 
    }

    // ── MUTATORS & ACCESSORS ─────────────────────────────────────────────

    public function setHadirAttribute($value): void
    {
        $this->attributes['hadir'] = filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    public static function getKategoriLabel(?string $type): string
    {
        $type = strtolower(trim((string) $type));
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
        $pasien = $this->pasien;
        return $pasien?->nama_lengkap ?? $pasien?->nama ?? 'Data sasaran';
    }

    public function getNikPasienAttribute(): string
    {
        return $this->pasien?->nik ?? '-';
    }

    public function getJenisKelaminPasienAttribute(): string
    {
        return $this->pasien?->jenis_kelamin ?? '-';
    }

    public function getTanggalLahirPasienAttribute(): ?string
    {
        $pasien = $this->pasien;
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
        $pasien = $this->pasien;
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
        return $this->pasien?->alamat ?? '-';
    }

    public function getInfoTambahanPasienAttribute(): string
    {
        $pasien = $this->pasien;
        $type   = strtolower(trim((string) $this->pasien_type));

        if (!$pasien) {
            return '-';
        }

        return match ($type) {
            'balita' => $pasien->nama_ibu ?? $pasien->alamat ?? '-',
            'remaja' => $pasien->sekolah ?? $pasien->kelas ?? $pasien->alamat ?? '-',
            'lansia' => $pasien->tingkat_kemandirian ?? $pasien->tekanan_darah ?? $pasien->alamat ?? '-',
            default  => '-',
        };
    }

    public function getKeteranganTextAttribute(): string
    {
        return filled($this->keterangan) ? $this->keterangan : '-';
    }

    // ── SCOPES ───────────────────────────────────────────────────────────

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
        return $query->where('pasien_type', strtolower($kategori));
    }

    public function scopeUntukAbsensi(Builder $query, int $absensiId): Builder
    {
        return $query->where('absensi_id', $absensiId);
    }

    public function scopeUntukPasien(Builder $query, string $kategori, int $pasienId): Builder
    {
        return $query
            ->where('pasien_type', strtolower($kategori))
            ->where('pasien_id', $pasienId);
    }
}