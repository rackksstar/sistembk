<?php

namespace App\Http\Requests\Guru;

use App\Models\User;
use App\Services\ConsultationScheduleService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

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

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $consultation = $this->route('consultation');
            $counselorId = (int) auth()->id();

            if (app(ConsultationScheduleService::class)->hasConflict(
                $counselorId,
                $this->input('consultation_date'),
                $this->input('consultation_time'),
                $consultation?->id
            )) {
                $validator->errors()->add(
                    'consultation_time',
                    'Jadwal bentrok dengan sesi konseling lain pada tanggal dan jam yang sama.'
                );
            }
        });
    }
}
