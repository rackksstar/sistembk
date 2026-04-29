<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreConsultationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'siswa';
    }

    public function rules(): array
    {
        return [
            'complaint' => ['required', 'string', 'max:1000'],
            'counselor_id' => ['required', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'complaint.required' => 'Silakan isi keluhan konseling.',
            'complaint.max' => 'Keluhan maksimal 1000 karakter.',
            'counselor_id.required' => 'Silakan pilih Guru BK.',
        ];
    }
}
