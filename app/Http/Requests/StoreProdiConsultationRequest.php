<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProdiConsultationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === User::ROLE_SISWA;
    }

    public function rules(): array
    {
        return [
            'subject' => ['required', 'string', 'max:120'],
            'program_studi_id' => [
                'required',
                Rule::exists('program_studis', 'id')
                    ->where(fn ($query) => $query->where('is_active', true)->where('is_verified', true)),
            ],
            'preferred_date' => ['nullable', 'date', 'after_or_equal:today'],
            'preferred_time' => ['nullable', 'string', 'max:80'],
            'details' => ['required', 'string', 'max:2000'],
            'counselor_id' => [
                'required',
                Rule::exists('users', 'id')
                    ->where(fn ($query) => $query
                        ->where('role', User::ROLE_GURU)
                        ->where('status', User::STATUS_APPROVED)),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'subject.required' => 'Topik konsultasi wajib diisi.',
            'program_studi_id.required' => 'Pilih program studi yang ingin dikonsultasikan.',
            'program_studi_id.exists' => 'Program studi tidak valid atau belum diverifikasi Admin.',
            'details.required' => 'Jelaskan pertanyaan atau hal yang ingin dibahas tentang prodi tersebut.',
            'counselor_id.required' => 'Silakan pilih Guru BK.',
            'counselor_id.exists' => 'Guru BK tidak valid atau belum disetujui.',
        ];
    }
}
