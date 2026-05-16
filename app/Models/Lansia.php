<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lansia extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'kode_lansia', 'nik', 'nama_lengkap', 'tempat_lahir',
        'tanggal_lahir', 'jenis_kelamin', 'alamat', 'penyakit_bawaan',
        'berat_badan', 'tinggi_badan', 'imt', 'kemandirian', 'telepon_keluarga', 'created_by',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'berat_badan'   => 'float',
        'tinggi_badan'  => 'float',
        'imt'           => 'float',
    ];

    // ── VIRTUAL ATTRIBUTES (LOGIKA CERDAS LANSIA) ─────────────────

    /**
     * Hitung Usia dalam Tahun (Real-time)
     * Dapat dipanggil dengan: $lansia->usia_tahun
     */
    public function getUsiaTahunAttribute()
    {
        if (!$this->tanggal_lahir) return 0;
        return Carbon::parse($this->tanggal_lahir)->age;
    }

    /**
     * Kategorikan otomatis berdasarkan SOP Kemenkes
     * (Pra-Lansia, Lansia, Lansia Risiko Tinggi)
     */
    public function getKategoriSopAttribute()
    {
        $tahun = $this->usia_tahun;

        if ($tahun >= 45 && $tahun < 60) {
            return 'Pra-Lansia (45-59 Tahun)';
        } elseif ($tahun >= 60 && $tahun < 70) {
            return 'Lansia (60-69 Tahun)';
        } elseif ($tahun >= 70) {
            return 'Lansia Risiko Tinggi (≥ 70 Tahun)';
        } else {
            // Jika ada kasus khusus dimasukkan ke sistem sebelum usia 45
            return 'Dewasa (< 45 Tahun)'; 
        }
    }

    /**
     * Status IMT (Indeks Massa Tubuh) Spesifik Asia/Indonesia
     * Dapat dipanggil dengan: $lansia->status_imt
     */
    public function getStatusImtAttribute()
    {
        $imt = $this->imt;
        if (!$imt) return '-';

        if ($imt < 18.5) return 'Kurus (Kekurangan BB)';
        if ($imt >= 18.5 && $imt <= 24.9) return 'Normal';
        if ($imt >= 25.0 && $imt <= 27.0) return 'Gemuk (Kelebihan BB Tingkat Ringan)';
        return 'Obesitas (Kelebihan BB Tingkat Berat)';
    }

    /**
     * Tampilkan penyakit bawaan dengan format rapi.
     */
    public function getInfoPenyakitAttribute()
    {
        return $this->penyakit_bawaan ? $this->penyakit_bawaan : 'Tidak Ada Penyakit Bawaan';
    }


    // ── RELASI DASAR ──────────────────────────────────────────────
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function kunjungans()
    {
        return $this->morphMany(Kunjungan::class, 'pasien')
                    ->orderBy('tanggal_kunjungan', 'desc');
    }

    // ── RELASI PEMERIKSAAN (POWERFUL EAGER LOADING) ───────────────
    public function pemeriksaans()
    {
        return $this->hasMany(Pemeriksaan::class, 'pasien_id')
                    ->where('kategori_pasien', 'lansia');
    }

    public function pemeriksaan_terakhir()
    {
        return $this->hasOne(Pemeriksaan::class, 'pasien_id')
                    ->where('kategori_pasien', 'lansia')
                    ->latestOfMany();
    }
}