<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // PERBAIKAN: Harus bernilai 'true' agar user diizinkan mengedit profilnya
        return true; 
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = $this->user();

        return [
            'full_name' => ['required', 'string', 'max:191'],
            
            // PERBAIKAN: Mengabaikan NIK milik profil user ini sendiri saat update
            'nik' => [
                'required',
                'string',
                'size:16',
                Rule::unique('profiles', 'nik')->ignore($user->profile->id ?? null),
            ],
            
            // PERBAIKAN: Mengabaikan Email milik user ini sendiri saat update
            'email' => [
                'nullable',
                'email',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
        ];
    }

    /**
     * Kustomisasi pesan error agar lebih ramah bagi warga/user.
     */
    public function messages(): array
    {
        return [
            'nik.unique' => 'NIK ini sudah terdaftar pada sistem kami.',
            'nik.size' => 'Format NIK tidak valid, harus tepat 16 digit angka.',
            'email.unique' => 'Alamat email ini sudah digunakan oleh akun lain.',
        ];
    }
}