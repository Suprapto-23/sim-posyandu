<?php

namespace App\Http\Requests\Kader;

use Illuminate\Foundation\Http\FormRequest;

class KunjunganRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [
            'pasien_id'         => 'required|integer',
            'pasien_type'       => 'required|string|in:balita,remaja,lansia',
            'tanggal_kunjungan' => 'required|date',
            'jadwal_id'         => 'nullable|integer',
            'keterangan'        => 'nullable|string',
        ];
    }
}