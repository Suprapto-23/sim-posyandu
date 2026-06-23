<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bidan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'jabatan',
        'no_str',
        'no_sip',
        'lokasi_praktik',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pemeriksaans()
    {
        // PERBAIKAN: Menambahkan 'user_id' sebagai local key
        return $this->hasMany(Pemeriksaan::class, 'pemeriksa_id', 'user_id');
    }
}
