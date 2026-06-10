<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Kolom yang boleh diisi mass-assignment.
     *
     * Catatan penting:
     * - Jangan masukkan must_change_password karena kolom itu sudah tidak dipakai.
     * - created_by boleh dipakai untuk audit siapa admin pembuat akun,
     *   tapi pastikan kolom users.created_by sudah ada lewat migration.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'nik',
        'role',
        'status',
        'last_login_at',
        'created_by',
    ];

    /**
     * Kolom yang disembunyikan saat model dijadikan array/json.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casting tipe data.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'password' => 'hashed',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relasi Akun
    |--------------------------------------------------------------------------
    */

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    public function bidan()
    {
        return $this->hasOne(Bidan::class);
    }

    public function kader()
    {
        return $this->hasOne(Kader::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function createdUsers()
    {
        return $this->hasMany(User::class, 'created_by');
    }

    public function loginLogs()
    {
        return $this->hasMany(LoginLog::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Relasi Data Sasaran
    |--------------------------------------------------------------------------
    | Relasi ini aman untuk kebutuhan SIM Posyandu:
    | Balita, Remaja, Lansia.
    */

    public function balitas()
    {
        return $this->hasMany(Balita::class, 'created_by');
    }

    public function remajas()
    {
        return $this->hasMany(Remaja::class, 'created_by');
    }

    public function lansias()
    {
        return $this->hasMany(Lansia::class, 'created_by');
    }

    /*
    |--------------------------------------------------------------------------
    | Relasi Aktivitas Kesehatan
    |--------------------------------------------------------------------------
    | Ini tidak menyimpan BB/TB/LP/LILA/HB langsung di users.
    | Data kesehatan tetap berada di tabel pemeriksaans/kunjungans,
    | sedangkan user hanya menjadi akun/petugas/pembuat data.
    */

    public function kunjungans()
    {
        return $this->hasMany(Kunjungan::class, 'petugas_id');
    }

    public function pemeriksaans()
    {
        return $this->hasMany(Pemeriksaan::class, 'pemeriksa_id');
    }

    public function verifikasiPemeriksaans()
    {
        return $this->hasMany(Pemeriksaan::class, 'verified_by');
    }

    public function notifikasis()
    {
        return $this->hasMany(Notifikasi::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Role
    |--------------------------------------------------------------------------
    */

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isBidan(): bool
    {
        return $this->role === 'bidan';
    }

    public function isKader(): bool
    {
        return $this->role === 'kader';
    }

    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    public function hasRole(string|array $role): bool
    {
        if (is_array($role)) {
            return in_array($this->role, $role, true);
        }

        return $this->role === $role;
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Password
    |--------------------------------------------------------------------------
    */

    public static function generatePassword(int $length = 8): string
    {
        return Str::random($length);
    }

    public static function generatePasswordFromNIK(?string $nik): string
    {
        if (!$nik || strlen($nik) < 16) {
            return self::generatePassword();
        }

        $last4 = substr($nik, -4);
        $tahunLahir = substr($nik, 10, 4);

        return $last4 . $tahunLahir;
    }

    /*
    |--------------------------------------------------------------------------
    | Accessor
    |--------------------------------------------------------------------------
    */

    public function getFullNameAttribute(): string
    {
        return $this->profile?->full_name
            ?? $this->profile?->nama_lengkap
            ?? $this->name
            ?? '-';
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'admin' => 'Admin',
            'bidan' => 'Bidan',
            'kader' => 'Kader',
            'user' => 'Warga',
            default => 'Tidak diketahui',
        };
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active';
    }
}