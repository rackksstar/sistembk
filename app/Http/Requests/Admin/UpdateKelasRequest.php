<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class UpdateKelasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === User::ROLE_ADMIN;
    }

    public function rules(): array
    {
        return [
            'sekolah_id' => ['required', 'exists:sekolahs,id'],
            'nama' => ['required', 'string', 'max:120'],
            'jenjang' => ['nullable', 'string', 'max:40'],
            'tingkatan' => ['nullable', 'string', 'max:40'],
        ];
    }
}

