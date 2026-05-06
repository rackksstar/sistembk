<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGuruBkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === User::ROLE_ADMIN;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'status' => ['required', 'in:'.implode(',', User::STATUSES)],
            'sekolah_id' => ['nullable', 'exists:sekolahs,id'],
            'nip' => ['nullable', 'string', 'max:40', Rule::unique('guru_bks', 'nip')],
            'jabatan' => ['nullable', 'string', 'max:120'],
            'bidang_studi' => ['nullable', 'string', 'max:120'],
        ];
    }
}

