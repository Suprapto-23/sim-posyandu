<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bidan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'sip',
        'spesialisasi',
        'rumah_sakit',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

   

    public function pemeriksaans()
    {
        return $this->hasMany(Pemeriksaan::class, 'bidan_id');
    }
}