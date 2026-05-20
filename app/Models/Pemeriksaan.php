<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * =========================================================================
 * PEMERIKSAAN MODEL (NEXUS ULTIMATE EDITION)
 * =========================================================================
 * Menghubungkan log kunjungan, petugas, bidan, dan data antropometri fisik.
 * Dilengkapi dengan sistem "Backward Compatibility" anti-crash.
 */
class Pemeriksaan extends Model
{
    use HasFactory;

    protected $table = 'pemeriksaans';
    protected $guarded = ['id'];

    // Atribut virtual ini akan selalu dikirim bersama response JSON/View
    protected $appends = [
        'nama_pasien',
        'nik_pasien',
        'status_verifikasi_text',
        'status_verifikasi_badge'
    ];

    protected $casts = [
        'tanggal_periksa' => 'date',
        'verified_at'     => 'datetime',
        'berat_badan'     => 'float',
        'tinggi_badan'    => 'float',
        'suhu_tubuh'      => 'float',
        'lingkar_kepala'  => 'float',
        'lingkar_lengan'  => 'float',
        'lingkar_perut'   => 'float',
        'imt'             => 'float',
        'gula_darah'      => 'float',
        'kolesterol'      => 'integer',
        'asam_urat'       => 'float',
        'usia_kehamilan'  => 'integer',
    ];

    /**
     * =================================================================
     * 1. RELASI ARSITEKTUR BARU (NEXUS ENGINE)
     * =================================================================
     */
    
    // Pintu gerbang utama ke data Pasien (Polymorphic)
    public function kunjungan()
    {
        return $this->belongsTo(Kunjungan::class, 'kunjungan_id');
    }

    // Menggunakan nama 'pemeriksa' agar 100% kompatibel dengan kode lama kader
    public function pemeriksa()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function verifikator()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * =================================================================
     * 2. RELASI ANTI-CRASH (BACKWARD COMPATIBILITY)
     * =================================================================
     * Mencegah Error 500 "Call to undefined relationship [balita]" 
     * jika ada modul/view lama yang belum sempat di-update.
     */
    public function balita()   { return $this->belongsTo(Balita::class, 'balita_id'); }
    public function remaja()   { return $this->belongsTo(Remaja::class, 'remaja_id'); }
    public function lansia()   { return $this->belongsTo(Lansia::class, 'lansia_id'); }


    /**
     * =================================================================
     * 3. ACCESSORS (VIRTUAL COLUMNS CERDAS)
     * =================================================================
     */

    /**
     * Mengambil Nama Pasien secara aman.
     * Cek dari arsitektur baru, jika gagal, cek dari arsitektur lama.
     */
    public function getNamaPasienAttribute(): string
    {
        // Prioritas 1: Ekosistem Baru (Melalui tabel kunjungan)
        if ($this->kunjungan && $this->kunjungan->pasien) {
            return $this->kunjungan->pasien->nama_lengkap ?? 'Pasien Tidak Diketahui';
        }

        // Prioritas 2: Ekosistem Lama (Langsung dari tabel terkait)
        if ($this->balita) return $this->balita->nama_lengkap;
        if ($this->remaja) return $this->remaja->nama_lengkap;
        if ($this->lansia) return $this->lansia->nama_lengkap;
        if ($this->ibuHamil) return $this->ibuHamil->nama_lengkap;

        return 'Warga Tidak Diketahui (Data Terhapus)';
    }

    /**
     * Mengambil NIK Pasien secara aman.
     */
    public function getNikPasienAttribute(): string
    {
        if ($this->kunjungan && $this->kunjungan->pasien) {
            return $this->kunjungan->pasien->nik ?? '-';
        }

        if ($this->balita) return $this->balita->nik ?? '-';
        if ($this->remaja) return $this->remaja->nik ?? '-';
        if ($this->lansia) return $this->lansia->nik ?? '-';
        if ($this->ibuHamil) return $this->ibuHamil->nik ?? '-';

        return '-';
    }

    public function getStatusVerifikasiTextAttribute(): string
    {
        return match($this->status_verifikasi) {
            'tervalidasi', 'verified', 'approved' => 'Tervalidasi Bidan',
            'ditolak', 'rejected'                 => 'Revisi / Ditolak',
            default                               => 'Menunggu Validasi',
        };
    }

    public function getStatusVerifikasiBadgeAttribute(): string
    {
        return match($this->status_verifikasi) {
            'tervalidasi', 'verified', 'approved' => 'emerald',
            'ditolak', 'rejected'                 => 'rose',   
            default                               => 'amber',  
        };
    }

    /**
     * =================================================================
     * 4. SCOPES (MACRO QUERY UNTUK CONTROLLER)
     * =================================================================
     */

    public function scopePending($query)
    {
        return $query->where(function($q) {
            $q->where('status_verifikasi', 'pending')
              ->orWhereNull('status_verifikasi'); 
        });
    }

    public function scopeVerified($query)
    {
        return $query->whereIn('status_verifikasi', ['tervalidasi', 'verified', 'approved']);
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