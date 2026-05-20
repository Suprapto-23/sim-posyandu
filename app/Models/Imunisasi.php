<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * =========================================================================
 * IMUNISASI MODEL (NEXUS ULTIMATE EDITION)
 * =========================================================================
 * Mesin utama pengelola riwayat vaksinasi. Dilengkapi dengan Virtual
 * Attributes (Accessors) dan Analytics Scopes untuk UI Dashboard yang cerdas.
 */
class Imunisasi extends Model
{
    use HasFactory;

    protected $table = 'imunisasis';

    // Proteksi dinamis, mempermudah Controller
    protected $guarded = ['id'];

    // 1. AUTO-APPEND VIRTUAL COLUMNS
    // Atribut ini akan otomatis dikalkulasi dan dikirim ke Frontend (View/JSON)
    // Sangat berguna untuk mencegah Blade Error (Null Pointer) di View.
    protected $appends = [
        'nama_penerima',
        'nik_penerima',
        'kategori_sasaran',
        'kategori_vaksin_badge'
    ];

    // 2. DATA CASTING
    // Memastikan presisi format saat ditarik dari Database
    protected $casts = [
        'tanggal_imunisasi' => 'date',
        'expiry_date'       => 'date',
        'dosis'             => 'integer',
    ];

    /**
     * RELASI DATABASE
     */
    public function kunjungan()
    {
        return $this->belongsTo(Kunjungan::class, 'kunjungan_id');
    }

    /**
     * =================================================================
     * 3. ACCESSORS (FAILSAFE & SMART UI LOGIC)
     * =================================================================
     */

    /**
     * SAFE PULL: Mengambil nama tanpa risiko sistem crash jika data pasien terhapus
     */
    public function getNamaPenerimaAttribute()
    {
        return $this->kunjungan?->pasien?->nama_lengkap ?? 'Data Terhapus / Anonim';
    }

    /**
     * SAFE PULL: Mengambil NIK
     */
    public function getNikPenerimaAttribute()
    {
        return $this->kunjungan?->pasien?->nik ?? '-';
    }

    /**
     * Mengidentifikasi kategori secara langsung dari relasi Polymorphic
     */
    public function getKategoriSasaranAttribute()
    {
        $type = $this->kunjungan?->pasien_type;
        if (!$type) return 'Umum';
        
        return match(class_basename($type)) {
            'Balita'   => 'Balita',
            'Remaja'   => 'Remaja',
            'Lansia'   => 'Lansia',
            default    => 'Umum'
        };
    }

    /**
     * SMART BADGING: Memberikan kode warna UI otomatis berdasarkan jenis vaksin
     * Membantu Kader melihat jenis imunisasi secara visual di Tabel Index.
     */
    public function getKategoriVaksinBadgeAttribute()
    {
        $vaksin = strtolower($this->vaksin);
        
        // Kategori Imunisasi Dasar Lengkap (IDL) Bayi/Balita (Warna Sky/Biru)
        if (str_contains($vaksin, 'bcg') || str_contains($vaksin, 'polio') || 
            str_contains($vaksin, 'hepatitis') || str_contains($vaksin, 'dpt') || 
            str_contains($vaksin, 'hib') || str_contains($vaksin, 'campak') || 
            str_contains($vaksin, 'mr') || str_contains($vaksin, 'pentabio') || 
            str_contains($vaksin, 'pcv') || str_contains($vaksin, 'rotavirus')) {
            return 'sky'; 
        }
        
        

        // Kategori Vaksin Lainnya (Warna Indigo)
        return 'indigo'; 
    }

    /**
     * =================================================================
     * 4. SCOPES (ANALYTICS BUILDER)
     * Digunakan oleh Controller untuk menghitung Metrik Dashboard secara instan
     * =================================================================
     */

    // Hitung capaian imunisasi bulan ini
    public function scopeBulanIni($query)
    {
        return $query->whereMonth('tanggal_imunisasi', Carbon::now()->month)
                     ->whereYear('tanggal_imunisasi', Carbon::now()->year);
    }

    // Filter spesifik target Balita
    public function scopeTargetBalita($query)
    {
        return $query->whereHas('kunjungan', function($q) {
            $q->where('pasien_type', 'App\Models\Balita');
        });
    }

    
}