<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

class Pemeriksaan extends Model
{
    use HasFactory;

    protected $fillable = [
        'kunjungan_id',
        'pasien_id',
        'kategori_pasien',
        'tanggal_periksa',

        'pemeriksa_id',
        

        'berat_badan',
        'tinggi_badan',
        'imt',
        'lingkar_kepala',
        'lingkar_lengan',
        'lingkar_perut',

        'suhu_tubuh',
        'tekanan_darah',
        'denyut_nadi',
        'respirasi',

        'gula_darah',
        'kolesterol',
        'asam_urat',
        'hemoglobin',

        'tingkat_kemandirian',
        'keluhan',
        'diagnosa',
        'tindakan',
        'catatan',
        'catatan_kader',
        'catatan_bidan',
        'rekomendasi',

        'status_verifikasi',
        'verified_by',
    ];

    protected $casts = [
        'tanggal_periksa' => 'date',
        'verified_at' => 'datetime',

        'berat_badan' => 'float',
        'tinggi_badan' => 'float',
        'imt' => 'float',
        'lingkar_kepala' => 'float',
        'lingkar_lengan' => 'float',
        'lingkar_perut' => 'float',

        'suhu_tubuh' => 'float',
        'denyut_nadi' => 'integer',
        'respirasi' => 'integer',

        'gula_darah' => 'float',
        'kolesterol' => 'float',
        'asam_urat' => 'float',
        'hemoglobin' => 'float',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relasi Utama
    |--------------------------------------------------------------------------
    */

    public function kunjungan()
    {
        return $this->belongsTo(Kunjungan::class, 'kunjungan_id');
    }

    public function pemeriksa()
    {
        return $this->belongsTo(User::class, 'pemeriksa_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function verifikator()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function verifikatorLegacy()
    {
        return $this->belongsTo(User::class, 'user_id_verifikator');
    }

    /*
    |--------------------------------------------------------------------------
    | Relasi Sasaran
    |--------------------------------------------------------------------------
    | Struktur final memakai pasien_id + kategori_pasien.
    | Relasi ini dipakai sebagai fallback jika kunjungan.pasien belum tersedia.
    */

    public function balita()
    {
        return $this->belongsTo(Balita::class, 'pasien_id');
    }

    public function remaja()
    {
        return $this->belongsTo(Remaja::class, 'pasien_id');
    }

    public function lansia()
    {
        return $this->belongsTo(Lansia::class, 'pasien_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessor Data Pasien
    |--------------------------------------------------------------------------
    */

    public function getPasienDataAttribute()
    {
        if ($this->kunjungan?->pasien) {
            return $this->kunjungan->pasien;
        }

        return match ($this->kategori_pasien) {
            'balita' => $this->balita,
            'remaja' => $this->remaja,
            'lansia' => $this->lansia,
            default => null,
        };
    }

    public function getNamaPasienAttribute(): string
    {
        return $this->pasien_data?->nama_lengkap
            ?? $this->pasien_data?->nama
            ?? 'Warga Tidak Diketahui';
    }

    public function getNikPasienAttribute(): string
    {
        return $this->pasien_data?->nik ?? '-';
    }

    public function getJenisKelaminPasienAttribute(): string
    {
        return $this->pasien_data?->jenis_kelamin ?? '-';
    }

    public function getTanggalLahirPasienAttribute()
    {
        return $this->pasien_data?->tanggal_lahir;
    }

    public function getNamaPemeriksaAttribute(): string
    {
        return $this->pemeriksa?->name
            ?? $this->pemeriksa?->nama
            ?? $this->creator?->name
            ?? '-';
    }

    /*
    |--------------------------------------------------------------------------
    | Accessor Status
    |--------------------------------------------------------------------------
    */

    public function getStatusVerifikasiTextAttribute(): string
    {
        return match ($this->normalized_status) {
            'verified' => 'Tervalidasi Bidan',
            'revision' => 'Perlu Revisi',
            default => 'Menunggu Validasi',
        };
    }

    public function getStatusVerifikasiBadgeAttribute(): string
    {
        return match ($this->normalized_status) {
            'verified' => 'emerald',
            'revision' => 'rose',
            default => 'amber',
        };
    }

    public function getNormalizedStatusAttribute(): string
    {
        $status = strtolower($this->status_verifikasi ?? 'pending');

        return match (true) {
            in_array($status, ['verified', 'tervalidasi', 'approved', 'disetujui'], true) => 'verified',
            in_array($status, ['ditolak', 'rejected', 'revisi', 'perlu_revisi', 'dikembalikan'], true) => 'revision',
            default => 'pending',
        };
    }

    public function getIsVerifiedAttribute(): bool
    {
        return $this->normalized_status === 'verified';
    }

    public function getNeedsRevisionAttribute(): bool
    {
        return $this->normalized_status === 'revision';
    }

    public function getIsPendingAttribute(): bool
    {
        return $this->normalized_status === 'pending';
    }

    /*
    |--------------------------------------------------------------------------
    | Scope
    |--------------------------------------------------------------------------
    */

    public function scopePending($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('status_verifikasi')
                ->orWhereIn('status_verifikasi', [
                    'pending',
                    'menunggu',
                    'menunggu_review',
                ]);
        });
    }

    public function scopeVerified($query)
    {
        return $query->whereIn('status_verifikasi', [
            'verified',
            'tervalidasi',
            'approved',
            'disetujui',
        ]);
    }

    public function scopeDitolak($query)
    {
        return $query->whereIn('status_verifikasi', [
            'ditolak',
            'rejected',
            'revisi',
            'perlu_revisi',
            'dikembalikan',
        ]);
    }

    public function scopeKategori($query, ?string $kategori)
    {
        if (!$kategori) {
            return $query;
        }

        return $query->where('kategori_pasien', $kategori);
    }

    public function scopeBulanIni($query)
    {
        return $query
            ->whereMonth('tanggal_periksa', Carbon::now()->month)
            ->whereYear('tanggal_periksa', Carbon::now()->year);
    }

    public function scopeTahunIni($query)
    {
        return $query->whereYear('tanggal_periksa', Carbon::now()->year);
    }
}