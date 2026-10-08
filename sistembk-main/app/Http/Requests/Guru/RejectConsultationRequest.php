<?php

namespace App\Http\Requests\Guru;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class RejectConsultationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === User::ROLE_GURU;
    }

    public function rules(): array
    {
        return [
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'rejection_reason.required' => 'Alasan penolakan wajib diisi.',
        ];
    }
}
