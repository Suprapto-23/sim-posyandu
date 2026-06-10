<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kunjungan extends Model
{
    use HasFactory;

    protected $fillable = [
        'pasien_id',
        'pasien_type',
        'petugas_id',
        'tanggal_kunjungan',
        'jenis_kunjungan',
        'keluhan',
        'status',
        'catatan',
    ];

    protected $casts = [
        'tanggal_kunjungan' => 'date',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relasi Utama
    |--------------------------------------------------------------------------
    */

    public function pasien()
    {
        return $this->morphTo();
    }

    public function petugas()
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }

    public function pemeriksaan()
    {
        return $this->hasOne(Pemeriksaan::class, 'kunjungan_id');
    }

    public function pemeriksaans()
    {
        return $this->hasMany(Pemeriksaan::class, 'kunjungan_id');
    }

    public function imunisasis()
    {
        return $this->hasMany(Imunisasi::class, 'kunjungan_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessor Petugas
    |--------------------------------------------------------------------------
    */

    public function getNamaPetugasAttribute(): string
    {
        return $this->petugas?->name
            ?? $this->petugas?->nama
            ?? 'Tidak diketahui';
    }

    public function getRolePetugasAttribute(): string
    {
        return match ($this->petugas?->role) {
            'admin' => 'Admin',
            'bidan' => 'Bidan',
            'kader' => 'Kader',
            'user' => 'Warga',
            default => 'Tidak diketahui',
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Accessor Pasien
    |--------------------------------------------------------------------------
    */

    public function getNamaPasienAttribute(): string
    {
        return $this->pasien?->nama_lengkap
            ?? $this->pasien?->nama
            ?? 'Warga Tidak Diketahui';
    }

    public function getNikPasienAttribute(): string
    {
        return $this->pasien?->nik ?? '-';
    }

    public function getKategoriPasienAttribute(): string
    {
        return match ($this->pasien_type) {
            Balita::class => 'balita',
            Remaja::class => 'remaja',
            Lansia::class => 'lansia',
            default => 'tidak_diketahui',
        };
    }

    public function getKategoriPasienLabelAttribute(): string
    {
        return match ($this->kategori_pasien) {
            'balita' => 'Balita',
            'remaja' => 'Remaja',
            'lansia' => 'Lansia',
            default => 'Tidak diketahui',
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Accessor Status
    |--------------------------------------------------------------------------
    */

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'selesai' => 'Selesai',
            'berjalan' => 'Berjalan',
            'dibatalkan' => 'Dibatalkan',
            'menunggu' => 'Menunggu',
            null => 'Menunggu',
            default => ucfirst((string) $this->status),
        };
    }

    public function getJenisKunjunganLabelAttribute(): string
    {
        return match ($this->jenis_kunjungan) {
            'pemeriksaan' => 'Pemeriksaan',
            'imunisasi' => 'Imunisasi',
            'konsultasi' => 'Konsultasi',
            default => ucfirst((string) $this->jenis_kunjungan ?: 'Kunjungan'),
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Scope
    |--------------------------------------------------------------------------
    */

    public function scopePemeriksaan($query)
    {
        return $query->where('jenis_kunjungan', 'pemeriksaan');
    }

    public function scopeImunisasi($query)
    {
        return $query->where('jenis_kunjungan', 'imunisasi');
    }

    public function scopeHariIni($query)
    {
        return $query->whereDate('tanggal_kunjungan', now()->toDateString());
    }

    public function scopeBulanIni($query)
    {
        return $query
            ->whereMonth('tanggal_kunjungan', now()->month)
            ->whereYear('tanggal_kunjungan', now()->year);
    }

    public function scopeKategori($query, string $kategori)
    {
        $model = match ($kategori) {
            'balita' => Balita::class,
            'remaja' => Remaja::class,
            'lansia' => Lansia::class,
            default => null,
        };

        if (!$model) {
            return $query;
        }

        return $query->where('pasien_type', $model);
    }
}