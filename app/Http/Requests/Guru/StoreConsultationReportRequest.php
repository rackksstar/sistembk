<?php

namespace App\Http\Requests\Guru;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class StoreConsultationReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === User::ROLE_GURU;
    }

    public function rules(): array
    {
        return [
            'result' => ['required', 'string', 'max:3000'],
            'evaluation' => ['required', 'string', 'max:3000'],
        ];
    }
}
