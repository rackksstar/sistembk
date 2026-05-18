<?php

namespace App\Http\Requests\Guru;

use App\Models\ConsultationRequest;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreConsultationReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === User::ROLE_GURU;
    }

    public function rules(): array
    {
        return [
            'case_category' => ['required', Rule::in(array_keys(ConsultationRequest::CASE_CATEGORIES))],
            'result' => ['required', 'string', 'max:3000'],
            'evaluation' => ['required', 'string', 'max:3000'],
            'follow_up' => ['nullable', 'string', 'max:3000'],
        ];
    }
}
