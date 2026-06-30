<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CreateKaderRequest extends FormRequest
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
            'email'     => 'nullable|email|max:191|unique:users,email',
            'password'  => 'required|string|min:6',
            'phone'     => 'nullable|string|max:20',
            'jabatan'   => 'nullable|string|max:100', // Opsional jika kader punya jabatan
        ];
    }
}