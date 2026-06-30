<?php

namespace App\Http\Requests\Kader;

use Illuminate\Foundation\Http\FormRequest;

class PendaftaranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [
            'nama_lengkap'  => 'required|string|max:191',
            'nik'           => 'required|numeric|digits:16', // Tidak unique krn bisa saja NIK ortu (balita)
            'jenis_kelamin' => 'required|in:L,P,Laki-laki,Perempuan',
            'tempat_lahir'  => 'required|string|max:191',
            'tanggal_lahir' => 'required|date|before_or_equal:today',
            'alamat'        => 'nullable|string',
        ];
    }
}