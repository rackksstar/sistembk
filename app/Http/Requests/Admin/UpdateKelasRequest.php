<?php

namespace App\Http\Requests\Admin;

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
            'jenjang' => ['nullable', 'string', 'max:40'],
            'tingkatan' => ['nullable', 'string', 'max:40'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.unique' => 'Kelas dengan nama ini sudah ada di sekolah tersebut.',
        ];
    }
}

