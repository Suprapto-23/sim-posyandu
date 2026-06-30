<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Balita extends Model
{
    // PERBAIKAN: Mencegah penghapusan permanen dari database
    use SoftDeletes; 

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
        'berat_lahir' => 'decimal:2',
        'panjang_lahir' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // PERBAIKAN: Menghapus relasi terkait jika data balita dihapus (mencegah orphan data)
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($model) {
            $model->kunjungans()->delete();
            $model->pemeriksaans()->delete();
        });
    }

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
            return 'Balita usia 12 sampai 59 bulan';
        }
        return 'Lewat usia sasaran Balita';
    }

    public function getJenisKelaminLabelAttribute(): string
    {
        return match ($this->jenis_kelamin) {
            'L' => 'Laki-laki',
            'P' => 'Perempuan',
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

    public function pemeriksaans(): HasMany
    {
        return $this->hasMany(Pemeriksaan::class, 'pasien_id')
            ->where('kategori_pasien', 'balita');
    }
}