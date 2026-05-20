<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * =========================================================================
 * JADWAL POSYANDU MODEL (NEXUS ENTERPRISE EDITION)
 * =========================================================================
 * Dilengkapi dengan Smart Accessors untuk Dynamic Theming UI dan
 * penangangan format waktu otomatis.
 */
class JadwalPosyandu extends Model
{
    use HasFactory;

    protected $table = 'jadwal_posyandu';

    protected $fillable = [
        'judul', 'deskripsi', 'tanggal', 'waktu_mulai',
        'waktu_selesai', 'lokasi', 'kategori',
        'target_peserta', 'status', 'created_by'
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    // Menambahkan Atribut Virtual untuk UI Dashboard
    protected $appends = [
        'theme_color', 
        'label_target', 
        'status_badge',
        'waktu_lengkap'
    ];

    /**
     * =================================================================
     * 1. SCOPES (ANALYTICS & FILTERING)
     * =================================================================
     */
    
    // Hanya jadwal yang masih berjalan
    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    // Jadwal hari ini dan ke depan
    public function scopeMendatang($query)
    {
        return $query->whereDate('tanggal', '>=', Carbon::today('Asia/Jakarta'));
    }

    /**
     * =================================================================
     * 2. SMART ACCESSORS (UI LOGIC FAILSAFE)
     * Memindahkan beban logika UI dari file Blade ke dalam Model.
     * =================================================================
     */

    /**
     * Otomatis memberikan nama kelas warna Tailwind (Dynamic Theming)
     * Konsisten dengan Modul Imunisasi dan Pemeriksaan.
     */
    public function getThemeColorAttribute(): string
    {
        return match($this->target_peserta) {
            'balita'    => 'sky',       // Biru Bayi/Balita
            'remaja'    => 'indigo',    // Ungu/Indigo Remaja
            'lansia'    => 'emerald',   // Hijau Lansia
            default     => 'violet',    // Umum/Semua Warga
        };
    }

    /**
     * Label Target Peserta yang bersih
     */
    public function getLabelTargetAttribute(): string
    {
        return match($this->target_peserta) {
            'balita'    => 'Balita & Anak',
            'remaja'    => 'Remaja',
            'lansia'    => 'Lansia',
            default     => 'Semua Warga',
        };
    }

    /**
     * Format rentang waktu yang rapi (Contoh: "08:00 - 12:00 WIB")
     */
    public function getWaktuLengkapAttribute(): string
    {
        $mulai = Carbon::parse($this->waktu_mulai)->format('H:i');
        $selesai = Carbon::parse($this->waktu_selesai)->format('H:i');
        return "{$mulai} - {$selesai} WIB";
    }

    /**
     * Indikator warna status untuk kemudahan rendering Badge di Blade
     */
    public function getStatusBadgeAttribute(): array
    {
        return match($this->status) {
            'aktif'      => ['color' => 'emerald', 'icon' => 'fa-play-circle', 'text' => 'Berjalan'],
            'selesai'    => ['color' => 'slate',   'icon' => 'fa-check-circle', 'text' => 'Selesai'],
            'dibatalkan' => ['color' => 'rose',    'icon' => 'fa-times-circle', 'text' => 'Batal'],
            default      => ['color' => 'slate',   'icon' => 'fa-info-circle',  'text' => $this->status],
        };
    }

    /**
     * Cek apakah jadwal benar-benar "Hari Ini"
     */
    public function getIsTodayAttribute(): bool
    {
        return Carbon::parse($this->tanggal)->setTimezone('Asia/Jakarta')->isToday();
    }
}