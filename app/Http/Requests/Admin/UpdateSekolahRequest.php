<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSekolahRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === User::ROLE_ADMIN;
    }

    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:255', Rule::unique('sekolahs', 'nama')->ignore($this->route('sekolah'))],
            'paket_aktif' => ['nullable', 'string', 'max:120'],
            'tanggal_aktivasi' => ['nullable', 'date'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}

