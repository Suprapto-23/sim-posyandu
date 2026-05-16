<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Remaja extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'kode_remaja', 'nik', 'nama_lengkap', 'tempat_lahir',
        'tanggal_lahir', 'jenis_kelamin', 'golongan_darah', 'sekolah',
        'kelas', 'nama_ortu', 'telepon_ortu', 'alamat', 'created_by'
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
    ];

    // ── VIRTUAL ATTRIBUTES (LOGIKA CERDAS REMAJA) ────────────────

    /**
     * Hitung Usia dalam Tahun (Real-time)
     * Dapat dipanggil dengan: $remaja->usia_tahun
     */
    public function getUsiaTahunAttribute()
    {
        if (!$this->tanggal_lahir) return 0;
        return Carbon::parse($this->tanggal_lahir)->age;
    }

    /**
     * Kategorikan otomatis berdasarkan SOP Posyandu Remaja (Kemenkes)
     */
    public function getKategoriSopAttribute()
    {
        $tahun = $this->usia_tahun;

        if ($tahun < 10) {
            return 'Pra-Remaja (< 10 Tahun)';
        } elseif ($tahun >= 10 && $tahun <= 19) {
            return 'Remaja (10 - 19 Tahun)';
        } else {
            return 'Dewasa Muda (> 19 Tahun)';
        }
    }

    /**
     * Format Info Pendidikan Otomatis untuk UI
     * Output contoh: "SMAN 1 Kajen (Kelas 11)"
     */
    public function getInfoPendidikanAttribute()
    {
        if ($this->sekolah && $this->kelas) {
            return $this->sekolah . ' (Kelas ' . $this->kelas . ')';
        }
        return $this->sekolah ?: 'Pendidikan Belum Diisi';
    }


    // ── RELASI DASAR ──────────────────────────────────────────────
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function kunjungans()
    {
        // Kunjungan selalu diurutkan dari yang terbaru
        return $this->morphMany(Kunjungan::class, 'pasien')
                    ->orderBy('tanggal_kunjungan', 'desc');
    }

    // ── RELASI PEMERIKSAAN (POWERFUL EAGER LOADING) ───────────────
    public function pemeriksaans()
    {
        return $this->hasMany(Pemeriksaan::class, 'pasien_id')
                    ->where('kategori_pasien', 'remaja');
    }

    public function pemeriksaan_terakhir()
    {
        // Menggunakan latestOfMany() untuk performa tinggi saat memuat grafik/kartu
        return $this->hasOne(Pemeriksaan::class, 'pasien_id')
                    ->where('kategori_pasien', 'remaja')
                    ->latestOfMany();
    }
}