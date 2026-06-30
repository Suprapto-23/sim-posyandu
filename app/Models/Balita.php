<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Carbon\Carbon;

class Balita extends Model
{
    use HasFactory, SoftDeletes; 

    protected $table = 'balitas';

    protected $fillable = [
        'user_id',
        'kode_balita',
        'nik',
        'nama_lengkap',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'berat_lahir',
        'panjang_lahir',
        'nama_ibu',
        'nik_ibu',
        'nama_ayah',
        'alamat',
        'created_by',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'berat_lahir'   => 'decimal:2',
        'panjang_lahir' => 'decimal:2',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
    ];

    // ── EVENT LISTENERS (Mencegah Orphan Data) ───────────────────────────
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($model) {
            // Hapus riwayat otomatis saat data balita dihapus
            $model->kunjungans()->delete();
            $model->pemeriksaans()->delete();
            $model->absensiDetails()->delete();
        });
    }

    // ── ACCESSORS & MUTATORS (Format Data Otomatis) ──────────────────────
    public function getUsiaBulanAttribute(): int
    {
        if (! $this->tanggal_lahir) {
            return 0;
        }
        return (int) $this->tanggal_lahir->diffInMonths(now());
    }

    public function getUsiaLabelAttribute(): string
    {
        $bulan = $this->usia_bulan;

        if ($bulan < 12) {
            return $bulan . ' bulan';
        }
        if ($bulan >= 12 && $bulan <= 59) {
            $tahun = floor($bulan / 12);
            $sisaBulan = $bulan % 12;
            return $sisaBulan > 0 ? "{$tahun} tahun {$sisaBulan} bulan" : "{$tahun} tahun";
        }
        return 'Lewat usia sasaran Balita';
    }

    public function getJenisKelaminLabelAttribute(): string
    {
        return match (strtolower((string) $this->jenis_kelamin)) {
            'l', 'laki-laki' => 'Laki-laki',
            'p', 'perempuan' => 'Perempuan',
            default => '-',
        };
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

    public function pembuat(): BelongsTo
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
            ->where('kategori_pasien', 'balita');
    }

    /**
     * PERBAIKAN: Fungsi ini mengambil SATU data pemeriksaan paling terakhir
     * Nama fungsi disesuaikan dengan snake_case yang dipanggil oleh Controller
     */
    public function pemeriksaan_terakhir()
    {
        return $this->hasOne(Pemeriksaan::class, 'pasien_id')
            ->where('kategori_pasien', 'balita')
            ->latestOfMany('tanggal_periksa');
    }
}