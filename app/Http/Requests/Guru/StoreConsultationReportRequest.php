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
            'rpl_id' => ['nullable', 'integer', 'exists:rpls,id'],
            'case_category' => ['required', Rule::in(array_keys(ConsultationRequest::CASE_CATEGORIES))],
            'meeting_number' => ['required', 'integer', 'min:1', 'max:100'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:600'],
            'location' => ['required', 'string', 'max:255'],
            'approach_technique' => ['required', 'string', 'max:2000'],
            'semester' => ['required', 'integer', 'in:1,2'],
            'year' => ['required', 'integer', 'min:2020', 'max:2100'],
            'result' => ['required', 'string', 'max:3000'],
            'evaluation' => ['required', 'string', 'max:3000'],
            'follow_up' => ['nullable', 'string', 'max:3000'],
        ];
    }
}
