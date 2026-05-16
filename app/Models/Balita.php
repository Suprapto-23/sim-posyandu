<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Balita extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'kode_balita', 'nik', 'nama_lengkap', 'tempat_lahir',
        'tanggal_lahir', 'jenis_kelamin', 'nik_ibu', 'nama_ibu', 
        'nik_ayah', 'nama_ayah', 'alamat', 'berat_lahir', 'panjang_lahir', 'created_by'
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
    ];

    // ── VIRTUAL ATTRIBUTES (SMART DETECTION BAYI / BALITA) ────────

    /**
     * Hitung usia dalam bulan secara dinamis (Real-time).
     * Dapat dipanggil di view dengan: $anak->usia_bulan
     */
    public function getUsiaBulanAttribute()
    {
        if (!$this->tanggal_lahir) return 0;
        return Carbon::parse($this->tanggal_lahir)->diffInMonths(Carbon::now());
    }

    /**
     * Kategorikan otomatis berdasarkan SOP Kemenkes.
     * Dapat dipanggil di view dengan: $anak->kategori_sop
     */
    public function getKategoriSopAttribute()
    {
        $bulan = $this->usia_bulan;

        if ($bulan >= 0 && $bulan <= 11) {
            return 'Bayi (0 - 11 Bulan)';
        } elseif ($bulan >= 12 && $bulan <= 59) {
            return 'Balita (12 - 59 Bulan)';
        } else {
            return 'Lulus Posyandu (> 59 Bulan)';
        }
    }

    // ── RELASI DASAR ──────────────────────────────────────────────
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function kunjungans()
    {
        return $this->morphMany(Kunjungan::class, 'pasien');
    }

    // ── RELASI PEMERIKSAAN (POWERFUL EAGER LOADING) ───────────────
    public function pemeriksaans()
    {
        return $this->hasMany(Pemeriksaan::class, 'pasien_id')
                    ->where('kategori_pasien', 'balita');
    }

    public function pemeriksaan_terakhir()
    {
        // Menggunakan latestOfMany() untuk performa tingkat tinggi (Anti N+1 Query)
        return $this->hasOne(Pemeriksaan::class, 'pasien_id')
                    ->where('kategori_pasien', 'balita')
                    ->latestOfMany();
    }
}