<?php

namespace App\Http\Requests;

use App\Models\ConsultationRequest;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreConsultationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === User::ROLE_SISWA;
    }

    public function rules(): array
    {
        return [
            'subject' => ['required', 'string', 'max:120'],
            'case_category' => ['required', Rule::in(array_keys(ConsultationRequest::CASE_CATEGORIES))],
            'preferred_date' => ['nullable', 'date', 'after_or_equal:today'],
            'preferred_time' => ['nullable', 'string', 'max:80'],
            'details' => ['required', 'string', 'max:2000'],
            'counselor_id' => ['required', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'subject.required' => 'Topik konseling wajib diisi.',
            'case_category.required' => 'Pilih kategori masalah.',
            'details.required' => 'Ceritakan keluhan atau hal yang ingin dibahas.',
            'counselor_id.required' => 'Silakan pilih Guru BK.',
        ];
    }
}
