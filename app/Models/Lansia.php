<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Carbon\Carbon;

class Lansia extends Model
{
    // Menggunakan perlindungan SoftDeletes agar data rekam medis lansia aman
    use HasFactory, SoftDeletes;

    protected $table = 'lansias';

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
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
    ];

    // ── EVENT LISTENERS (Mencegah Orphan Data) ───────────────────────────
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($model) {
            // Mencegah data kunjungan, pemeriksaan, dan absensi tertinggal di database
            $model->kunjungans()->delete();
            $model->pemeriksaans()->delete();
            $model->absensiDetails()->delete();
        });
    }

    // ── VIRTUAL ATTRIBUTES (Logika Usia Lansia) ──────────────────────────
    public function getUsiaTahunAttribute(): int
    {
        if (!$this->tanggal_lahir) return 0;
        return (int) Carbon::parse($this->tanggal_lahir)->age;
    }

    public function getKategoriUsiaAttribute(): string
    {
        $usia = $this->usia_tahun;

        if ($usia >= 45 && $usia <= 59) return 'Pra-Lansia (45-59 Tahun)';
        if ($usia >= 60 && $usia <= 69) return 'Lansia (60-69 Tahun)';
        if ($usia >= 70) return 'Lansia Risiko Tinggi (>= 70 Tahun)';

        return 'Bukan Sasaran Lansia';
    }

    public function getJenisKelaminLabelAttribute(): string
    {
        return match (strtolower((string) $this->jenis_kelamin)) {
            'l', 'laki-laki' => 'Laki-laki',
            'p', 'perempuan' => 'Perempuan',
            default => '-',
        };
    }

    public function getInfoPenyakitAttribute(): string
    {
        return $this->penyakit_bawaan ? $this->penyakit_bawaan : 'Tidak Ada Penyakit Bawaan';
    }

    public function getStatusAkunLabelAttribute(): string
    {
        return $this->user_id ? 'Terhubung' : 'Belum Terhubung';
    }

    public function getStatusAkunClassAttribute(): string
    {
        return $this->user_id
            ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
            : 'border-amber-200 bg-amber-50 text-amber-700';
    }

    // ── RELASI DATABASE ──────────────────────────────────────────────────
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function kunjungans(): MorphMany
    {
        return $this->morphMany(Kunjungan::class, 'pasien');
    }

    public function absensiDetails(): MorphMany
    {
        return $this->morphMany(AbsensiDetail::class, 'pasien');
    }

    public function pemeriksaans(): HasMany
    {
        return $this->hasMany(Pemeriksaan::class, 'pasien_id')
                    ->where('kategori_pasien', 'lansia');
    }

    /**
     * FUNGSI KRUSIAL: Mengambil SATU data pemeriksaan terakhir.
     * Menggunakan snake_case untuk mengatasi error Call to undefined relationship.
     */
    public function pemeriksaan_terakhir()
    {
        return $this->hasOne(Pemeriksaan::class, 'pasien_id')
            ->where('kategori_pasien', 'lansia')
            ->latestOfMany('tanggal_periksa');
    }
}