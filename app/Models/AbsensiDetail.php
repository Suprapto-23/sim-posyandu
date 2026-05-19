<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Class AbsensiDetail
 * * @property int $id
 * @property int $absensi_id
 * @property int $pasien_id
 * @property string $pasien_type
 * @property bool $hadir
 * @property string|null $keterangan
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * * @property-read \App\Models\AbsensiPosyandu $absensi
 * @property-read \Illuminate\Database\Eloquent\Model $pasien
 */
class AbsensiDetail extends Model
{
    /**
     * Nama tabel di database (opsional tapi praktik yang baik untuk eksplisit).
     *
     * @var string
     */
    protected $table = 'absensi_details';

    /**
     * Kolom yang dapat diisi melalui Mass Assignment.
     * Pastikan 'pasien_type' ditambahkan agar Polymorphic relation bisa bekerja.
     *
     * @var array<string>
     */
    protected $fillable = [
        'absensi_id',
        'pasien_id',
        'pasien_type', // Contoh isi: 'App\Models\Balita', 'App\Models\Lansia'
        'hadir',
        'keterangan',
    ];

    /**
     * Casting tipe data kolom untuk konsistensi.
     * Mengubah 1/0 dari database menjadi tipe boolean sejati di PHP.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'hadir'      => 'boolean',
        'pasien_id'  => 'integer',
        'absensi_id' => 'integer',
    ];

    /**
     * =========================================================================
     * 🔗 ELOQUENT RELATIONSHIPS
     * =========================================================================
     */

    /**
     * Relasi balik ke Header (Sesi Absensi Posyandu).
     * * @return BelongsTo
     */
    public function absensi(): BelongsTo
    {
        return $this->belongsTo(AbsensiPosyandu::class, 'absensi_id');
    }

    /**
     * RELASI POLYMORPHIC (SUPER POWER 🔥)
     * Relasi dinamis ke entitas pasien (Balita, Remaja, atau Lansia).
     * Eloquent akan otomatis mencari tabel dan model yang tepat berdasarkan 
     * kombinasi kolom 'pasien_type' dan 'pasien_id'.
     * * @return MorphTo
     */
    public function pasien(): MorphTo
    {
        return $this->morphTo();
    }
}