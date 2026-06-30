<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CreateBidanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [
            'full_name' => 'required|string|max:191',
            'nik'       => 'required|numeric|digits:16|unique:profiles,nik',
            'email'     => 'required|email|max:191|unique:users,email', // Bidan biasanya wajib email
            'password'  => 'required|string|min:6',
            'phone'     => 'nullable|string|max:20',
            'no_str'    => 'nullable|string|max:100', // Nomor Surat Tanda Registrasi Bidan
        ];
    }
}