<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lansia extends Model
{
    // PERBAIKAN: Menjaga integritas riwayat pemeriksaan dengan SoftDeletes
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'kode_lansia',
        'nik',
        'nama_lengkap',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'alamat',
        'telepon_keluarga',
        'penyakit_bawaan',
        'golongan_darah',
        'created_by',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'berat_badan'   => 'float',
        'tinggi_badan'  => 'float',
        'imt'           => 'float',
    ];

    // PERBAIKAN: Proteksi dari bug Attempt to read property on null
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($model) {
            $model->kunjungans()->delete();
            $model->pemeriksaans()->delete();
        });
    }

    // ── VIRTUAL ATTRIBUTES (LOGIKA CERDAS LANSIA) ─────────────────
    public function getUsiaTahunAttribute()
    {
        if (!$this->tanggal_lahir) return 0;
        return $this->tanggal_lahir->age;
    }

    public function getKategoriUsiaAttribute()
    {
        $usia = $this->usia_tahun;

        if ($usia >= 45 && $usia <= 59) return 'Pra-Lansia (45-59 Tahun)';
        if ($usia >= 60 && $usia <= 69) return 'Lansia (60-69 Tahun)';
        if ($usia >= 70) return 'Lansia Risiko Tinggi (>= 70 Tahun)';

        return 'Bukan Sasaran Lansia';
    }

    public function getStatusImtAttribute()
    {
        $imt = $this->imt;
        if (!$imt) return '-';

        if ($imt < 18.5) return 'Kurus (Kekurangan BB)';
        if ($imt >= 18.5 && $imt <= 24.9) return 'Normal';
        if ($imt >= 25.0 && $imt <= 27.0) return 'Gemuk (Kelebihan BB Tingkat Ringan)';
        return 'Obesitas (Kelebihan BB Tingkat Berat)';
    }

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

    public function pemeriksaans()
    {
        return $this->hasMany(Pemeriksaan::class, 'pasien_id')
                    ->where('kategori_pasien', 'lansia')
                    ->orderBy('tanggal_periksa', 'desc');
    }
}