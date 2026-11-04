<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class StoreSekolahRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === User::ROLE_ADMIN;
    }

    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:255', 'unique:sekolahs,nama'],
            'npsn' => ['required', 'string', 'max:20', 'unique:sekolahs,npsn'],
            'alamat' => ['required', 'string'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'is_mou' => ['required', 'boolean'],
            'paket_aktif' => ['nullable', 'string', 'max:120'],
            'tanggal_aktivasi' => ['nullable', 'date'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
