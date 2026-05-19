<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class AbsensiPosyandu
 * * @property int $id
 * @property string $kode_absensi
 * @property string $kategori
 * @property \Carbon\Carbon $tanggal_posyandu
 * @property int $nomor_pertemuan
 * @property int $bulan
 * @property int $tahun
 * @property string|null $catatan
 * @property int $dicatat_oleh
 * @property-read \App\Models\User $pencatat
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\AbsensiDetail[] $details
 */
class AbsensiPosyandu extends Model
{
    protected $table = 'absensi_posyandu';

    /**
     * Kolom yang dapat diisi melalui Mass Assignment.
     *
     * @var array<string>
     */
    protected $fillable = [
        'kode_absensi',
        'kategori',
        'tanggal_posyandu',
        'nomor_pertemuan',
        'bulan',
        'tahun',
        'catatan',
        'dicatat_oleh',
    ];

    /**
     * Casting tipe data kolom untuk konsistensi di level PHP.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tanggal_posyandu' => 'date',
        'bulan'            => 'integer',
        'tahun'            => 'integer',
    ];

    /**
     * Relasi ke Kader / User yang mencatat sesi absensi ini.
     * * @return BelongsTo
     */
    public function pencatat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }

    /**
     * Relasi ke manifest rincian absensi (Daftar Pasien).
     * * @return HasMany
     */
    public function details(): HasMany
    {
        return $this->hasMany(AbsensiDetail::class, 'absensi_id');
    }

    /**
     * Relasi khusus untuk mengambil rincian pasien yang berstatus HADIR saja.
     * Bagus untuk optimasi summary counter di halaman index/riwayat.
     * * @return HasMany
     */
    public function hadir(): HasMany
    {
        return $this->hasMany(AbsensiDetail::class, 'absensi_id')->where('hadir', true);
    }

    /**
     * =========================================================================
     * 🪄 MAGIC ACCESSORS & MUTATORS (Penerjemah Data Otomatis)
     * =========================================================================
     */
    
    /**
     * Mengubah value slug kategori menjadi label teks medis standar.
     * * @return string
     */
    public function getKategoriLabelAttribute(): string
    {
        return match($this->kategori) {
            'bayi'      => 'Bayi (0-11 Bulan)',
            'balita'    => 'Balita (12-59 Bulan)',
            'remaja'    => 'Remaja',
            'lansia'    => 'Lansia',
            'ibu_hamil' => 'Ibu Hamil',
            default     => 'Tidak Diketahui',
        };
    }

    /**
     * Menerjemahkan angka bulan (1-12) menjadi nama bulan dalam Bahasa Indonesia.
     * * @return string
     */
    public function getBulanLabelAttribute(): string
    {
        $bulanList = [
            1 => 'Januari',   2 => 'Februari',  3 => 'Maret', 
            4 => 'April',     5 => 'Mei',       6 => 'Juni', 
            7 => 'Juli',      8 => 'Agustus',   9 => 'September', 
            10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        return $bulanList[$this->bulan] ?? 'Tidak Diketahui';
    }

    /**
     * =========================================================================
     * 🎯 LOCAL SCOPES (Kriteria Pencarian Reusable)
     * Pembersihan logika query dari Controller agar Controller tetap ramping (Skinny).
     * =========================================================================
     */

    /**
     * Scope untuk memfilter absensi berdasarkan kategori tertentu.
     */
    public function scopeKategori(Builder $query, string $kategori): Builder
    {
        return $query->where('kategori', $kategori);
    }

    /**
     * Scope untuk memfilter absensi berdasarkan bulan dan tahun tertentu (untuk filter arsip).
     */
    public function scopePeriode(Builder $query, ?int $bulan, ?int $tahun): Builder
    {
        return $query->when($bulan, fn($q) => $q->where('bulan', $bulan))
                     ->when($tahun, fn($q) => $q->where('tahun', $tahun));
    }
}