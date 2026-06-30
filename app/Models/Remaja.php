<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Remaja extends Model
{
    // PERBAIKAN: Keamanan level enterprise untuk data yang tidak sengaja dihapus
    use HasFactory, SoftDeletes;

    protected $table = 'remajas';

    protected $fillable = [
        'user_id',
        'kode_remaja', 
        'nik',
        'nama_lengkap',
        'jenis_kelamin',
        'tanggal_lahir',
        'tempat_lahir',
        'alamat',
        'sekolah',     
        'kelas',       
        'nama_ortu', 
        'telepon_ortu',
        'created_by',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // PERBAIKAN: Pembersihan relasi otomatis
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($model) {
            $model->kunjungans()->delete();
            $model->pemeriksaans()->delete();
            $model->absensiDetails()->delete();
        });
    }

    /* Relasi Akun & Petugas */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /* Relasi Kunjungan & Pemeriksaan */
    public function kunjungans(): MorphMany
    {
        return $this->morphMany(Kunjungan::class, 'pasien');
    }

    public function pemeriksaans(): HasMany
    {
        return $this->hasMany(Pemeriksaan::class, 'pasien_id')
            ->where('kategori_pasien', 'remaja');
    }

    public function pemeriksaanTerbaru()
    {
        return $this->hasOne(Pemeriksaan::class, 'pasien_id')
            ->where('kategori_pasien', 'remaja')
            ->latestOfMany('tanggal_periksa');
    }

    public function absensiDetails(): HasMany
    {
        return $this->hasMany(AbsensiDetail::class, 'pasien_id')
            ->where('pasien_type', 'remaja'); // Catatan: Anda menggunakan string literal disini sesuai struktur DB lama Anda
    }

    /* Accessor Identitas */
    public function getNamaAttribute(): string
    {
        return $this->nama_lengkap ?? '-';
    }

    public function getNamaPendekAttribute(): string
    {
        $nama = trim((string) $this->nama_lengkap);
        if ($nama === '') return '-';

        $parts = explode(' ', $nama);
        return count($parts) > 2 ? $parts[0] . ' ' . $parts[1] : $nama;
    }

    public function getJenisKelaminLabelAttribute(): string
    {
        return match (strtolower((string) $this->jenis_kelamin)) {
            'l', 'laki-laki', 'laki laki', 'pria' => 'Laki-laki',
            'p', 'perempuan', 'wanita' => 'Perempuan',
            default => '-',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'active', 'aktif' => 'Aktif',
            'inactive', 'nonaktif' => 'Nonaktif',
            default => ucfirst((string) ($this->status ?? 'aktif')),
        };
    }

    public function getIsActiveAttribute(): bool
    {
        return in_array($this->status, ['active', 'aktif', null], true);
    }

    /* Accessor Umur */
    public function getUmurAttribute(): ?int
    {
        if (!$this->tanggal_lahir) return null;
        return Carbon::parse($this->tanggal_lahir)->age;
    }

    public function getUmurTextAttribute(): string
    {
        if (!$this->tanggal_lahir) return '-';

        $tanggalLahir = Carbon::parse($this->tanggal_lahir);
        $tahun = $tanggalLahir->diffInYears(now());
        $bulan = $tanggalLahir->copy()->addYears($tahun)->diffInMonths(now());

        if ($tahun <= 0) return $bulan . ' bulan';
        return $bulan > 0 ? $tahun . ' tahun ' . $bulan . ' bulan' : $tahun . ' tahun';
    }

    public function getTanggalLahirFormatAttribute(): string
    {
        if (!$this->tanggal_lahir) return '-';
        return Carbon::parse($this->tanggal_lahir)->translatedFormat('d F Y');
    }

    /* Accessor Pemeriksaan Terbaru */
    public function getBeratBadanTerakhirAttribute() { return $this->pemeriksaanTerbaru?->berat_badan; }
    public function getTinggiBadanTerakhirAttribute() { return $this->pemeriksaanTerbaru?->tinggi_badan; }
    public function getImtTerakhirAttribute() { return $this->pemeriksaanTerbaru?->imt; }
    public function getLilaTerakhirAttribute() { return $this->pemeriksaanTerbaru?->lingkar_lengan; }
    public function getLingkarPerutTerakhirAttribute() { return $this->pemeriksaanTerbaru?->lingkar_perut; }
    public function getTekananDarahTerakhirAttribute() { return $this->pemeriksaanTerbaru?->tekanan_darah; }
    public function getHemoglobinTerakhirAttribute() { return $this->pemeriksaanTerbaru?->hemoglobin; }

    /* Scope */
    public function scopeAktif($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('status')->orWhereIn('status', ['active', 'aktif']);
        });
    }

    public function scopeNonaktif($query)
    {
        return $query->whereIn('status', ['inactive', 'nonaktif']);
    }

    public function scopeSearch($query, ?string $keyword)
    {
        $keyword = trim((string) $keyword);
        if ($keyword === '') return $query;

        return $query->where(function ($q) use ($keyword) {
            if (is_numeric($keyword)) {
                $q->where('nik', 'like', "{$keyword}%")
                  ->orWhere('no_hp', 'like', "%{$keyword}%");
            } else {
                $q->where('nama_lengkap', 'like', "%{$keyword}%")
                  ->orWhere('nama_ortu', 'like', "%{$keyword}%");
            }
        });
    }

    public function scopeGender($query, ?string $gender)
    {
        if (!$gender) return $query;
        return $query->where('jenis_kelamin', $gender);
    }

    public function scopeTerbaru($query)
    {
        return $query->latest('created_at')->latest('id');
    }
}