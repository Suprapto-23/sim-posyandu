<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CreateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // PERBAIKAN: Mengizinkan admin membuat user
    }

    public function rules(): array
    {
        // PERBAIKAN: Mencegah NIK/Email ganda dan format yang salah
        return [
            'full_name' => 'required|string|max:191',
            'nik'       => 'required|numeric|digits:16|unique:profiles,nik',
            'email'     => 'nullable|email|max:191|unique:users,email',
            'password'  => 'required|string|min:6',
            'phone'     => 'nullable|string|max:20',
            'address'   => 'nullable|string',
        ];
    }
    
    public function messages(): array
    {
        return [
            'nik.digits' => 'NIK harus tepat 16 angka.',
            'nik.unique' => 'NIK ini sudah terdaftar di sistem.',
            'email.unique' => 'Email ini sudah dipakai oleh pengguna lain.',
        ];
    }
}