<?php

namespace App\Http\Requests\Bidan;

use Illuminate\Foundation\Http\FormRequest;

class PemeriksaanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        // Validasi agar nilai berat dan tinggi badan masuk akal (mencegah salah ketik)
        return [
            'kunjungan_id'  => 'required|integer',
            'berat_badan'   => 'nullable|numeric|min:0|max:200',
            'tinggi_badan'  => 'nullable|numeric|min:0|max:250',
            'tekanan_darah' => 'nullable|string|max:20', // cth: 120/80
            'suhu_tubuh'    => 'nullable|numeric|min:30|max:45',
            'keluhan'       => 'nullable|string',
            'tindakan'      => 'nullable|string',
            'keterangan'    => 'nullable|string',
        ];
    }
}