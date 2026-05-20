<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kunjungan extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_kunjungan',
        'pasien_id',
        'pasien_type',
        'tanggal_kunjungan',
        'jenis_kunjungan',
        'pertemuan_ke', // Tambahan: Mencatat absensi (Pertemuan 1, 2, 3 dst)
        'keluhan',
        'petugas_id',
    ];

    protected $casts = [
        'tanggal_kunjungan' => 'date',
        'pertemuan_ke'      => 'integer', // Memastikan nilainya selalu angka
    ];

    public function pasien()
    {
        // Fitur Polimorfik: Bisa menyambung ke Balita, Remaja, Lansia
        return $this->morphTo();
    }

    public function petugas()
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }

    public function pemeriksaan()
    {
        return $this->hasOne(Pemeriksaan::class);
    }

    public function imunisasis()
    {
        return $this->hasMany(Imunisasi::class);
    }

    public function vitamins()
    {
        return $this->hasMany(Vitamin::class);
    }

    

    // Accessor: Mengambil Nama Lengkap dari model mana pun yang terhubung
    public function getNamaPasienAttribute()
    {
        return $this->pasien->nama_lengkap ?? 'Tidak diketahui';
    }

    public function getNamaPetugasAttribute()
    {
        return $this->petugas->nama ?? 'Tidak diketahui';
    }

    public function getJenisKunjunganLabelAttribute()
    {
        $jenis = [
            'umum'        => 'Kunjungan Umum',
            'imunisasi'   => 'Imunisasi',
            'pemeriksaan' => 'Pemeriksaan',
            'darurat'     => 'Darurat'
        ];
        
        return $jenis[$this->jenis_kunjungan] ?? 'Tidak Diketahui';
    }

    public function scopeToday($query)
    {
        return $query->whereDate('tanggal_kunjungan', today());
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('tanggal_kunjungan', date('m'))
            ->whereYear('tanggal_kunjungan', date('Y'));
    }
}