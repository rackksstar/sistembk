<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === User::ROLE_ADMIN;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'nisn' => ['required', 'string', 'max:20', Rule::unique('students', 'nisn')->ignore($this->route('student'))],
            'birth_date' => ['required', 'date', 'before:today'],
            'school' => ['nullable', 'string', 'max:255'],
            'user_id' => ['nullable', 'exists:users,id'],
        ];
    }
}
