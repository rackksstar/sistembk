<?php

namespace App\Http\Requests\Admin;

use App\Models\Kelas;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'nama' => [
                'required', 'string', 'max:120',
                Rule::unique('kelas', 'nama')
                    ->where(fn ($q) => $q->where('sekolah_id', $this->input('sekolah_id')))
                    ->ignore($this->route('kelas')),
            ],
            'jenjang' => ['nullable', 'string', Rule::in(Kelas::JENJANG_OPTIONS)],
            'tingkatan' => ['nullable', 'string', Rule::in(Kelas::TINGKATAN_OPTIONS)],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.unique' => 'Kelas dengan nama ini sudah ada di sekolah tersebut.',
            'jenjang.in' => 'Jenjang harus salah satu dari: '.implode(', ', Kelas::JENJANG_OPTIONS).'.',
            'tingkatan.in' => 'Tingkatan harus salah satu dari: '.implode(', ', Kelas::TINGKATAN_OPTIONS).'.',
        ];
    }
}

