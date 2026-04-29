<?php

namespace App\Http\Requests\Guru;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class ScheduleConsultationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === User::ROLE_GURU;
    }

    public function rules(): array
    {
        return [
            'consultation_date' => ['required', 'date', 'after_or_equal:today'],
            'consultation_time' => ['required', 'date_format:H:i'],
            'student_id' => ['required', 'exists:users,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
