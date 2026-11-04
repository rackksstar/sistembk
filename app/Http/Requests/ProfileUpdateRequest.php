<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        if ($this->user()?->role === User::ROLE_GURU) {
            $guruBk = $this->user()->guruBkProfile;

            return [
                'name' => ['required', 'string', 'max:255'],
                'no_hp' => [
                    'required',
                    'string',
                    'max:30',
                    Rule::unique('guru_bks', 'no_hp')->ignore($guruBk),
                    Rule::unique('users', 'username')->ignore($this->user()->id),
                ],
                'nip' => ['required', 'string', 'max:40', Rule::unique('guru_bks', 'nip')->ignore($guruBk)],
            ];
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
        ];
    }
}
