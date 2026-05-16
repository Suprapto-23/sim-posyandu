<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IbuHamil extends Model
{
    use HasFactory;

    protected $table = 'ibu_hamils';

    protected $fillable = [
        'user_id', 'kode_hamil', 'nama_lengkap', 'nik', 'tempat_lahir',
        'tanggal_lahir', 'nama_suami', 'alamat', 'telepon_ortu', 
        'hpht', 'hpl', 'golongan_darah', 'riwayat_penyakit',
        'berat_badan', 'tinggi_badan', 'imt', 'status', 'created_by'
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'hpht'          => 'date',
        'hpl'           => 'date',
        'berat_badan'   => 'float',
        'tinggi_badan'  => 'float',
        'imt'           => 'float',
    ];

    // ── VIRTUAL ATTRIBUTES (LOGIKA KEHAMILAN CERDAS) ──────────────

    /**
     * Hitung Usia Kehamilan dalam Minggu.
     * Berdasarkan HPHT (Hari Pertama Haid Terakhir).
     */
    public function getUsiaKehamilanMingguAttribute()
    {
        if (!$this->hpht) return 0;
        return Carbon::parse($this->hpht)->diffInWeeks(Carbon::now());
    }

    /**
     * Hitung Sisa Hari Kehamilan (Sisa hari ke HPL).
     */
    public function getSisaHariHplAttribute()
    {
        if (!$this->hpl) return 0;
        $hpl = Carbon::parse($this->hpl);
        $now = Carbon::now();
        
        if ($now->gt($hpl)) return 0; // Sudah lewat HPL
        return $now->diffInDays($hpl);
    }

    /**
     * Tentukan Trimester Kehamilan secara otomatis.
     */
    public function getTrimesterAttribute()
    {
        $minggu = $this->usia_kehamilan_minggu;

        if ($minggu >= 1 && $minggu <= 12) {
            return 'Trimester I';
        } elseif ($minggu >= 13 && $minggu <= 27) {
            return 'Trimester II';
        } elseif ($minggu >= 28) {
            return 'Trimester III';
        }
        
        return 'Belum Terdeteksi';
    }

    /**
     * Status IMT (Indeks Massa Tubuh) Ibu.
     */
    public function getStatusImtAttribute()
    {
        $imt = $this->imt;
        if (!$imt) return '-';

        if ($imt < 18.5) return 'Berat Badan Kurang';
        if ($imt >= 18.5 && $imt <= 24.9) return 'Normal';
        if ($imt >= 25 && $imt <= 29.9) return 'Kelebihan Berat Badan';
        return 'Obesitas';
    }

    // ── RELASI ────────────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function kunjungans()
    {
        return $this->morphMany(Kunjungan::class, 'pasien');
    }

    public function pemeriksaans()
    {
        return $this->hasMany(Pemeriksaan::class, 'pasien_id')
                    ->where('kategori_pasien', 'ibu_hamil');
    }

    public function pemeriksaan_terakhir()
    {
        return $this->hasOne(Pemeriksaan::class, 'pasien_id')
                    ->where('kategori_pasien', 'ibu_hamil')
                    ->latestOfMany();
    }
}