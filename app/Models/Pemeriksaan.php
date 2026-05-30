<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pemeriksaan extends Model
{
    use HasFactory;

    protected $table = 'pemeriksaans';

    protected $guarded = ['id'];

    protected $appends = [
        'nama_pasien',
        'nik_pasien',
        'status_verifikasi_text',
        'status_verifikasi_badge',
    ];

    protected $casts = [
        'tanggal_periksa' => 'date',
        'verified_at' => 'datetime',
        'berat_badan' => 'float',
        'tinggi_badan' => 'float',
        'suhu_tubuh' => 'float',
        'lingkar_kepala' => 'float',
        'lingkar_lengan' => 'float',
        'lingkar_perut' => 'float',
        'imt' => 'float',
        'gula_darah' => 'float',
        'kolesterol' => 'float',
        'asam_urat' => 'float',
    ];

    public function kunjungan()
    {
        return $this->belongsTo(Kunjungan::class, 'kunjungan_id');
    }

    public function pemeriksa()
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

    public function balita()
    {
        return $this->belongsTo(Balita::class, 'balita_id');
    }

    public function remaja()
    {
        return $this->belongsTo(Remaja::class, 'remaja_id');
    }

    public function lansia()
    {
        return $this->belongsTo(Lansia::class, 'lansia_id');
    }

    public function getNamaPasienAttribute(): string
    {
        $pasienPolymorphic = $this->kunjungan?->pasien;

        if ($pasienPolymorphic) {
            return $pasienPolymorphic->nama_lengkap ?? 'Pasien Tidak Diketahui';
        }

        if ($this->balita) {
            return $this->balita->nama_lengkap ?? 'Balita Tidak Diketahui';
        }

        if ($this->remaja) {
            return $this->remaja->nama_lengkap ?? 'Remaja Tidak Diketahui';
        }

        if ($this->lansia) {
            return $this->lansia->nama_lengkap ?? 'Lansia Tidak Diketahui';
        }

        return 'Warga Tidak Diketahui';
    }

    public function getNikPasienAttribute(): string
    {
        $pasienPolymorphic = $this->kunjungan?->pasien;

        if ($pasienPolymorphic) {
            return $pasienPolymorphic->nik ?? '-';
        }

        if ($this->balita) {
            return $this->balita->nik ?? '-';
        }

        if ($this->remaja) {
            return $this->remaja->nik ?? '-';
        }

        if ($this->lansia) {
            return $this->lansia->nik ?? '-';
        }

        return '-';
    }

    public function getStatusVerifikasiTextAttribute(): string
    {
        return match ($this->status_verifikasi) {
            'tervalidasi', 'verified', 'approved' => 'Tervalidasi Bidan',
            'ditolak', 'rejected' => 'Revisi / Ditolak',
            default => 'Menunggu Validasi',
        };
    }

    public function getStatusVerifikasiBadgeAttribute(): string
    {
        return match ($this->status_verifikasi) {
            'tervalidasi', 'verified', 'approved' => 'emerald',
            'ditolak', 'rejected' => 'rose',
            default => 'amber',
        };
    }

    public function scopePending($query)
    {
        return $query->where(function ($q) {
            $q->where('status_verifikasi', 'pending')
                ->orWhereNull('status_verifikasi');
        });
    }

    public function scopeVerified($query)
    {
        return $query->whereIn('status_verifikasi', [
            'tervalidasi',
            'verified',
            'approved',
        ]);
    }

    public function scopeDitolak($query)
    {
        return $query->whereIn('status_verifikasi', [
            'ditolak',
            'rejected',
        ]);
    }

    public function scopeKategori($query, $kategori)
    {
        return $query->where('kategori_pasien', $kategori);
    }

    public function scopeBulanIni($query)
    {
        return $query->whereMonth('tanggal_periksa', Carbon::now()->month)
            ->whereYear('tanggal_periksa', Carbon::now()->year);
    }
}